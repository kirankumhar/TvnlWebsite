<?php

if ($domainId > 0) {
    $stmt = $pdo->prepare(
        "SELECT id, eng_name , hin_name
         FROM domains 
         WHERE id = ?
         LIMIT 1"
    );
    $stmt->execute([$domainId]);
} else {
    $stmt = $pdo->prepare(
        "SELECT id, eng_name , hin_name
         FROM domains WHERE is_deleted = '0'"
    );
    $stmt->execute();
}

$domains_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
