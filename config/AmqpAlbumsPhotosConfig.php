<?php

namespace config;

use lib\AmqpRpcConfigInterface;

/**
 * Config for albums and photos
 * Class AmqpConfig
 * @package config
 */
class AmqpAlbumsPhotosConfig implements AmqpRpcConfigInterface
{
    /**
     * @var string
     */
    private $host = 'localhost';
    /**
     * @var string
     */
    private $login = 'guest';
    /**
     * @var string
     */
    private $passsword = 'guest';
    /**
     * @var string
     */
    private $vHost = '/';
    /**
     * @var string
     */
    private $exchangeName = 'exchange';
    /**
     * @var string
     */
    private $queueName = 'queue';
    /**
     * @var string
     */
    private $routingKey = 'test';
    /**
     * @var int
     */
    private $deliveryMode = 2;

    /**
     * @inheritdoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritdoc
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @inheritdoc
     */
    public function getPassword(): string
    {
        return $this->passsword;
    }

    /**
     * @inheritdoc
     */
    public function getVHost(): string
    {
        return $this->vHost;
    }

    /**
     * @inheritdoc
     */
    public function getExchangeName(): string
    {
        return $this->exchangeName;
    }

    /**
     * @inheritdoc
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * @inheritdoc
     */
    public function getRoutingKeyName(): string
    {
        return $this->routingKey;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryMode(): int
    {
        return $this->deliveryMode;
    }
}