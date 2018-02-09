<?php

namespace crabovwik\y\tasks;

use crabovwik\y\data\AbstractYRow;
use crabovwik\y\interfaces\TaskInterface;

class MaxRequestUsersTask implements TaskInterface
{
    protected $usersCount = 5;

    protected $userRequestCountHash;

    public function __construct()
    {
        $this->userRequestCountHash = array();
    }

    public function getDescription()
    {
        return "# Поиск 5ти пользователей, сгенерировавших наибольшее количество запросов";
    }

    public function doLogic(AbstractYRow $row)
    {
        $srcUser = $row->getSrcUser();

        if ($srcUser == '') {
            return;
        }

        $value = isset($this->userRequestCountHash[$srcUser]) ? $this->userRequestCountHash[$srcUser] + 1 : 1;
        $this->userRequestCountHash[$srcUser] = $value;
    }

    public function getResult()
    {
        arsort($this->userRequestCountHash);

        $sliceLength = count($this->userRequestCountHash) >= $this->usersCount ? $this->usersCount : count($this->userRequestCountHash);

        return "'" . implode("', '", array_keys(array_slice($this->userRequestCountHash, 0, $sliceLength))) . "'";
    }
}