<?php
// Import schema into MySQL using credentials from config/.env
require_once __DIR__ . '/../config/env.php';

$host = env('DB_HOST', 'localhost');
$dbname = env('DB_NAME', 'sped_lms');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', '');

$sqlFile = __DIR__ . '/../config/schema.sql';
if (!file_exists($sqlFile)) {
    echo "Schema file not found: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Failed to read schema file.\n";
    exit(1);
}

$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_errno) {
    echo "MySQL connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "\n";
    exit(1);
}

$mysqli->set_charset('utf8mb4');

// Remove DROP TABLE statements to avoid foreign-key ordering issues on live DBs
$filtered = preg_replace('/^\s*DROP TABLE IF EXISTS.*;?/mi', '', $sql);
$filtered = preg_replace('/^\s*DROP TABLE.*;?/mi', '', $filtered);

// Ensure CREATE TABLE is idempotent: add IF NOT EXISTS where missing
$filtered = preg_replace('/CREATE\s+TABLE\s+(?!IF\s+NOT\s+EXISTS)/mi', 'CREATE TABLE IF NOT EXISTS ', $filtered);

// Extract FOREIGN KEY constraints from CREATE TABLE blocks and convert them
// into ALTER TABLE ... ADD CONSTRAINT statements appended after all CREATEs.
// Robustly parse CREATE TABLE blocks to extract FK constraints
$alterStatements = [];
$out = '';
$pos = 0;
$len = strlen($filtered);
$pattern = '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?\s*\(/i';
while (preg_match($pattern, $filtered, $m, PREG_OFFSET_CAPTURE, $pos)) {
    $matchStart = $m[0][1];
    $tableName = $m[1][0];

    // append everything before this CREATE TABLE
    $out .= substr($filtered, $pos, $matchStart - $pos);

    // find opening parenthesis position
    $openPos = strpos($filtered, '(', $matchStart);
    if ($openPos === false) break;

    // find matching closing parenthesis by counting depth
    $i = $openPos + 1;
    $depth = 1;
    while ($i < $len && $depth > 0) {
        $ch = $filtered[$i];
        if ($ch === '(') $depth++;
        elseif ($ch === ')') $depth--;
        $i++;
    }
    if ($depth !== 0) {
        // unmatched parentheses; abort
        break;
    }
    $closePos = $i - 1; // position of the matching ')'

    // find the semicolon that ends the CREATE TABLE statement
    $semiPos = strpos($filtered, ';', $closePos);
    if ($semiPos === false) $semiPos = $len - 1;

    // Extract body between parentheses
    $body = substr($filtered, $openPos + 1, $closePos - $openPos - 1);
    $suffix = substr($filtered, $closePos + 1, $semiPos - $closePos - 1); // e.g. ENGINE=...

    // Remove FK constraints from body
    $namedFk = '/CONSTRAINT\s+`?([a-zA-Z0-9_]+)`?\s+FOREIGN\s+KEY\s*\([^\)]+\)\s*REFERENCES\s+`?([a-zA-Z0-9_]+)`?\s*\([^\)]+\)[^,;]*/ims';
    $body = preg_replace_callback($namedFk, function($mm) use (&$alterStatements, $tableName) {
        $constraintSql = trim($mm[0], " \t\n\r,");
        $alterStatements[] = "ALTER TABLE `$tableName` ADD " . $constraintSql . ";";
        return '';
    }, $body);

    $inlineFk = '/FOREIGN\s+KEY\s*\([^\)]+\)\s*REFERENCES\s+`?([a-zA-Z0-9_]+)`?\s*\([^\)]+\)[^,;]*/ims';
    $body = preg_replace_callback($inlineFk, function($mm) use (&$alterStatements, $tableName) {
        $fkSql = trim($mm[0], " \t\n\r,");
        $alterStatements[] = "ALTER TABLE `$tableName` ADD " . $fkSql . ";";
        return '';
    }, $body);

    // Cleanup body
    $body = preg_replace('/,\s*,+/m', ',', $body);
    $body = preg_replace('/\(\s*,/m', '(', $body);
    $body = preg_replace('/,\s*\)/m', ')', $body);
    $body = preg_replace('/^[\s,]*$/m', '', $body);
    $body = trim($body);
    $body = preg_replace('/,\s*$/', '', $body);

    // Rebuild CREATE TABLE block
    $out .= "CREATE TABLE IF NOT EXISTS `" . $tableName . "` (" . $body . ")" . $suffix . ";";

    $pos = $semiPos + 1;
}

// append the remainder
$out .= substr($filtered, $pos);

$filtered = $out;

// Append all ALTER TABLE statements after the CREATEs
if (!empty($alterStatements)) {
    $filtered .= "\n\n-- Add foreign key constraints after table creation\n" . implode("\n", $alterStatements) . "\n";
}

// Disable foreign key checks during import to avoid ordering issues
$full = "SET FOREIGN_KEY_CHECKS=0;\n" . $filtered . "\nSET FOREIGN_KEY_CHECKS=1;";

// Execute statements one by one: strip comments and split on semicolons
$clean = preg_replace('/--.*$/m', '', $full); // remove single-line -- comments
$clean = preg_replace('/\/\*.*?\*\//s', '', $clean); // remove /* */ comments

$parts = preg_split('/;\s*/', $clean);
$successCount = 0;
foreach ($parts as $part) {
    $stmt = trim($part);
    if ($stmt === '') continue;
    // Ensure statement ends without extra semicolon
    if (substr($stmt, -1) === ';') $stmt = substr($stmt, 0, -1);

    // Debug: show statement snippet being executed
    echo "--- Executing statement (len=" . strlen($stmt) . ") ---\n";
    echo substr($stmt, 0, 800) . "\n\n";

    if (!$mysqli->query($stmt)) {
        echo "Statement failed: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
        echo "Offending SQL snippet:\n" . substr($stmt, 0, 800) . "\n";
        $mysqli->close();
        exit(1);
    }
    $successCount++;
}

echo "Import completed. Statements processed (approx): $successCount\n";

// Optional: show DB version table entry if exists
$res = $mysqli->query("SELECT version FROM db_version ORDER BY version DESC LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    echo "db_version latest: " . $row['version'] . "\n";
}

$mysqli->close();

return 0;
