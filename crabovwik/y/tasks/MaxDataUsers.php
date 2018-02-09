<?php

namespace crabovwik\y\tasks;

use crabovwik\y\data\AbstractYRow;
use crabovwik\y\interfaces\TaskInterface;

class MaxDataUsersTask implements TaskInterface
{
    protected $usersCount = 5;

    protected $userOutputByteCountHash;

    public function __construct()
    {
        $this->userRequestCountHash = array();
    }

    public function getDescription()
    {
        return "# Поиск 5ти пользователей, отправивших наибольшее количество данных";
    }

    public function doLogic(AbstractYRow $row)
    {
        $srcUser = $row->getSrcUser();

        if ($srcUser == '') {
            return;
        }

        $value = isset($this->userOutputByteCountHash[$srcUser]) ? $this->userOutputByteCountHash[$srcUser] + $row->getOutputByte() : $row->getOutputByte();
        $this->userOutputByteCountHash[$srcUser] = $value;
    }

    public function getResult()
    {
        arsort($this->userOutputByteCountHash);

        $sliceLength = count($this->userOutputByteCountHash) >= $this->usersCount ? $this->usersCount : count($this->userOutputByteCountHash);

        return "'" . implode("', '", array_keys(array_slice($this->userOutputByteCountHash, 0, $sliceLength))) . "'";
    }
}
