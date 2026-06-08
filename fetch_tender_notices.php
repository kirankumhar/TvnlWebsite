<?php
require_once __DIR__ . '/../cd-admin/src/database/Database.php';
require_once __DIR__ . '/../cd-admin/src/models/TenderModel.php';

$database = new Database();
$pdo = $database->getConnection();
$tenderModel = new TenderModel($pdo);

header('Content-Type: application/json');

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
$offset = ($page - 1) * $limit;

$financialYear = $_POST['financial_year'] ?? null;
$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;
$searchTerm = $_POST['search_term'] ?? null;

try {
    $tenders = $tenderModel->getOpenTenders($limit, $offset, $financialYear, $startDate, $endDate, $searchTerm);
    $totalRecords = $tenderModel->getOpenTendersCount($financialYear, $startDate, $endDate, $searchTerm);
    $totalPages = ceil($totalRecords / $limit);

    foreach ($tenders as &$tender) {
        // Format dates for display
        $tender['tender_dated_formatted'] = date('d-M-Y', strtotime($tender['tender_dated']));
        $tender['tender_posting_formatted'] = date('d-M-Y', strtotime($tender['tender_posting']));
        $tender['tender_closing_date_formatted'] = date('d-M-Y | H:i', strtotime($tender['tender_closing_date']));
        $tender['tender_opening_date_formatted'] = date('d-M-Y | H:i', strtotime($tender['tender_opening_date']));

        // Construct file paths
        $base_url_prefix = '/tvnl-website/cd-admin/src/'; // Adjust this if your base URL is different
        $tender['tender_notice_path_full'] = !empty($tender['tender_notice_path']) ? $base_url_prefix . $tender['tender_notice_path'] : '';
        $tender['tender_doc_path_full'] = !empty($tender['tender_doc_path']) ? $base_url_prefix . $tender['tender_doc_path'] : '';
        
        // Handle other attachments (up to 3 as per create-tender.php)
        for ($i = 1; $i <= 3; $i++) {
            $path_key = 'tender_other_attach_' . $i . '_path';
            $title_key = 'tender_other_attach_' . $i . '_title';
            $tender[$path_key . '_full'] = !empty($tender[$path_key]) ? $base_url_prefix . $tender[$path_key] : '';
        }
    }

    echo json_encode([
        'success' => true,
        'tenders' => $tenders,
        'pagination' => [
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'results_per_page' => $limit,
            'offset' => $offset
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>