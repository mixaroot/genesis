<?php

namespace console;

use lib\Console;
use components\ParseCsv;
use components\Amqp;
use config\AmqpAlbumsPhotosConfig;

/**
 * Write albums and photos
 * Class GetAlbumsPhotos
 * @package console
 */
class WriteAlbumsPhotos extends Console
{
    const COLUMN_WITH_IDS = 0;
    const SLEEP_SEC = 1;

    public function write()
    {
        $ids = $this->getUsersIdsFromParameters();
        if (empty($ids)) {
            $ids = $this->getUsersIdsFromConsole();
        }
        $message = json_encode($ids);
        $responseMessage = $this->rpcRequest($message);
        $this->viewForWrite($responseMessage);
    }

    /**
     * @param $responseMessage
     */
    private function viewForWrite(string $responseMessage)
    {
        echo "\n Response: \n";
        echo $responseMessage;
    }

    /**
     * Get ids from console command parameters
     * @return array
     * @throws \Exception
     */
    private function getUsersIdsFromParameters(): array
    {
        if (!empty($this->consoleParameters['id'])) {
            return $this->getIdsFromParameter($this->consoleParameters['id']);
        }
        if (!empty($this->consoleParameters['path'])) {
            return $this->getIdsFromCsv($this->consoleParameters['path']);
        }
        return [];
    }

    /**
     * Get ids from console
     * @return array
     * @throws \Exception
     */
    private function getUsersIdsFromConsole(): array
    {
        $parameter = false;
        while (empty($parameter)) {
            echo "Please add user id or path to file with ids: ";
            $parameter = trim(fgets(STDIN));
            sleep(static::SLEEP_SEC);
        }
        if (is_numeric($parameter)) {
            return $this->getIdsFromParameter($parameter);
        } else {
            return $this->getIdsFromCsv($parameter);
        }
    }

    /**
     * Get ids from parameters
     * @param string $id
     * @return array
     * @throws \Exception
     */
    private function getIdsFromParameter(string $id): array
    {
        if (!is_numeric($id)) {
            throw new \Exception('id must be integer');
        }
        return [$id];
    }

    /**
     * Get ids from csv file
     * @param string $path
     * @return array
     * @throws \Exception
     */
    private function getIdsFromCsv(string $path): array
    {
        if (!file_exists($path)) {
            throw new \Exception('File does not exist');
        }
        $oParsedCsv = new ParseCsv();
        return $oParsedCsv->parse($path)->getColumn(static::COLUMN_WITH_IDS);
    }

    /**
     * Rpc request
     * @param string $message
     * @return null
     */
    private function rpcRequest(string $message): string
    {
        $oAmqp = new Amqp(new AmqpAlbumsPhotosConfig);
        return $oAmqp->client($message);
    }
}