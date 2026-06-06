<?php

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session Timeout, Please Login Again.';
    header('Location: index.php');
    exit;
}

require './src/database/Database.php';
$database = new Database();
$pdo = $database->getConnection();

$usersRoles = [
    'superadmin' => 'Super Admin',
    'admin' => 'Admin',
    'coadmin' => 'Co-Admin'
];


$stmt = $pdo->prepare("SELECT id, domain_id, username, email, role , permission, password_set_date, password_expire_in_days FROM users WHERE id = ?");
$stmt->execute([(int) $_SESSION['user_id']]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // user not found → force logout
    session_destroy();
    header('Location: /login.php');
    exit;
}

if ($user) {
    $setDate = new DateTime(date('Y-m-d', strtotime($user['password_set_date'])));
    $today   = new DateTime(date('Y-m-d'));

    $interval   = $setDate->diff($today);
    $daysPassed = (int) $interval->days;

    $expireIn = (int) $user['password_expire_in_days'];
    $daysLeft = max(0, $expireIn - $daysPassed);

    $isExpired = ($daysLeft <= 0);
} else {
    $daysLeft = 0;
    $isExpired = true;
}

// Redirect if expired
if ($isExpired && basename($_SERVER['PHP_SELF']) !== 'expire-update-password.php') {
    header("Location: expire-update-password.php");
    exit;
}

// Map roles
$usersRoles = [
    'superadmin' => 'Super Admin',
    'admin'      => 'Admin',
    'coadmin'    => 'Co-Admin',
    'writer'     => 'Writer',
    'author'     => 'Author'
];

// Assign values safely
$domainId = (int) $user['domain_id'];
$username = $user['username'];
$email    = $user['email'];
$role     = $usersRoles[$user['role']] ?? 'Admin';
$authRole = $user['role'];
$userId   = $user['id'];
$domainName = getDomainNameById($domainId);
if($domainId > 0){
    $title = $domainName . ' | Dashboard';
} else {
    $title = 'Administration | Dashboard';
}
$permissions = json_decode($user['permission'] ?? '[]', true);

// print_r($permissionJson); die();

function getDomainNameById($domainId)
{
    $database = new Database();
    $pdo = $database->getConnection();
    $stmt = $pdo->prepare("SELECT eng_name FROM domains WHERE id = ?");
    $stmt->execute([$domainId]);
    return $stmt->fetchColumn();
}

function canAccess(string $module, array $permissions , string $role = null): bool
{   
    if ($role === 'superadmin') {
        return true;
    }
    return in_array($module, $permissions ?? [], true); 
}
