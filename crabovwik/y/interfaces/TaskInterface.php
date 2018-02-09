<?php

namespace crabovwik\y\interfaces;

use crabovwik\y\data\AbstractYRow;

interface TaskInterface
{
    public function getDescription();

    public function doLogic(AbstractYRow $row);

    public function getResult();
}
