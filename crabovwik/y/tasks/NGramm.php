<?php

namespace crabovwik\y\tasks;

use crabovwik\y\data\AbstractYRow;
use crabovwik\y\interfaces\TaskInterface;

class NGrammTask implements TaskInterface
{
    public function getDescription()
    {
        return "
        # Рассматривая события сетевого трафика как символы неизвестного языка,\n
        # найти 5 наиболее устойчивых N-грамм журнала событий\n
        # (текста на неизвестном языке)";
    }

    public function doLogic(AbstractYRow $row)
    {

    }

    public function getResult()
    {

    }
}