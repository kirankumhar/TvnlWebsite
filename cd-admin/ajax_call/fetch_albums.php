<?php
// fetch_albums.php - AJAX endpoint for fetching albums with pagination

// Encryption Key
$encryption_key = 'af7af6d2f08c8e7cdc4cc2d03046453c139c09ed7a5d98ec73ac9c230ec0a2f8';

// Generate a random IV for each encryption
$iv = openssl_random_pseudo_bytes(16);

// Include your database connection
require_once __DIR__ . '/../src/database/Database.php';
$database = new Database();
$pdo = $database->getConnection();

// Set header to return JSON
header('Content-Type: application/json');

// Check if session_year parameter exists
if (!isset($_POST['session_year']) || empty($_POST['session_year'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session year parameter is required'
    ]);
    exit;
}

$sessionYear = $_POST['session_year'];
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 8;
$offset = ($page - 1) * $limit;

// Determine type
$requestData = $_POST['data'] ?? '';
if ($requestData == 'press') {
    $type = 'Press Clips';
} elseif ($requestData == 'photo') {
    $type = 'Photos';
} elseif ($requestData == 'video') {
    $type = 'Videos';
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data type'
    ]);
    exit;
}

try {
    // First, get total count for pagination
    $countQuery = "SELECT COUNT(*) as total 
                   FROM albums a 
                   WHERE a.session_year = :session_year 
                   AND a.is_deleted = 0 
                   AND a.is_hide = 0 
                   AND a.type = :type";
    
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->bindParam(':session_year', $sessionYear, PDO::PARAM_STR);
    $countStmt->bindParam(':type', $type, PDO::PARAM_STR);
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);
    
    // Prepare the SQL query with pagination
    if ($type == 'Videos') {
        $query = "SELECT a.uniq_id as id, a.event_date, a.name_en, p.video_link as original_path  
                  FROM albums a 
                  LEFT JOIN videos p ON a.cover_video_id = p.id 
                  WHERE a.session_year = :session_year 
                  AND a.is_deleted = 0 
                  AND a.is_hide = 0 
                  AND a.type = :type
                  ORDER BY a.event_date DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':session_year', $sessionYear, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($albums as &$album) {
            // Add prefix and encrypt the path for videos
            $encrypted_id = openssl_encrypt($album['id'], 'AES-256-CBC', $encryption_key, 0, $iv);
            $encrypted_data = base64_encode($encrypted_id . '::' . base64_encode($iv));
            $url_data = urlencode($encrypted_data);
            $album['id'] = $url_data;
            $album['cover_image'] = $album['original_path'] ?? 'assets/images/default-video.jpg';
            unset($album['original_path']);
        }
        
    } else {
        // For Photos and Press Clips
        $query = "SELECT a.uniq_id as id, a.event_date, a.name_en, p.file_path as original_path  
                  FROM albums a 
                  LEFT JOIN photos p ON a.cover_photo_id = p.id 
                  WHERE a.session_year = :session_year 
                  AND a.is_deleted = 0 
                  AND a.is_hide = 0 
                  AND a.type = :type
                  ORDER BY a.event_date DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':session_year', $sessionYear, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($albums as &$album) {
            if (!empty($album['original_path'])) {
                // Add prefix and encrypt the path
                $originalPath = "cd-admin/src/" . $album['original_path'];
                $encryptedPath = base64_encode($originalPath);
                $album['cover_image'] = 'enc_image.php?img=' . urlencode($encryptedPath);
                
                $encrypted_id = openssl_encrypt($album['id'], 'AES-256-CBC', $encryption_key, 0, $iv);
                $encrypted_data = base64_encode($encrypted_id . '::' . base64_encode($iv));
                $url_data = urlencode($encrypted_data);
                $album['id'] = $url_data;
                
                // Format date
                if (!empty($album['event_date'])) {
                    $album['formatted_date'] = date('d M Y', strtotime($album['event_date']));
                }
                
                unset($album['original_path']);
            } else {
                $album['cover_image'] = 'assets/images/default-album.jpg';
            }
        }
    }
    
    // Return JSON response with pagination
    echo json_encode([
        'success' => true,
        'albums' => $albums,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'limit' => $limit
        ]
    ]);
    
} catch (PDOException $e) {
    // Return error if there's a database issue
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>