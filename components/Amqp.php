<?php

namespace components;

use lib\AmqpRpcConfigInterface;

/**
 * Component for Amqp RPC
 * TODO можно разбить на три класса:
 * TODO родительский абстрактный с методами connect, channel, exchange,
 * TODO и 2 расширяющих его AmqpServer, AmqpClient
 * TODO но это спорный момент, решил не разбивать
 * Class Amqp
 * @package components
 */
class Amqp
{
    /**
     * @var AmqpRpcConfigInterface|null
     */
    private $oConfig = null;
    /**
     * @var \AMQPConnection|null
     */
    private $connect = null;
    /**
     * @var \AMQPChannel|null
     */
    private $channel = null;
    /**
     * @var \AMQPExchange|null
     */
    private $exchange = null;
    /**
     * @var \AMQPQueue|null
     */
    private $clientQueue = null;
    /**
     * @var \AMQPQueue|null
     */
    private $serverQueue = null;

    /**
     * Set configuration
     * @param AmqpRpcConfigInterface $oConfig
     */
    public function __construct(AmqpRpcConfigInterface $oConfig)
    {
        $this->oConfig = $oConfig;
    }

    /**
     * Work with client side of rpc
     * @param string $requestMessage
     * @return null
     */
    public function client(string $requestMessage): string
    {
        $connect = $this->connect();
        $channel = $this->channel($connect);
        $exchange = $this->exchange($channel);
        $clientQueue = $this->clientQueue($channel, $exchange);
        $this->clientPublishMessage($exchange, $clientQueue, $requestMessage);
        $responseMessage = $this->clientReadResult($clientQueue);
        return $responseMessage;
    }

    /**
     * Work with server side of rpc
     * @param callable $callback
     */
    public function server(callable $callback)
    {
        $connect = $this->connect();
        $channel = $this->channel($connect);
        $exchange = $this->exchange($channel);
        $serverQueue = $this->serverQueue($channel);
        $this->serverReadAndPublish($serverQueue, $exchange, $callback);
    }

    /**
     * Common connect
     * @return \AMQPConnection|null
     */
    private function connect(): \AMQPConnection
    {
        if (null === $this->connect) {
            $this->connect = new \AMQPConnection();
            $this->connect->setHost($this->oConfig->getHost());
            $this->connect->setLogin($this->oConfig->getLogin());
            $this->connect->setPassword($this->oConfig->getPassword());
            $this->connect->setVhost($this->oConfig->getVHost());
            $this->connect->connect();
        }
        return $this->connect;
    }

    /**
     * Common channel
     * @param \AMQPConnection $connect
     * @return \AMQPChannel|null
     */
    private function channel(\AMQPConnection $connect): \AMQPChannel
    {
        if (null === $this->channel) {
            $this->channel = new \AMQPChannel($connect);
        }
        return $this->channel;
    }

    /**
     * Common Exchange
     * @param \AMQPChannel $channel
     * @return \AMQPExchange|null
     */
    private function exchange(\AMQPChannel $channel): \AMQPExchange
    {
        if (null === $this->exchange) {
            $exchange = new \AMQPExchange($channel);
            $exchange->setName($this->oConfig->getExchangeName());
            $exchange->setType(AMQP_EX_TYPE_TOPIC);
            $exchange->setFlags(AMQP_DURABLE);
            $exchange->declare();
            $this->exchange = $exchange;
        }
        return $this->exchange;
    }

    /**
     * Queue for client side of rpc
     * @param \AMQPChannel $channel
     * @param \AMQPExchange $exchange
     * @return \AMQPQueue|null
     */
    private function clientQueue(\AMQPChannel $channel, \AMQPExchange $exchange): \AMQPQueue
    {
        if (null === $this->clientQueue) {
            $clientQueue = new \AMQPQueue($channel);
            $clientQueue->setFlags(AMQP_IFUNUSED | AMQP_AUTODELETE | AMQP_EXCLUSIVE);
            $clientQueue->declare();
            $clientQueue->bind($exchange->getName(), $clientQueue->getName());
            $this->clientQueue = $clientQueue;
        }
        return $this->clientQueue;
    }

    /**
     * Queue for server side of rpc
     * @param \AMQPChannel $channel
     * @return \AMQPQueue|null
     */
    private function serverQueue(\AMQPChannel $channel): \AMQPQueue
    {
        if (null === $this->serverQueue) {
            $serverQueue = new \AMQPQueue($channel);
            $serverQueue->setName($this->oConfig->getQueueName());
            $serverQueue->setFlags(AMQP_DURABLE);
            $serverQueue->declare();
            $serverQueue->bind($this->oConfig->getExchangeName(), $this->oConfig->getRoutingKeyName());
            $this->serverQueue = $serverQueue;
        }
        return $this->serverQueue;
    }

    /**
     * Publish message for client side of rpc
     * Send message to server side
     * @param \AMQPExchange $exchange
     * @param \AMQPQueue $clientQueue
     * @param $requestMessage
     */
    private function clientPublishMessage(\AMQPExchange $exchange, \AMQPQueue $clientQueue, $requestMessage)
    {
        $exchange->publish(
            $requestMessage,
            $this->oConfig->getRoutingKeyName(),
            AMQP_MANDATORY,
            [
                'delivery_mode' => $this->oConfig->getDeliveryMode(),
                'reply_to' => $clientQueue->getName(),
                'correlation_id' => sha1($clientQueue->getName())
            ]
        );
    }

    /**
     * Listen response from server side
     * @param \AMQPQueue $clientQueue
     * @return null
     */
    private function clientReadResult(\AMQPQueue $clientQueue)
    {
        $responseMessage = null;
        $clientQueue->consume(function (\AMQPEnvelope $env, \AMQPQueue $queue) use (&$responseMessage) {
            $responseMessage = $env->getBody();
            $queue->ack($env->getDeliveryTag());
            return false;
        });
        return $responseMessage;
    }

    /**
     * Listen request from client side and send response
     * TODO можно разбить на 2 метода, но спорно, усложняется понимания процесса
     * @param \AMQPQueue $serverQueue
     * @param \AMQPExchange $exchange
     * @param callable $callback
     */
    private function serverReadAndPublish(\AMQPQueue $serverQueue, \AMQPExchange $exchange, callable $callback)
    {
        $serverQueue->consume(function (\AMQPEnvelope $env, \AMQPQueue $queue) use ($callback, $exchange) {
            $responseMessage = $callback($env->getBody());
            $exchange->publish(
                $responseMessage,
                $env->getReplyTo(),
                AMQP_MANDATORY,
                ['correlation_id' => $env->getCorrelationId()]
            );
            $queue->ack($env->getDeliveryTag());
        });
    }
}