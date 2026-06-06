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

        $query = "SELECT a.tender_posting, a.tender_title as title, a.tender_type, a.tender_nit_ref_no as ref_no, a.tender_notice_path as notice_path, p.sub_category_name as ntype, a.tender_doc_path as doc_path , a.tender_other_attach_1_path as attach_path1, a.tender_other_attach_2_path as attach_path2, a.tender_other_attach_3_path as attach_path3,  a.tender_other_attach_1_title as attach_title1, a.tender_other_attach_2_title as attach_title2, a.tender_other_attach_3_title as attach_title3, a.created_at as created, a.new_tag as new_tag
            FROM tenders a 
            INNER JOIN sub_category p ON a.tender_category = p.id 
            WHERE a.financial_year = :session_year 
            AND a.is_deleted = '0' 
            ORDER BY a.tender_posting DESC";

        // Prepare and execute the statement
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':session_year', $sessionYear, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch all results as an associative array
        $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($albums as &$album) {
            // Process each path independently instead of using else if
            if (!empty($album['notice_path'])) {
                $originalPath = $album['notice_path'];
                $encryptedPath = base64_encode("cdgps/src/$originalPath");
                $album['notice_path'] = 'enc_pdf.php?pdf=' . urlencode($encryptedPath);
            }
            
            if (!empty($album['doc_path'])) {
                $originalPath = $album['doc_path'];
                $encryptedPath = base64_encode("cdgps/src/$originalPath");
                $album['doc_path'] = 'enc_pdf.php?pdf=' . urlencode($encryptedPath);
            }
            
            if (!empty($album['attach_path1'])) {
                $originalPath = $album['attach_path1'];
                $encryptedPath = base64_encode("cdgps/src/$originalPath");
                $album['attach_path1'] = 'enc_pdf.php?pdf=' . urlencode($encryptedPath);
            }
            
            if (!empty($album['attach_path2'])) {
                $originalPath = $album['attach_path2'];
                $encryptedPath = base64_encode("cdgps/src/$originalPath");
                $album['attach_path2'] = 'enc_pdf.php?pdf=' . urlencode($encryptedPath);
            }
            
            if (!empty($album['attach_path3'])) {
                $originalPath = $album['attach_path3'];
                $encryptedPath = base64_encode("cdgps/src/$originalPath");
                $album['attach_path3'] = 'enc_pdf.php?pdf=' . urlencode($encryptedPath);
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