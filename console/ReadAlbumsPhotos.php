<?php

namespace console;

use lib\Console;
use models\VkUsers;
use models\VkAlbums;
use config\MysqlConfig;

/**
 * Class ReadAlbumsPhotos
 * @package console
 */
class ReadAlbumsPhotos extends Console
{
    /**
     * @throws \Exception
     */
    public function read()
    {
        $userId = $this->getUserId();
        $oMysqlConfig = new MysqlConfig();
        $oVkUsers = new VkUsers($oMysqlConfig);
        $user = $oVkUsers->getUser($userId);
        $oVkAlbums = new VkAlbums($oMysqlConfig);
        $albumsWithPhotos = $oVkAlbums->getAlbumsWithPhotos($userId);
        $this->view($user, $albumsWithPhotos);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getUserId()
    {
        $userId = $this->consoleParameters['id'];
        if (!is_numeric($userId)) {
            throw new \Exception('Incorrect user id, must be numeric');
        }
        return (int)$userId;
    }

    /**
     * @param array $user
     * @param array $albumsWithPhotos
     */
    private function view(array $user, array $albumsWithPhotos)
    {
        $userOut = "First name: {$user['first_name']} Last name: {$user['last_name']}";
        echo "Start to out photos for user ($userOut)\n";
        foreach ($albumsWithPhotos as $item) {
            echo "{$item['photo_75']}\n";
        }
        echo "End to out photos for user ($userOut)\n";
    }
}