<?php
echo "<h2>Route Debug</h2>";
echo "<p><strong>BASE_PATH:</strong> " . (defined('BASE_PATH') ? BASE_PATH : 'NOT DEFINED') . "</p>";
echo "<p><strong>Expected Submit URL:</strong> " . (defined('BASE_PATH') ? BASE_PATH : '') . "/enrollment/submit</p>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>PHP Self:</strong> " . $_SERVER['PHP_SELF'] . "</p>";

// Test if routing works
echo "<hr>";
echo "<h3>Test Form Submit:</h3>";
echo "<form method='POST' action='" . (defined('BASE_PATH') ? BASE_PATH : '') . "/enrollment/submit'>";
echo "<input type='hidden' name='test' value='1'>";
echo "<button type='submit'>Test Submit</button>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<p style='color: green;'><strong>POST received!</strong></p>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
}
?>
