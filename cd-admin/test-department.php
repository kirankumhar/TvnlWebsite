<?php
// Test file to verify paths
echo "<h2>Testing Department Controller Paths</h2>";

$controllerPath = __DIR__ . '/src/controllers/tender/DepartmentController.php';
echo "<p>Controller path: " . $controllerPath . "</p>";

if (file_exists($controllerPath)) {
    echo "<p style='color:green'>✓ Controller found!</p>";
    require_once $controllerPath;
    echo "<p style='color:green'>✓ Controller loaded!</p>";
} else {
    echo "<p style='color:red'>✗ Controller NOT found!</p>";
}

$modelPath = __DIR__ . '/src/models/DepartmentModel.php';
echo "<p>Model path: " . $modelPath . "</p>";

if (file_exists($modelPath)) {
    echo "<p style='color:green'>✓ Model found!</p>";
} else {
    echo "<p style='color:red'>✗ Model NOT found!</p>";
}

$loggerPath = __DIR__ . '/src/utils/ActivityLogger.php';
echo "<p>Logger path: " . $loggerPath . "</p>";

if (file_exists($loggerPath)) {
    echo "<p style='color:green'>✓ Logger found!</p>";
} else {
    echo "<p style='color:red'>✗ Logger NOT found!</p>";
}
?>