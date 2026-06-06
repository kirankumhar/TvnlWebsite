<?php
require_once __DIR__ . '../../utils/ActivityLogger.php';

class UpdatePostModel
{
    private $pdo;
    private $logger;
    private $loggedId;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->logger = new ActivityLogger($pdo);
        $this->loggedId = $_SESSION['login_id'];
    }

    public function updatePosting($data)
    {
        // Fetch old data
        $oldDataStmt = $this->pdo->prepare("SELECT * FROM notices WHERE uniq_id = :id");
        $oldDataStmt->execute([':id' => $data['uniq_id']]);
        $oldData = $oldDataStmt->fetch(PDO::FETCH_ASSOC);

        $sql = "UPDATE notices SET
                    domain_id = :domainId,
                    notice_category = :categoryId,
                    notice_subcategory = :sub_category,
                    notice_childsubcategory = :child_sub_category,
                    notice_dated = :notice_dated,
                    notice_ref_no = :notice_ref_no,
                    notice_title = :notice_title,
                    notice_path = :attachment,
                    notice_new_tag = :notice_new_tag,
                    notice_new_tag_days = :notice_new_tag_days,
                    status = :status,
                    ip_address = :ip_address,
                    session_year = :session_year,
                    updated_by = :updated_by,
                    notice_url = :external_url,
                    url_tab_open = :url_tab_open
                WHERE uniq_id = :uniq_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':uniq_id', $data['uniq_id'], PDO::PARAM_INT);
        $stmt->bindParam(':domainId', $data['domainId'], PDO::PARAM_INT);
        $stmt->bindParam(':categoryId', $data['categoryId'], PDO::PARAM_INT);
        $stmt->bindParam(':sub_category', $data['sub_category'], PDO::PARAM_INT);
        $stmt->bindParam(':child_sub_category', $data['child_sub_category'], PDO::PARAM_INT);
        $stmt->bindParam(':notice_dated', $data['notice_dated'], PDO::PARAM_STR);
        $stmt->bindParam(':notice_ref_no', trim($data['notice_ref_no']), PDO::PARAM_STR);
        $stmt->bindParam(':notice_title', trim($data['notice_title']), PDO::PARAM_STR);
        $stmt->bindParam(':attachment', $data['attachment'], PDO::PARAM_STR);
        $stmt->bindParam(':notice_new_tag', trim($data['notice_new_tag']), PDO::PARAM_STR);
        $stmt->bindParam(':notice_new_tag_days', trim($data['notice_new_tag_days']), PDO::PARAM_INT);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
        $stmt->bindParam(':ip_address', $data['ip_address'], PDO::PARAM_STR);
        $stmt->bindParam(':session_year', $data['session_year'], PDO::PARAM_STR);
        $stmt->bindParam(':updated_by', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':external_url', $data['external_url'], PDO::PARAM_STR);
        $stmt->bindParam(':url_tab_open', $data['new_tab_open'], PDO::PARAM_STR);
        $stmt->execute();

        $newData = json_encode($data);

        $this->logger->log(
            'notices',
            $data['uniq_id'],
            'UPDATE',
            NULL,
            json_encode($oldData),
            $newData,
            $_SESSION['user_id'],
            $this->loggedId
        );
    }
}
