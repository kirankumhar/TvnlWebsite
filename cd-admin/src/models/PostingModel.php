<?php
require_once __DIR__ . '../../utils/ActivityLogger.php';

class PostingModel
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

    public function generateUniqueSixDigitID()
    {
        do {
            $id = mt_rand(100000, 999999);

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notices WHERE uniq_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $exists = $stmt->fetchColumn();
        } while ($exists > 0);
        return $id;
    }

    function generatePostingId()
    {
        $currentYear = date('y');
        $currentMonth = date('m');

        $counter = 1;

        $stmt = $this->pdo->query("SELECT MAX(id) AS last_posting_id FROM postings");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastPostingId = $result['last_posting_id'];

        if ($lastPostingId !== false) {
            $lastMonth = substr($lastPostingId, 2, 2);
            if ($lastMonth == $currentMonth) {
                $lastCounter = intval(substr($lastPostingId, -3));
                $counter = $lastCounter + 1;
            }
        }
        $formattedCounter = str_pad($counter, 3, '0', STR_PAD_LEFT);
        $postingId = $currentYear . $currentMonth . $formattedCounter;
        return $postingId;
    }

    public function saveData($data)
    {
        // print_r($data); die();
        $sql = "INSERT INTO notices (
                    domain_id, uniq_id, notice_category, notice_subcategory, notice_childsubcategory, notice_dated,
                    notice_ref_no, notice_title, notice_path,
                    notice_new_tag, notice_new_tag_days, ip_address, session_year, notice_url, url_tab_open
                ) VALUES (
                    :domainId, :uniq_id, :category, :sub_category, :child_sub_category, :dated,
                    :reference_no, :title, :attachment,
                    :new_flag, :new_no_of_days, :ip_address, :session_year, :external_url, :url_tab_open
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':domainId', $data['domain_id'], PDO::PARAM_INT);
        $stmt->bindParam(':uniq_id', $data['uniq_id'], PDO::PARAM_INT);
        $stmt->bindParam(':category', $data['categoryId'], PDO::PARAM_INT);
        $stmt->bindParam(':sub_category', $data['sub_category'], PDO::PARAM_INT);
        $stmt->bindParam(':child_sub_category', $data['child_sub_category'], PDO::PARAM_INT);
        $stmt->bindParam(':dated', $data['dated'], PDO::PARAM_STR);
        $stmt->bindParam(':reference_no', trim($data['reference_no']), PDO::PARAM_STR);
        $stmt->bindParam(':title', trim($data['title']), PDO::PARAM_STR);
        $stmt->bindParam(':attachment', $data['attachment'], PDO::PARAM_STR);
        $stmt->bindParam(':new_flag', $data['new_flag'], PDO::PARAM_STR);
        $stmt->bindParam(':new_no_of_days', trim($data['new_no_of_days']), PDO::PARAM_INT);
        $stmt->bindParam(':ip_address', $data['ip_address'], PDO::PARAM_STR);
        $stmt->bindParam(':session_year', $data['session_year'], PDO::PARAM_STR);
        $stmt->bindParam(':external_url', $data['attachmentURL'], PDO::PARAM_STR);
        $stmt->bindParam(':url_tab_open', $data['new_tab_open'], PDO::PARAM_STR);
        $stmt->execute();

        $this->logger->log('notices', $data['uniq_id'], 'INSERT', null, null, json_encode($data), $_SESSION['user_id'], $this->loggedId);
        return true;
    }
    public function fetchSubCategoriesByCategoryId($categoryId)
    {
        $stmt = $this->pdo->prepare("SELECT id, sub_category_name FROM sub_category WHERE category_id = :categoryId AND is_deleted = '0'");
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchTypeByCategoryId($categoryId)
    {
        $stmt = $this->pdo->prepare("
                SELECT * FROM sy_fy 
                WHERE type = (SELECT a.type_id FROM category_master a WHERE a.id = :categoryId);");
        $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchTypeCategoryId($categoryId, $type = '')
    {
        $addType = '';
        if (!empty($type)) {
            $addType = 'AND p.type = :type';
        }

        $sql = "SELECT p.*, cm.category_name as cat_name, sc.sub_category_name 
                FROM postings p 
                INNER JOIN category_master cm ON p.category = cm.id 
                LEFT JOIN sub_category sc on p.sub_category = sc.id 
                WHERE p.category = :categoryId $addType AND p.is_deleted = '0' 
                ORDER BY p.created_on ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
        if (!empty($type)) {
            $stmt->bindValue(':type', $type, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function getCategoryName($catId)
    {
        $stmt = $this->pdo->prepare("SELECT category_name FROM category_master WHERE id = :categoryId AND is_deleted = '0'");
        $stmt->bindParam(':categoryId', $catId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubCategoryName($subcatId)
    {
        $stmt = $this->pdo->prepare("SELECT sub_category_name FROM sub_category WHERE id = :subcategoryId AND is_deleted = '0'");
        $stmt->bindParam(':subcategoryId', $subcatId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deletePost($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM notices WHERE uniq_id = :id AND is_deleted = '0'");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            $sql = "UPDATE notices SET is_deleted='1' WHERE uniq_id= :id";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
        }

        $this->logger->log(
            'notices',
            $id,
            'DELETE',
            'is_deleted',
            '0',
            '1',
            $_SESSION['user_id'],
            $this->loggedId
        );
        return true;
    }

    public function deleteAttach($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM notices WHERE uniq_id = :id AND is_deleted = '0'");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            $sql = "UPDATE notices SET notice_path=null WHERE uniq_id= :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }

    public function restorePost($id)
    {

        $stmt = $this->pdo->prepare("SELECT * FROM notices WHERE uniq_id = :id AND is_deleted = '1'");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM category_master WHERE id = :id AND is_deleted = '1'");
        $stmt->bindParam(':id', $data[0]['category'], PDO::PARAM_INT);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            return 'no_cat';
        }

        if ($data) {
            $sql = "UPDATE notices SET is_deleted='0' WHERE id= :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }

    public function getType($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM sy_fy WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
