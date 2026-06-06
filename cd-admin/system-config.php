<?php
// Prevent direct access
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $projectName = '/tvnl-website/cd-admin'; // local folder name
} else {
    $projectName = '/admin';
}

$protocol = isset($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$base_url = $protocol . $_SERVER['HTTP_HOST'] . $projectName;

$GLOBALS['baseUrl'] = $projectName;

$app_name = "TVNL";
$full_app_name = "TENUGHAT VIDYUT NIGAM LIMITED";
$loginPage_app_name = "TENUGHAT VIDYUT NIGAM LIMITED";
$logo = $base_url . "/assets/images/logos/logo_tvnl.png";
$faviconIcon = $base_url . '/assets/images/logos/logo_tvnl.png';

$dashboardThemes = [
    'indigo' => [
        'primary-color' => '#6366f1',
        'primary-hover' => '#4f46e5',
        'sidebar-bg' => '#0b62e6',
        'sidebar-secondary' => '#162f6b',
        'sidebar-hover' => '#0a58ca',
        'sidebar-active' => '#4f46e5',
        'sidebar-active-secondary' => '#818cf8',
    ],

    'blue' => [
        'primary-color' => '#2563eb',
        'primary-hover' => '#1d4ed8',
        'sidebar-bg' => '#0f172a',
        'sidebar-secondary' => '#162f6b',
        'sidebar-hover' => '#1e293b',
        'sidebar-active' => '#2563eb',
        'sidebar-active-secondary' => '#60a5fa',
    ],

    'green' => [
        'primary-color' => '#16a34a',
        'primary-hover' => '#15803d',
        'sidebar-bg' => '#052e16',
        'sidebar-secondary' => '#085729',
        'sidebar-hover' => '#064e3b',
        'sidebar-active' => '#16a34a',
        'sidebar-active-secondary' => '#4ade80',
    ],

    'teal' => [
        'primary-color' => '#0d9488',
        'primary-hover' => '#0f766e',
        'sidebar-bg' => '#042f2e',
        'sidebar-secondary' => '#235f3c',
        'sidebar-hover' => '#134e4a',
        'sidebar-active' => '#0d9488',
        'sidebar-active-secondary' => '#5eead4',
    ],

    'purple' => [
        'primary-color' => '#7c3aed',
        'primary-hover' => '#6d28d9',
        'sidebar-bg' => '#2e1065',
        'sidebar-secondary' => '#51076e',
        'sidebar-hover' => '#3b0764',
        'sidebar-active' => '#7c3aed',
        'sidebar-active-secondary' => '#c4b5fd',
    ],

    'slate' => [
        'primary-color' => '#475569',
        'primary-hover' => '#334155',
        'sidebar-bg' => '#020617',
        'sidebar-secondary' => '#4e4f53',
        'sidebar-hover' => '#020617',
        'sidebar-active' => '#475569',
        'sidebar-active-secondary' => '#94a3b8',
    ],
    'classic-blue' => [
        'primary-color' => '#1d4ed8',
        'primary-hover' => '#1e40af',
        'sidebar-bg' => '#0f172a',
        'sidebar-secondary' => '#0f1f52',
        'sidebar-hover' => '#1e293b',
        'sidebar-active' => '#1d4ed8',
        'sidebar-active-secondary' => '#60a5fa',
    ],

    'gov-indigo' => [
        'primary-color' => '#4f46e5',
        'primary-hover' => '#2d2774',
        'sidebar-bg' => '#111827',
        'sidebar-secondary' => '#1b2e58',
        'sidebar-hover' => '#1f2937',
        'sidebar-active' => '#4f46e5',
        'sidebar-active-secondary' => '#818cf8',
    ],

    'forest-green' => [
        'primary-color' => '#15803d',
        'primary-hover' => '#1a7a3f',
        'sidebar-bg' => '#022c22',
        'sidebar-secondary' => 'rgb(35, 110, 67)',
        'sidebar-hover' => '#064e3b',
        'sidebar-active' => '#15803d',
        'sidebar-active-secondary' => '#4ade80',
    ],

    'teal-admin' => [
        'primary-color' => '#0f766e',
        'primary-hover' => '#115e59',
        'sidebar-bg' => '#042f2e',
        'sidebar-secondary' => 'rgb(35, 88, 57)',
        'sidebar-hover' => '#134e4a',
        'sidebar-active' => '#0f766e',
        'sidebar-active-secondary' => '#5eead4',
    ],

    'royal-purple' => [
        'primary-color' => '#6d28d9',
        'primary-hover' => '#5b21b6',
        'sidebar-bg' => '#2e1065',
        'sidebar-secondary' => '#583f86',
        'sidebar-hover' => '#3b0764',
        'sidebar-active' => '#6d28d9',
        'sidebar-active-secondary' => '#c4b5fd',
    ],

    'slate-dark' => [
        'primary-color' => '#334155',
        'primary-hover' => '#1e293b',
        'sidebar-bg' => '#020617',
        'sidebar-secondary' => '#091755',
        'sidebar-hover' => '#020617',
        'sidebar-active' => '#334155',
        'sidebar-active-secondary' => '#94a3b8',
    ],

    'steel-gray' => [
        'primary-color' => '#475569',
        'primary-hover' => '#334155',
        'sidebar-bg' => '#0f172a',
        'sidebar-secondary' => '#132c64',
        'sidebar-hover' => '#1e293b',
        'sidebar-active' => '#475569',
        'sidebar-active-secondary' => '#cbd5e1',
    ],

    'navy-govt' => [
        'primary-color' => '#1e3a8a',
        'primary-hover' => '#1e40af',
        'sidebar-bg' => '#020617',
        'sidebar-secondary' => '#122eac',
        'sidebar-hover' => '#020617',
        'sidebar-active' => '#1e3a8a',
        'sidebar-active-secondary' => '#93c5fd',
    ],

    'olive-admin' => [
        'primary-color' => '#3f6212',
        'primary-hover' => '#365314',
        'sidebar-bg' => '#1a2e05',
        'sidebar-secondary' => '#345511',
        'sidebar-hover' => '#1a2e05',
        'sidebar-active' => '#3f6212',
        'sidebar-active-secondary' => '#bef264',
    ],

    'charcoal-modern' => [
        'primary-color' => '#0f172a',
        'primary-hover' => '#020617',
        'sidebar-bg' => '#000000',
        'sidebar-secondary' => '#4e4d4d',
        'sidebar-hover' => '#020617',
        'sidebar-active' => '#334155',
        'sidebar-active-secondary' => '#64748b',
    ],
];

//$themeKey = array_rand($dashboardThemes);
$themeKey = 'classic-blue';
$projectTheme = $dashboardThemes[$themeKey];
