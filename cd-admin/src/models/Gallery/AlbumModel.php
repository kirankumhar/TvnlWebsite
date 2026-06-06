<?php
require_once "../../database/Database.php";

class AlbumModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function createAlbum($domainId, $name_en, $name_hi, $description_en, $location, $event_date, $type, $uid, $session_year)
    {
        $sql = "INSERT INTO albums (domain_id, name_en, name_hi, description_en,  location, event_date, created_at, type, uniq_id, session_year)
                VALUES ( ?, ?, ?, ?, ?, ?, NOW(), ?,?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$domainId, $name_en, $name_hi, $description_en, $location, $event_date, $type, $uid, $session_year]);
        return $this->pdo->lastInsertId();
    }

    public function addPhotoToAlbum($album_id, $file_path, $caption_en)
    {
        $sql = "INSERT INTO photos (album_id, file_path, caption_en, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$album_id, $file_path, $caption_en]);
        return $this->pdo->lastInsertId();
    }

    public function setCoverPhoto($album_id, $photo_id)
    {
        $sql = "UPDATE albums SET cover_photo_id = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$photo_id, $album_id]);
    }

    public function addVideosAlbum($album_id, $caption_en, $link)
    {
        $sql = "INSERT INTO videos (albums_id, video_link, video_title, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$album_id, $link, $caption_en]);
        return $this->pdo->lastInsertId();
    }

    public function setCoverVideo($album_id, $video_id)
    {
        $sql = "UPDATE albums SET cover_video_id = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$video_id, $album_id]);
    }
}
