<?php

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/DailyPatientRecordModel.php';

class DailyPatientRecordsController
{
    private $model;

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $this->model = new DailyPatientRecordModel($pdo);
    }

    public function insertRecord()
    {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        // Start session and retrieve inputs
        session_start();

        if (!isset($_POST['patientCont']) || !isset($_POST['patientDate'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient count and record date are mandatory']);
            exit;
        }

        $patientCount = filter_input(INPUT_POST, 'patientCont', FILTER_SANITIZE_NUMBER_INT);
        $recordDate = filter_input(INPUT_POST, 'patientDate', FILTER_SANITIZE_STRING);
        $details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_STRING);

        $checkRecord = $this->model->showRecord($recordDate);

        if ($checkRecord) {
            $this->updateRecord();
        } else {
            $result = $this->model->insertRecord($patientCount, $recordDate, $details);
        }

        print_r($result);
        die;

        if ($result) {
            $_SESSION['message'] = "Patient Count added successfully.";
            header("Location: ../../daily-patient-records.php");
        } else {
            $_SESSION['error'] = "Failed to add Patient Count.";
            header("Location: ../../daily-patient-records.php");
            exit;
        }
    }

    public function updateRecord()
    {
        $recordId = filter_input(INPUT_POST, 'record_id', FILTER_SANITIZE_NUMBER_INT);
        $patientCount = filter_input(INPUT_POST, 'patient_id', FILTER_SANITIZE_NUMBER_INT);
        $recordDate = filter_input(INPUT_POST, 'record_date', FILTER_SANITIZE_STRING);
        $details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_STRING);

        $result = $this->model->updateRecord($recordId, $patientCount, $recordDate, $details);

        if ($result) {
            $_SESSION['message'] = "Patient Count updated successfully.";
            header("Location: ../../daily-patient-records.php");
        } else {
            $_SESSION['error'] = "Failed to update Patient Count.";
            header("Location: ../../daily-patient-records.php");
            exit;
        }
    }

}

$controller = new DailyPatientRecordsController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if (!empty($action) && $action == 'updateRecord') {
            $controller->updateRecord();
        }
        // elseif (!empty($action) && $action == 'deleteCategory') {
        //     $controller->softDeleteCategory();
        // }
    } else {
        $controller->insertRecord();
    }

} else {
    $controller->showCategories();
}
