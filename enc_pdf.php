<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');


if (isset($_GET['pdf'])) {
    $encodedPath = $_GET['pdf'];
   
    // Decode the path
    $pdfPath = base64_decode($encodedPath);
   
    // Prepend document root if the path is project-relative
    if (!empty($pdfPath) && $pdfPath[0] === '/' && !file_exists($pdfPath)) {
        $pdfPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;
    }

    // Validate path to prevent directory traversal
    if (strpos($pdfPath, '..') !== false) {
        header("HTTP/1.0 403 Forbidden");
        exit;
    }
    
    // Check if file exists
    if (file_exists($pdfPath)) {
        // Check file extension
        $extension = strtolower(pathinfo($pdfPath, PATHINFO_EXTENSION));
        
        if ($extension != 'pdf' && $extension != 'jpg' && $extension != 'jpeg') {
            header("HTTP/1.0 403 Forbidden");
            exit;
        }
        
        // Set appropriate content type based on file extension
        if ($extension == 'pdf') {
            header("Content-Type: application/pdf");
        } else if ($extension == 'jpg' || $extension == 'jpeg') {
            header("Content-Type: image/jpeg");
        }
        
        header("Content-Disposition: inline; filename=\"" . basename($pdfPath) . "\"");
        header("Content-Length: " . filesize($pdfPath));
        
        // Output the file
        readfile($pdfPath);
    } else {
        // If file doesn't exist, return a 404 error
        header("HTTP/1.0 404 Not Found");
        echo "File not found";
    }
} else {
    header("HTTP/1.0 400 Bad Request");
}
?>