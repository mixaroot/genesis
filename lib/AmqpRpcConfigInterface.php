<?php

namespace lib;

/**
 * Amqp configuration interface for rpc
 * Interface AmqpRpcConfigInterface
 * @package config
 */
interface AmqpRpcConfigInterface
{
    /**
     * @return string
     */
    public function getHost(): string;

    /**
     * @return string
     */
    public function getLogin(): string;

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @return string
     */
    public function getVHost(): string;

    /**
     * @return string
     */
    public function getExchangeName(): string;

    /**
     * @return string
     */
    public function getQueueName(): string;

    /**
     * @return string
     */
    public function getRoutingKeyName(): string;

    /**
     * @return int
     */
    public function getDeliveryMode(): int;
}