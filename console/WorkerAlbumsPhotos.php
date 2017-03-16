<?php

namespace console;

use lib\Console;
use components\Amqp;
use config\AmqpAlbumsPhotosConfig;
use models\VkRequests;
use models\VkUsers;
use models\VkAlbums;
use models\VkPhotos;
use config\MysqlConfig;

/**
 * Class WorkerAlbumsPhotos
 * @package console
 */
class WorkerAlbumsPhotos extends Console
{
    /**
     * @var VkRequests|null
     */
    private $oVkRequests = null;
    /**
     * @var MysqlConfig|null
     */
    private $oMysqlConfig = null;
    /**
     * @var VkUsers|null
     */
    private $oVkUsers = null;
    /**
     * @var VkAlbums|null
     */
    private $oVkAlbums = null;
    /**
     * @var VkPhotos|null
     */
    private $oVkPhotos = null;

    /**
     * Write data about users
     */
    public function write()
    {
        $oAmqp = new Amqp(new AmqpAlbumsPhotosConfig);
        $oAmqp->server($this->getWorkFunction());
    }

    /**
     * Callback function for response to client side
     * @return \Closure
     */
    private function getWorkFunction()
    {
        return function ($data) {
            try {
                $this->workWithMessage($data);
            } catch (\Throwable $ex) {
                $this->workWithException($ex);
                return 'Error: ' . $ex->getMessage();
            }
            return "Wrote users: $data\n";
        };
    }

    /**
     * Method for work with error
     * Something like logs
     * @param \Throwable $ex
     * @throws \Exception
     */
    private function workWithException(\Throwable $ex)
    {

    }

    /**
     * Work with message from client side request
     * @param $data
     */
    private function workWithMessage($data)
    {
        $ids = json_decode($data, true);
        $this->validationIds($ids);
        $this->initProperties();
        $this->workWithUsers($ids);
    }

    /**
     * Init objects for work with message
     */
    private function initProperties()
    {
        $this->oVkRequests = new VkRequests();
        $this->oMysqlConfig = new MysqlConfig();
        $this->oVkUsers = new VkUsers($this->oMysqlConfig);
        $this->oVkAlbums = new VkAlbums($this->oMysqlConfig);
        $this->oVkPhotos = new VkPhotos($this->oMysqlConfig);
    }

    /**
     * Write user information
     * @param array $ids
     */
    private function workWithUsers(array $ids)
    {
        foreach ($ids as $id) {
            $ownerId = (int)$id;
            $user = $this->oVkRequests->getUser($ownerId);
            if (!empty($user)) {
                $this->oVkUsers->replaceUser($user);
                $this->workWithAlbums($ownerId);
            }
        }
    }

    /**
     * Write albums information
     * @param int $ownerId
     * @throws \Exception
     */
    private function workWithAlbums(int $ownerId)
    {
        $albums = $this->oVkRequests->getAlbums($ownerId);
        if (!empty($albums)) {
            $albumsIds = array_column($albums, 'id');
            $this->oVkAlbums->replaceAlbums($albums, $albumsIds, $ownerId);
            $this->workWithPhotos($ownerId, $albumsIds);
        }
    }

    /**
     * Write photos information
     * @param int $ownerId
     * @param array $albumsIds
     * @throws \Exception
     */
    private function workWithPhotos(int $ownerId, array $albumsIds)
    {
        foreach ($albumsIds as $albumId) {
            $photos = $this->oVkRequests->getPhotosFromAlbum($ownerId, $albumId);
            $photosIds = array_column($photos, 'id');
            if (!empty($photos)) {
                $this->oVkPhotos->replacePhotos($photos, $photosIds, $albumId, $ownerId);
            }
        }
    }

    /**
     * @param array $ids
     */
    private function validationIds(array $ids)
    {
        //TODO валидация, для тестового задания можно не риализовывать
    }
}