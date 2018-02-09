<?php

namespace crabovwik\y\data;

abstract class AbstractRow
{
    protected function parseData($unpreparedData)
    {
        $preparedData = $this->prepareData($unpreparedData);

        array_shift($preparedData);

        if (count($preparedData) === 0) {
            throw new \RuntimeException("Incorrect unprepared data. Can't parse it.");
        }

        $this->initFields($preparedData);
    }

    abstract protected function prepareData($unpreparedData);

    abstract protected function initFields($preparedData);
}
