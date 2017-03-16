<?php

namespace models;

use lib\Model;

/**
 * Class VkPhotos
 * @package models
 */
class VkPhotos extends Model
{
    /**
     * @var array
     */
    private $addFields = [
        'photo_75' => '',
        'photo_130' => '',
        'photo_604' => '',
        'photo_807' => '',
        'photo_1280' => '',
        'photo_2560' => '',
        'text' => ''
    ];

    /**
     * @param array $photos
     * @param array $photosIds
     * @param int $albumId
     * @param int $ownerId
     * @throws \Exception
     */
    public function replacePhotos(array $photos, array $photosIds, int $albumId, int $ownerId)
    {
        $this->validationPhotos($photos);
        $this->addFieldsForPhotos($photos);
        try {
            $this->getConnect()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->getConnect()->beginTransaction();
            $this->replace($photos);
            $this->delete($photosIds, (int)$albumId, $ownerId);
            $this->getConnect()->commit();
        } catch (\Exception $e) {
            $this->getConnect()->rollBack();
            throw new \Exception('Update data error. Message: ' . $e->getMessage());
        }
    }

    /**
     * @param array $photos
     */
    private function validationPhotos(array $photos)
    {
        // TODO для тестового задания думаю можно не риализовывать
    }

    /**
     * @param array $photos
     * @return bool
     */
    private function replace(array $photos): bool
    {
        $query = "REPLACE INTO vk_photos
                (id, album_id, owner_id, photo_75, photo_130, photo_604, photo_807, photo_1280, photo_2560, width, height, text)
                VALUES ";
        $qPart = array_fill(0, count($photos), "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $query .= implode(",", $qPart);
        $stmt = $this->getConnect()->prepare($query);
        $i = 1;
        foreach ($photos as $photo) {
            $stmt->bindValue($i++, $photo['id'], \PDO::PARAM_INT);
            $stmt->bindValue($i++, $photo['album_id'], \PDO::PARAM_INT);
            $stmt->bindValue($i++, $photo['owner_id'], \PDO::PARAM_INT);
            $stmt->bindValue($i++, $photo['photo_75'], \PDO::PARAM_STR);
            $stmt->bindValue($i++, $photo['photo_130'], \PDO::PARAM_STR);
            $stmt->bindValue($i++, $photo['photo_604'], \PDO::PARAM_STR);
            $stmt->bindValue($i++, $photo['photo_807'], \PDO::PARAM_STR);
            $stmt->bindValue($i++, $photo['photo_1280'], \PDO::PARAM_STR);
            $stmt->bindValue($i++, $photo['photo_2560'], \PDO::PARAM_STR);
            $stmt->bindValue($i++, $photo['width'], \PDO::PARAM_INT);
            $stmt->bindValue($i++, $photo['height'], \PDO::PARAM_INT);
            $stmt->bindValue($i++, $photo['text'], \PDO::PARAM_STR);
        }
        return $stmt->execute();
    }

    /**
     * @param array $photosIds
     * @param int $albumId
     * @param int $ownerId
     * @return bool
     */
    private function delete(array $photosIds, int $albumId, int $ownerId): bool
    {
        $in = implode(',', array_fill(0, count($photosIds), '?'));
        $stmt = $this->getConnect()->prepare("DELETE FROM vk_photos WHERE id NOT IN ($in) AND album_id = ? AND owner_id = ?;");
        foreach ($photosIds as $k => $id) {
            $stmt->bindValue(($k + 1), $id);
        }
        $stmt->bindValue(count($photosIds) + 1, $albumId, \PDO::PARAM_INT);
        $stmt->bindValue(count($photosIds) + 2, $ownerId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * @param $photos
     */
    private function addFieldsForPhotos(array &$photos)
    {
        foreach ($photos as &$photo) {
            $photo = array_merge($this->addFields, $photo);
        }
    }
}