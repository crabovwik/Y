<?php

namespace crabovwik\y\tasks;

use crabovwik\y\data\AbstractYRow;
use crabovwik\y\interfaces\TaskInterface;

class PeriodicRequest
{
    /** @var int */
    protected $from;

    /** @var int */
    protected $to;

    /** @var int */
    protected $frequency;

    /** @var int */
    protected $count;

    /** @var AbstractYRow  */
    protected $request;

    public function __construct($from, $to, AbstractYRow $request)
    {
        $this->from = $from;
        $this->to = $to;
        $this->request = $request;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getFrequency()
    {
        return $this->frequency;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getRequest()
    {
        return $this->request;
    }
}

class DataStruct
{
    /** @var int */
    protected $from;

    /** @var int */
    protected $to;

    /** @var AbstractYRow */
    protected $prePrev;

    /** @var AbstractYRow */
    protected $prev;

    /** @var AbstractYRow */
    protected $current;

    /** @var int */
    protected $inRow;

    /** @var PeriodicRequest[] */
    protected $periodicDataStructList;

    /** @var int */
    protected $countToBePeriodic = 3;

    public function __construct()
    {
        $this->inRow = 0;
        $this->periodicDataStructList = array();
    }

    public function getPrePrev()
    {
        return $this->prePrev;
    }

    public function getPrev()
    {
        return $this->prev;
    }

    public function getCurrent()
    {
        return $this->current;
    }

    public function handle(AbstractYRow $row)
    {
        if ($this->prePrev === null) {
            $this->prePrev = $row;
            return;
        }

        if ($this->prev === null) {
            $this->prev = $row;
            return;
        }

        $currentPrevTimeInterval = $row->getUnixTimestamp() - $this->prev->getUnixTimestamp();
        $prevPrePrevTimeInterval = $this->prev->getUnixTimestamp() - $this->prePrev->getUnixTimestamp();

        if ($currentPrevTimeInterval === $prevPrePrevTimeInterval) {

            if ($this->inRow === 0) {
                $this->from = $row->getUnixTimestamp();
            } else {
                $this->to = $row->getUnixTimestamp();
            }

            $this->addInRow();
        } else {
            if ($this->isPeriodic()) {
                $this->toPeriodicDataStructList();
            }

            $this->resetInRow();
        }

        $this->roll();
    }

    protected function addInRow()
    {
        $this->inRow++;
    }

    protected function isPeriodic() {
        return $this->inRow < $this->countToBePeriodic;
    }

    protected function toPeriodicDataStructList()
    {
        $this->periodicDataStructList[] = new PeriodicRequest($this->from, $this->to, $this->prePrev);
    }

    protected function resetInRow()
    {
        $this->inRow = 0;
    }

    protected function roll()
    {
        $this->prePrev = $this->prev;
        $this->prev = $this->current;
        $this->current = null;
    }

    public function getPeriodicDataStructList()
    {
        return $this->periodicDataStructList;
    }

    public function getInRow()
    {
        return $this->inRow;
    }
}

class Oh
{
    protected $links;

    public function __construct()
    {
        $this->links = array();
    }

    public function handle(AbstractYRow $row)
    {
        $link = $this->getLink($row);

        $isLinked = false;
        foreach ($link as $innerLink) {
            if ($innerLink->isYour($row)) {
                $innerLink->add($row);
                $isLinked = true;
            }
        }

        if (!$isLinked) {
            $link[] =
        }
    }

    protected function addFirstInnerLink($key, AbstractYRow $row)
    {
        $this->links[$key] = $row;
    }

    public function &getLink(AbstractYRow $row)
    {
        $srcUser = $row->getSrcUser();

        if ($srcUser == '') {
            return null;
        }

        if (!isset($this->links[$srcUser])) {
            $this->links[$srcUser] = array();
        }

        return $this->links[$srcUser];
    }

    public function deleteLink($link)
}

class PeriodSrcUserRequestsTask implements TaskInterface
{
    /** @var int */
    protected $usersCount = 5;

    /** @var DataStruct[] */
    protected $dataStructs;

    public function __construct()
    {
        $this->dataStructs = array();
    }

    public function getDescription()
    {
        return "# Поиск регулярных запросов (запросов выполняющихся периодически) по полю src_user";
    }

    public function doLogic(AbstractYRow $row)
    {
        $srcUser = $row->getSrcUser();
//        if ($srcUser == '') {
//            return;
//        }

        if (!isset($this->dataStructs[$srcUser])) {
            $this->dataStructs[$srcUser] = new DataStruct();
        }

        $this->dataStructs[$srcUser]->handle($row);
    }

    public function getResult()
    {
        $resultData = array();
        foreach ($this->dataStructs as $srcUser => $dataStruct) {
            /** @var PeriodicRequest[] $periodicDataStructList */
            $periodicDataStructList = $dataStruct->getPeriodicDataStructList();

            foreach ($periodicDataStructList as $periodicDataStruct) {
                $resultData[$srcUser][] = array(
                    'from' => $periodicDataStruct->getFrom(),
                    'to' => $periodicDataStruct->getTo(),
                    'frequency' => $periodicDataStruct->getFrequency(),
                    'count' => $periodicDataStruct->getCount(),
                    'request' => $periodicDataStruct->getRequest()->getAsYArray(),
                );
            }
        }

        return json_encode($resultData);
    }
}