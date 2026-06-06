<?php
$domainWhere = '';
$domainParams = [];

// REMOVED domain filtering - now fetching all data regardless of domain

$usersCount = getCount($pdo, 'users', "is_deleted = '0'");
$catCount = getCount($pdo, 'category_master', "is_deleted = '0'");
$subCatCount = getCount($pdo, 'sub_category', "is_deleted = '0'");
$postCount = getCount($pdo, 'postings', "is_deleted = '0'");
$newsCount = getCount($pdo, 'news', "is_deleted = '0'");
$noticeCount = getCount($pdo, 'notices', "is_deleted = '0'");
$photoCount = getCount($pdo, 'albums', "is_deleted = '0' AND type = 'Photos'");
$vdoCount = getCount($pdo, 'albums', "is_deleted = '0' AND type = 'Videos'");
$pressCount = getCount($pdo, 'albums', "is_deleted = '0' AND type = 'Press Clips'");

// Assign consistent but varied colors for each card type
$cardColors = [
    'category' => '#6366f1',
    'subcategory' => '#10b981',
    'news' => '#ef4444',
    'notices' => '#f59e0b',
    'photos' => '#0ea5e9',
    'videos' => '#8b5cf6',
    'users' => '#64748b'
];

$cards = [];

// REMOVED Domains card entirely

if (hasModuleRow($pdo, $userId, 'users')) {
    $cards[] = [
        'title' => 'Total Users',
        'url'   => 'manage-user.php',
        'count' => $usersCount,
        'icon'  => 'ti ti-users',
        'color' => $cardColors['users']
    ];
}

if (hasModuleRow($pdo, $userId, 'category')) {
    $cards[] = [
        'title' => 'Total Categories',
        'url'   => 'manage-category.php',
        'count' => $catCount,
        'icon'  => 'ti ti-category',
        'color' => $cardColors['category']
    ];
}

if (hasModuleRow($pdo, $userId, 'category')) {
    $cards[] = [
        'title' => 'Total Sub Categories',
        'url'   => 'manage-sub-category.php',
        'count' => $subCatCount,
        'icon'  => 'ti ti-layout-list',
        'color' => $cardColors['subcategory']
    ];
}

if (hasModuleRow($pdo, $userId, 'news')) {
    $cards[] = [
        'title' => 'Total News',
        'url'   => 'manage-news.php',
        'count' => $newsCount,
        'icon'  => 'ti ti-news',
        'color' => $cardColors['news']
    ];
}

if (hasModuleRow($pdo, $userId, 'notices')) {
    $cards[] = [
        'title' => 'Total Notices',
        'url'   => 'manage-notices.php',
        'count' => $noticeCount,
        'icon'  => 'ti ti-file-text',
        'color' => $cardColors['notices']
    ];
}

if (hasModuleRow($pdo, $userId, 'mediaPhoto')) {
    $cards[] = [
        'title' => 'Total Photos',
        'url'   => 'manage-photos.php',
        'count' => $photoCount,
        'icon'  => 'ti ti-photo',
        'color' => $cardColors['photos']
    ];
}

if (hasModuleRow($pdo, $userId, 'mediavideo')) {
    $cards[] = [
        'title' => 'Total Videos',
        'url'   => 'manage-videos.php',
        'count' => $vdoCount,
        'icon'  => 'ti ti-video',
        'color' => $cardColors['videos']
    ];
}

$todayThought = $thoughtsOfTheDay[array_rand($thoughtsOfTheDay)];

/* Fetch password info for logged-in user */
$stmt = $pdo->prepare("
    SELECT password_set_date, password_expire_in_days 
    FROM users 
    WHERE id = :user_id 
    LIMIT 1
");
$stmt->execute([
    ':user_id' => $_SESSION['user_id'] // adjust as per your session
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    $setDate = new DateTime(date('Y-m-d', strtotime($user['password_set_date'])));
    $today   = new DateTime(date('Y-m-d'));

    $interval   = $setDate->diff($today);
    $daysPassed = (int) $interval->days;

    $expireIn = (int) $user['password_expire_in_days'];
    $daysLeft = max(0, $expireIn - $daysPassed);

    $isExpired = ($daysLeft <= 0);
} else {
    $daysLeft = null;
    $isExpired = true;
}

// recent notices - REMOVED domain filtering
$sql = "
    SELECT a.*, b.sub_category_name AS category_name, csc.child_sub_category_name
    FROM notices a
    JOIN sub_category b ON a.notice_subcategory = b.id
    LEFT JOIN child_sub_category csc ON csc.id = a.notice_childsubcategory
    WHERE a.is_deleted = '0'
    ORDER BY a.created_at DESC
    LIMIT 4
";
$recentNotices = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

function getCount(PDO $pdo, string $table, string $where = '1=1', array $params = [])
{
    $sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}