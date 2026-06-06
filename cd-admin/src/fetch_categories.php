<?php
require_once __DIR__ . '/database/Database.php';

// print_r($_POST); die();

if (isset($_POST['domainId']) && isset($_POST['action']) && $_POST['action'] == 'fetchCategories') {

    $domainId = intval($_POST['domainId']);

    $database = new Database();
    $pdo = $database->getConnection();

    $sql = "SELECT 
                id,
                category_name
            FROM category_master
            WHERE is_deleted = '0'
              AND domain_id = :domain_id
            ORDER BY category_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
}

if (isset($_POST['domainId']) && isset($_POST['action']) && $_POST['action'] == 'fetchSubCategories') {

    $domainId = intval($_POST['domainId']);
    $search   = isset($_POST['currentPage']) ? trim($_POST['currentPage']) : '';

    $database = new Database();
    $pdo = $database->getConnection();

    $sql = "SELECT 
                id,
                sub_category_name
            FROM sub_category
            WHERE is_deleted = '0'
              AND domain_id = :domain_id";

    if ($search !== '') {
        $sql .= " AND sub_category_name LIKE :search ";
    }
    $sql .= " ORDER BY sub_category_name ASC";    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':domain_id', $domainId, PDO::PARAM_INT);
    if ($search !== '') {
        $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
    }
    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
}

if (isset($_POST['catid']) && isset($_POST['action']) && $_POST['action'] == 'fetchSubCategories') {

    $catid = intval($_POST['catid']);

    $database = new Database();
    $pdo = $database->getConnection();

    $sql = "SELECT 
                id,
                sub_category_name
            FROM sub_category
            WHERE is_deleted = '0'
              AND category_id = :catid
            ORDER BY sub_category_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':catid', $catid, PDO::PARAM_INT);
    $stmt->execute();

    $subCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($subCategories);
}

if (isset($_POST['subcatid']) && isset($_POST['action']) && $_POST['action'] == 'fetchChildSubCategories') {

    $subcatid = intval($_POST['subcatid']);

    $database = new Database();
    $pdo = $database->getConnection();

    $sql = "SELECT 
                id,
                child_sub_category_name
            FROM child_sub_category
            WHERE is_deleted = '0'
              AND subcategory_id = :subcatid
            ORDER BY child_sub_category_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':subcatid', $subcatid, PDO::PARAM_INT);
    $stmt->execute();

    $subCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($subCategories);
}

