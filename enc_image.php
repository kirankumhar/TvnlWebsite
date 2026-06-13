<?php
if (isset($_GET['img'])) {
    $encodedPath = $_GET['img'];
    
    // Decode the path
    $imagePath = base64_decode($encodedPath);
    
    // Prepend document root if it's a project-relative path
    if (!empty($imagePath) && $imagePath[0] === '/' && !file_exists($imagePath)) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/tvnl-website/cd-admin' . $imagePath;
    }

    // Validate path to prevent directory traversal
    if (strpos($imagePath, '..') !== false) {
        header("HTTP/1.0 403 Forbidden");
        exit;
    }
    
    // Check if file exists
    if (file_exists($imagePath)) {
        // Get image content type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $contentType = finfo_file($finfo, $imagePath);
        finfo_close($finfo);
        
        // Output the image with proper headers
        header("Content-Type: $contentType");
        readfile($imagePath);
    } else {
        // If file doesn't exist, return default image
        header("Content-Type: image/jpeg");
        readfile("assets/images/default-album.jpg");
    }
} else {
    header("HTTP/1.0 400 Bad Request");
}
?>