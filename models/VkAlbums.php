<?php

namespace models;

use lib\Model;

/**
 * Class VkAlbums
 * @package models
 */
class VkAlbums extends Model
{
    /**
     * @param int $ownerId
     * @return array
     */
    public function getAlbumsWithPhotos(int $ownerId)
    {
        $sql = "SELECT va.id AS album_id, va.title, vp.id AS photo_id, vp.photo_75
                  FROM vk_albums AS va
                  LEFT JOIN vk_photos AS vp ON va.id = vp.album_id
                WHERE va.owner_id =:owner_id;";
        $sth = $this->getConnect()
            ->prepare($sql);
        $sth->bindValue(':owner_id', $ownerId, \PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param array $albums
     * @param array $albumsIds
     * @param int $ownerId
     * @throws \Exception
     */
    public function replaceAlbums(array $albums, array $albumsIds, int $ownerId)
    {
        $this->validationAlbums($albums);
        try {
            $this->getConnect()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->getConnect()->beginTransaction();
            $this->replace($albums);
            $this->delete($albumsIds, $ownerId);
            $this->getConnect()->commit();
        } catch (\Exception $e) {
            $this->getConnect()->rollBack();
            throw new \Exception('Update data error. Message: ' . $e->getMessage());
        }
    }

    /**
     * @param array $albums
     */
    private function validationAlbums(array $albums)
    {
        // TODO для тестового задания думаю можно не риализовывать
    }

    /**
     * Replace values
     * @param array $albums
     * @return bool
     */
    private function replace(array $albums): bool
    {
        $query = "REPLACE INTO vk_albums (id, thumb_id, owner_id, title, size, description, created, updated) VALUES ";
        $qPart = array_fill(0, count($albums), "(?, ?, ?, ?, ?, ?, ?, ?)");
        $query .= implode(",", $qPart);
        $stmt = $this->getConnect()->prepare($query);
        $i = 1;
        foreach ($albums as $item) {
            $stmt->bindValue($i++, $item['id'], \PDO::PARAM_INT);
            $stmt->bindValue($i++, $item['thumb_id'], \PDO::PARAM_INT);
            $stmt->bindValue($i++, $item['owner_id'], \PDO::PARAM_INT);
            $stmt->bindValue($i++, $item['title'], \PDO::PARAM_STR);
            $stmt->bindValue($i++, $item['size'], \PDO::PARAM_INT);
            $stmt->bindValue($i++, $item['description'], \PDO::PARAM_STR);
            $stmt->bindValue($i++, $item['created'], \PDO::PARAM_INT);
            $stmt->bindValue($i++, $item['updated'], \PDO::PARAM_INT);
        }
        return $stmt->execute();

    }

    /**
     * Delete old values
     * @param array $albumsIds
     * @param int $ownerId
     * @return bool
     */
    private function delete(array $albumsIds, int $ownerId): bool
    {
        $in = implode(',', array_fill(0, count($albumsIds), '?'));
        $stmt = $this->getConnect()->prepare("DELETE FROM vk_albums WHERE id NOT IN ($in) AND owner_id = ?;");
        foreach ($albumsIds as $k => $id) {
            $stmt->bindValue(($k + 1), $id);
        }
        $stmt->bindValue(count($albumsIds) + 1, $ownerId, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}