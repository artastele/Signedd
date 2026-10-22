<?php
$files = glob(__DIR__ . '/../public/videos/fsl/*.{mov,mp4,webm}', GLOB_BRACE);
$mp4s = glob(__DIR__ . '/../public/videos/fsl/*.mp4');
echo "Total FSL video files: " . count($files) . "\n";
echo "Total MP4 files: " . count($mp4s) . "\n";
foreach (array_slice($mp4s, 0, 15) as $f) {
    echo "MP4: " . basename($f) . "\n";
}
