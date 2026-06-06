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

    try {
        // Prepare the SQL query
        $query = "SELECT a.uniq_id as id, a.news_event_date as ndate, a.news_title as title, a.news_pic1  as original_path  
                    FROM news a 
                    WHERE a.session_year = :session_year 
                    AND a.is_deleted = 0 
                    AND a.is_hide = 'N'
                    ORDER BY a.news_event_date DESC";

        // Prepare and execute the statement
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':session_year', $sessionYear, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch all results as an associative array
        $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($albums as &$album) {
            if (!empty($album['original_path'])) {
                // Add prefix and encrypt the path
                $originalPath = "cdgps/src/" . trim($album['original_path']);
                $encryptedPath = base64_encode($originalPath);
                $album['cover_image'] = 'enc_image.php?img=' . urlencode($encryptedPath);

                // Encrypt URL
                $encrypted_id = openssl_encrypt($album['id'], 'AES-256-CBC', $encryption_key, 0, $iv);

                // Combine encrypted data with IV and encode for URL
                $encrypted_data = base64_encode($encrypted_id . '::' . base64_encode($iv));
                $url_data = urlencode($encrypted_data);

                $album['id'] = '';
                $album['id'] = $url_data;


                // Remove the original path from response
                unset($album['original_path']);
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