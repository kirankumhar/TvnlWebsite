<?php
$modulesAll = [
    'domain','category','news','notices','mediaPressclip',
    'mediaPhoto','mediavideo','users','permission'
];

$modulesAdmin = [
    'category','news','notices','mediaPressclip',
    'mediaPhoto','mediavideo','users','permission'
];

$modulesContent = [
    'news','notices','mediaPressclip',
    'mediaPhoto','mediavideo'
];

$modulesCoAdmin = [
    'category','news','notices',
    'mediaPressclip','mediaPhoto','mediavideo'
];

$fullActions   = ['create','edit','delete'];
$editorActions = ['create','edit'];
$noActions     = [];

$permissionRoleWise = [

    'superadmin' => [
        'module'     => $modulesAll,
        'permission' => array_fill_keys($modulesAll, $fullActions)
    ],

    'admin' => [
        'module'     => $modulesAdmin,
        'permission' => array_fill_keys($modulesAdmin, $fullActions)
    ],

    'coadmin' => [
        'module'     => $modulesCoAdmin,
        'permission' => array_fill_keys($modulesCoAdmin, $fullActions)
    ],

    'writer' => [
        'module'     => $modulesContent,
        'permission' => array_fill_keys($modulesContent, $editorActions)
    ],

    'author' => [
        'module'     => $modulesContent,
        'permission' => array_fill_keys($modulesContent, $noActions)
    ]
];
