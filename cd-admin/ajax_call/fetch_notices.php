<?php
// Include your database connection

require_once __DIR__ . '/../src/database/Database.php';
$database = new Database();
$pdo = $database->getConnection();

// Check if session_year parameter exists
if (isset($_POST['session_year'])) {
    $sessionYear = $_POST['session_year'];

    try {
        // Prepare the SQL query

        $query = "SELECT a.notice_dated, a.notice_title as title, a.notice_path as original_path, p.sub_category_name as ntype, a.notice_ref_no as ref_no, a.notice_url ,a.url_tab_open,a.created_at as created, a.notice_new_tag as new_tag
            FROM notices a 
            INNER JOIN sub_category p ON a.notice_subcategory = p.id 
            WHERE a.session_year = :session_year 
            AND a.is_deleted = '0' 
            AND a.status = 'A'
            ORDER BY a.notice_dated DESC";

        // Prepare and execute the statement
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':session_year', $sessionYear, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch all results as an associative array
        $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($albums as &$album) {
            if (!empty($album['original_path'])) {
                // Add prefix and encrypt the path
                $originalPath = $album['original_path'];
                $encryptedPath = base64_encode("cdgps/src/$originalPath");
                $album['cover_image'] = 'enc_pdf.php?pdf=' . urlencode($encryptedPath);

                // Remove the original path from response
                unset($album['original_path']);
            } else {
                $album['cover_image'] = 'cdgps/src/default-album.jpg';
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