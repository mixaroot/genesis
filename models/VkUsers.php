<?php

namespace models;

use lib\Model;

/**
 * Class VkUsers
 * @package models
 */
class VkUsers extends Model
{
    /**
     * @param array $user
     * @return bool
     */
    public function replaceUser(array $user): bool
    {
        $this->validationUser($user);
        $sql = "REPLACE INTO vk_users (id, first_name, last_name) VALUES (:id, :first_name, :last_name);";
        $stm = $this->getConnect()
            ->prepare($sql);
        $stm->bindValue(':id', $user['id'], \PDO::PARAM_INT);
        $stm->bindValue(':first_name', $user['first_name'], \PDO::PARAM_STR);
        $stm->bindValue(':last_name', $user['last_name'], \PDO::PARAM_STR);
        return $stm->execute();
    }

    /**
     * @paramint  $id
     * @return array
     */
    public function getUser(int $id)
    {
        $sql = "SELECT first_name, last_name FROM vk_users WHERE id =:id;";
        $sth = $this->getConnect()
            ->prepare($sql);
        $sth->bindValue(':id', $id, \PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetch();
    }

    /**
     * @param array $user
     */
    private function validationUser(array $user)
    {
        // TODO для тестового задания думаю можно не риализовывать
    }
}