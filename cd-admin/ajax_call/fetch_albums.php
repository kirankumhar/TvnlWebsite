<?php
//  Encryption Key

$encryption_key = 'af7af6d2f08c8e7cdc4cc2d03046453c139c09ed7a5d98ec73ac9c230ec0a2f8';

// Include your database connection

require_once __DIR__ . '/../src/database/Database.php';
$database = new Database();
$pdo = $database->getConnection();

// Check if session_year parameter exists
if (isset($_POST['session_year'])) {
    $sessionYear = $_POST['session_year'];

    $type = $_POST['data'] == 'press' ? 'Press Clips' : ($_POST['data'] == 'photo' ? 'Photos' : 'Videos');

    try {
        // Prepare the SQL query
        if ($type == 'Videos') {
            $query = "SELECT a.uniq_id as id, a.event_date, a.name_en, p.video_link as original_path  
            FROM albums a 
            LEFT JOIN videos p ON a.cover_video_id = p.id 
            WHERE a.session_year = :session_year 
            AND a.is_deleted = 0 
            AND a.is_hide = 0 
            AND a.type = :type
            ORDER BY a.event_date DESC";

            // Prepare and execute the statement
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':session_year', $sessionYear, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->execute();

            // Fetch all results as an associative array
            $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($albums as &$album) {
                // Add prefix and encrypt the path
                $encrypted_id = openssl_encrypt($album['id'], 'AES-256-CBC', $encryption_key, 0, $iv);
                // Combine encrypted data with IV and encode for URL
                $encrypted_data = base64_encode($encrypted_id . '::' . base64_encode($iv));
                $url_data = urlencode($encrypted_data);

                $album['id'] = $url_data;
            }
            unset($album);

        } else {
            $query = "SELECT a.uniq_id as id, a.event_date, a.name_en, p.file_path as original_path  
            FROM albums a 
            LEFT JOIN photos p ON a.cover_photo_id = p.id 
            WHERE a.session_year = :session_year 
            AND a.is_deleted = 0 
            AND a.is_hide = 0 
            AND a.type = :type
            ORDER BY a.event_date DESC";

            // Prepare and execute the statement
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':session_year', $sessionYear, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->execute();

            // Fetch all results as an associative array
            $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($albums as &$album) {
                if (!empty($album['original_path'])) {
                    // Add prefix and encrypt the path
                    $originalPath = "cdgps/src/" . $album['original_path'];
                    $encryptedPath = base64_encode($originalPath);
                    $album['cover_image'] = 'enc_image.php?img=' . urlencode($encryptedPath);

                    $encrypted_id = openssl_encrypt($album['id'], 'AES-256-CBC', $encryption_key, 0, $iv);

                    // Combine encrypted data with IV and encode for URL
                    $encrypted_data = base64_encode($encrypted_id . '::' . base64_encode($iv));
                    $url_data = urlencode($encrypted_data);

                    $album['id'] = '';
                    $album['id'] = $url_data;

                    // Remove the original path from response
                    unset($album['original_path']);

                } else {
                    $album['cover_image'] = 'cdfgms/src/default-album.jpg';
                }
            }
        }

        // Return JSON response
        echo json_encode([
            'success' => true,
            'albums' => $albums
        ]);

    } catch (PDOException $e) {
        // Return error if there's a database issue
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    // Return error if session_year not provided
    echo json_encode([
        'success' => false,
        'message' => 'Session year parameter is required'
    ]);
}
?>