<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$database = new Database();
$conn = $database->getConnection();

$response = ['success' => false, 'cancellations' => [], 'total_records' => 0];

try {
    $results_per_page = 10;
    $current_page = isset($_POST['page']) && is_numeric($_POST['page']) ? (int)$_POST['page'] : 1;
    $offset = ($current_page - 1) * $results_per_page;
    
    // Get filter parameters
    $financial_year = isset($_POST['financial_year']) ? $_POST['financial_year'] : '';
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    
    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    
    if (!empty($financial_year)) {
        $where_conditions[] = "YEAR(c.can_date_pub) = :financial_year";
        $params[':financial_year'] = $financial_year;
    }
    
    if (!empty($start_date)) {
        $where_conditions[] = "c.can_date_pub >= :start_date";
        $params[':start_date'] = $start_date;
    }
    
    if (!empty($end_date)) {
        $where_conditions[] = "c.can_date_pub <= :end_date";
        $params[':end_date'] = $end_date;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM tbl_cancellation_notice c 
                    LEFT JOIN tbl_tender_details t ON c.ten_id = t.ten_id 
                    $where_clause";
    $count_stmt = $conn->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $total_records = $total_result['total'];
    $total_pages = ceil($total_records / $results_per_page);
    
    // Get cancellations with tender details
    $query = "SELECT c.*, t.title, t.nit_no, t.descp, t.notice_url, t.document_url 
              FROM tbl_cancellation_notice c 
              LEFT JOIN tbl_tender_details t ON c.ten_id = t.ten_id 
              $where_clause 
              ORDER BY c.cancel_id DESC 
              LIMIT :offset, :results_per_page";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':results_per_page', $results_per_page, PDO::PARAM_INT);
    $stmt->execute();
    
    $cancellations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['cancellations'] = $cancellations;
    $response['total_records'] = $total_records;
    $response['current_page'] = $current_page;
    $response['total_pages'] = $total_pages;
    $response['results_per_page'] = $results_per_page;
    $response['offset'] = $offset;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>