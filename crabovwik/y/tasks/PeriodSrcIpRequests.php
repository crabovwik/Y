<?php

namespace crabovwik\y\tasks;

use crabovwik\y\data\AbstractYRow;
use crabovwik\y\interfaces\TaskInterface;

class PeriodSrcIpRequestsTask implements TaskInterface
{
    public function getDescription()
    {
        return "# Поиск регулярных запросов (запросов выполняющихся периодически) по полю src_ip";
    }

    public function doLogic(AbstractYRow $row)
    {

    }

    public function getResult()
    {

    }
}