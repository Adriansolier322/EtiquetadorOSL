<?php
header('Content-Type: application/json');

$savedPdfDir = 'pdf/saved/';
$pdfs = [];

if (file_exists($savedPdfDir) && is_dir($savedPdfDir)) {
    $files = scandir($savedPdfDir, SCANDIR_SORT_DESCENDING);
    
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
            $pdfs[] = [
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'path' => $savedPdfDir . $file
            ];
        }
    }
}

echo json_encode($pdfs);
?>