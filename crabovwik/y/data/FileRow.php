<?php

namespace crabovwik\y\data;

class FileRow extends AbstractYRow
{
    public function __construct($line)
    {
        $this->parseData($line);
    }

    protected function prepareData($unpreparedData)
    {
        preg_match($this->getRegexp(), $unpreparedData, $regexResult);
        return $regexResult;
    }

    protected function getRegexp()
    {
        return '#"(.*)",(\w*),(\w*),(\d*),(\w*),(\w*),(\d*),(\d*),(\d*)#';
    }

    protected function initFields($regexResult)
    {
        list(
            $this->time,
            $this->srcUser, $this->srcIp, $this->srcPort,
            $this->dstUser, $this->dstIp, $this->dstPort,
            $this->inputByte, $this->outputByte
            ) = $regexResult;
    }
}
