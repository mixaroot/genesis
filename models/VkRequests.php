<?php

namespace models;

use \GuzzleHttp\Client;
use \GuzzleHttp\Promise;
use \GuzzleHttp\ClientInterface;
use \Psr\Http\Message\ResponseInterface;

/**
 * Model for request to vk
 * Class VkRequests
 * @package models
 */
class VkRequests
{
    const SUCCESS_CODE = 200;

    const CONNECT_TIMEOUT = 10;

    const API_VERSION = '5.62';

    const BASE_URL = 'https://api.vk.com/method/';
    /**
     * @var null
     */
    private $client = null;

    /**
     * Get common information about user
     * @param int $ownerId
     * @return array
     * @throws \Exception
     */
    public function getUser(int $ownerId): array
    {
        $response = $this->getClient()->request('GET', 'users.get', [
            'query' => [
                'user_ids' => $ownerId,
                'v' => static::API_VERSION
            ]
        ]);
        $this->validationResponse($response);
        $users = json_decode($response->getBody(), true);
        $this->validationUsers($users);
        return $users['response'][0];
    }

    /**
     * Get all user albums
     * @param int $ownerId
     * @return bool|mixed
     */
    public function getAlbums(int $ownerId)
    {
        $response = $this->getClient()->request('GET', 'photos.getAlbums', [
            'query' => [
                'owner_id' => $ownerId,
                'v' => static::API_VERSION,
                'need_system' => 1
            ]
        ]);
        $this->validationResponse($response);
        $albums = json_decode($response->getBody(), true);
        $this->validateAlbums($albums);
        return $albums['response']['items'];
    }

    /**
     * @param int $ownerId
     * @param int $albumId
     * @return mixed
     * @throws \Exception
     */
    public function getPhotosFromAlbum(int $ownerId, int $albumId)
    {
        $response = $this->getClient()->request('GET', 'photos.get', [
            'query' => [
                'owner_id' => $ownerId,
                'v' => static::API_VERSION,
                'album_id' => $albumId
            ]
        ]);
        $this->validationResponse($response);
        $photos = json_decode($response->getBody(), true);
        $this->validatePhotos($photos);
        return $photos['response']['items'];
    }

    /**
     * @return Client|null
     */
    private function getClient(): ClientInterface
    {
        if (null === $this->client) {
            $this->client = new Client([
                'base_uri' => static::BASE_URL,
                'timeout' => static::CONNECT_TIMEOUT
            ]);
        }
        return $this->client;
    }

    /**
     * Validation response
     * TODO вместо простой валидации, стоит добавит несколько попыток для некоторых кодов ответа
     * TODO для тестового задания думаю можно не риализовывать
     * @param ResponseInterface $response
     * @throws \Exception
     */
    private function validationResponse(ResponseInterface $response)
    {
        if (static::SUCCESS_CODE != $response->getStatusCode()) {
            throw new \Exception('Did not get response code 200. Code: ' .
                $response->getStatusCode() .
                '. Response body: ' . $response->getBody());
        }
    }

    /**
     * @param $users
     */
    private function validationUsers($users)
    {
        //TODO валидация, для тестового задания думаю можно не риализовывать
    }

    /**
     * @param $albums
     */
    private function validateAlbums($albums)
    {
        //TODO валидация, для тестового задания думаю можно не риализовывать
    }

    /**
     * @param $photos
     */
    private function validatePhotos($photos)
    {
        //TODO валидация, для тестового задания думаю можно не риализовывать
    }
}