<?php
// Extract signedtest_deploy.zip to a local folder for FileZilla upload
$zipPath = dirname(__DIR__) . '/signedtest_deploy.zip';
$extractTo = dirname(__DIR__) . '/signedtest_extracted';

if (!file_exists($zipPath)) {
    echo "[ERROR] ZIP not found: $zipPath\n"; exit(1);
}

// Clean old extraction
if (is_dir($extractTo)) {
    echo "Removing old extraction folder...\n";
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($extractTo, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($extractTo);
}

echo "Extracting ZIP to: $extractTo\n";
$zip = new ZipArchive();
if ($zip->open($zipPath) !== true) {
    echo "[ERROR] Cannot open ZIP\n"; exit(1);
}
$zip->extractTo($extractTo);
$zip->close();

// Count files
$count = iterator_count(
    new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($extractTo, RecursiveDirectoryIterator::SKIP_DOTS)
    )
);

echo "[DONE] Extracted $count items to:\n";
echo "  $extractTo\n\n";
echo "Now in FileZilla:\n";
echo "  Left panel: navigate to $extractTo\n";
echo "  Right panel: navigate to /htdocs\n";
echo "  Select all in left panel -> drag to right panel\n";
