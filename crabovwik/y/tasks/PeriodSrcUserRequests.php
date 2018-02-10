<?php

namespace crabovwik\y\tasks;

use crabovwik\y\data\AbstractYRow;
use crabovwik\y\data\GroupedLinkItems;
use crabovwik\y\data\LinkFileRow;
use crabovwik\y\interfaces\LinkItemInterface;
use crabovwik\y\interfaces\TaskInterface;

//class PeriodicRequest
//{
//    /** @var int */
//    protected $from;
//
//    /** @var int */
//    protected $to;
//
//    /** @var int */
//    protected $frequency;
//
//    /** @var int */
//    protected $count;
//
//    /** @var AbstractYRow  */
//    protected $request;
//
//    public function __construct($from, $to, AbstractYRow $request)
//    {
//        $this->from = $from;
//        $this->to = $to;
//        $this->request = $request;
//    }
//
//    public function getFrom()
//    {
//        return $this->from;
//    }
//
//    public function getTo()
//    {
//        return $this->to;
//    }
//
//    public function getFrequency()
//    {
//        return $this->frequency;
//    }
//
//    public function getCount()
//    {
//        return $this->count;
//    }
//
//    public function getRequest()
//    {
//        return $this->request;
//    }
//}
//
//class DataStruct
//{
//    /** @var int */
//    protected $from;
//
//    /** @var int */
//    protected $to;
//
//    /** @var AbstractYRow */
//    protected $prePrev;
//
//    /** @var AbstractYRow */
//    protected $prev;
//
//    /** @var AbstractYRow */
//    protected $current;
//
//    /** @var int */
//    protected $inRow;
//
//    /** @var PeriodicRequest[] */
//    protected $periodicDataStructList;
//
//    /** @var int */
//    protected $countToBePeriodic = 3;
//
//    public function __construct()
//    {
//        $this->inRow = 0;
//        $this->periodicDataStructList = array();
//    }
//
//    public function getPrePrev()
//    {
//        return $this->prePrev;
//    }
//
//    public function getPrev()
//    {
//        return $this->prev;
//    }
//
//    public function getCurrent()
//    {
//        return $this->current;
//    }
//
//    public function handle(AbstractYRow $row)
//    {
//        if ($this->prePrev === null) {
//            $this->prePrev = $row;
//            return;
//        }
//
//        if ($this->prev === null) {
//            $this->prev = $row;
//            return;
//        }
//
//        $currentPrevTimeInterval = $row->getUnixTimestamp() - $this->prev->getUnixTimestamp();
//        $prevPrePrevTimeInterval = $this->prev->getUnixTimestamp() - $this->prePrev->getUnixTimestamp();
//
//        if ($currentPrevTimeInterval === $prevPrePrevTimeInterval) {
//
//            if ($this->inRow === 0) {
//                $this->from = $row->getUnixTimestamp();
//            } else {
//                $this->to = $row->getUnixTimestamp();
//            }
//
//            $this->addInRow();
//        } else {
//            if ($this->isPeriodic()) {
//                $this->toPeriodicDataStructList();
//            }
//
//            $this->resetInRow();
//        }
//
//        $this->roll();
//    }
//
//    protected function addInRow()
//    {
//        $this->inRow++;
//    }
//
//    protected function isPeriodic() {
//        return $this->inRow < $this->countToBePeriodic;
//    }
//
//    protected function toPeriodicDataStructList()
//    {
//        $this->periodicDataStructList[] = new PeriodicRequest($this->from, $this->to, $this->prePrev);
//    }
//
//    protected function resetInRow()
//    {
//        $this->inRow = 0;
//    }
//
//    protected function roll()
//    {
//        $this->prePrev = $this->prev;
//        $this->prev = $this->current;
//        $this->current = null;
//    }
//
//    public function getPeriodicDataStructList()
//    {
//        return $this->periodicDataStructList;
//    }
//
//    public function getInRow()
//    {
//        return $this->inRow;
//    }
//}

class Linker
{
    /** @var GroupedLinkItems[] */
    protected $groupedItems;

    /** @var GroupedLinkItems[] */
    protected $pairedItems;

    /** @var LinkItemInterface[] */
    protected $unpairedItems;

    protected $groupedItemsLinksHistory;

    protected $pairedItemsLinksHistory;

    public function add(LinkItemInterface $linkItem)
    {
        $this->unpairedItems[] = $linkItem;
    }

    public function getGroupedLinks()
    {
        return $this->groupedItems;
    }

    protected function addGroupedItemsLinksHistory($id, $inId)
    {
        $this->groupedItemsLinksHistory[$id][] = $inId;
    }

    protected function addPairedItemsLinksHistory($id, $inId)
    {
        $this->pairedItemsLinksHistory[$id][] = $inId;
    }

    protected function removeGroupedItemsLinksHistory($id, $inId)
    {
        unset($this->groupedItemsLinksHistory[$id][$inId]);
    }

    protected function removePairedItemsLinksHistory($id, $inId)
    {
        unset($this->pairedItemsLinksHistory[$id][$inId]);
    }

    public function link()
    {
        $this->pairUnpairedItems();
        $this->groupUnpairedWithPairedItems();
        $this->mergePairedWithGroupedItems();
        $this->moveMultiPairedToGroupedItems();
    }

    protected function pairUnpairedItems()
    {
        $unpairedItemsCount = count($this->unpairedItems);

        if ($unpairedItemsCount < 2) {
            return;
        }

        for ($i = 0; $i < $unpairedItemsCount; $i++) {
            for ($j = $i + 1; $j < $unpairedItemsCount; $j++) {
                $groupedLinkItems = new GroupedLinkItems();

                $groupedLinkItems->add($this->unpairedItems[$i]);
                $groupedLinkItems->add($this->unpairedItems[$j]);

                $this->addPairedItemsLinksHistory($this->unpairedItems[$i]->getId(), $groupedLinkItems->getId());

                $this->pairedItems[$groupedLinkItems->getId()] = $groupedLinkItems;
            }
        }
    }

    protected function groupUnpairedWithPairedItems()
    {
        $unpairedItemsCount = count($this->unpairedItems);
        $pairedItemsCount = count($this->pairedItems);

        if ($unpairedItemsCount === 0 || $pairedItemsCount === 0) {
            return;
        }

        for ($i = 0; $i < $unpairedItemsCount; $i++) {
            $unpairedItem = $this->unpairedItems[$i];
            for ($j = 0; $j < $pairedItemsCount; $j++) {
                $pairedItem = $this->pairedItems[$j];

                if (in_array($pairedItem, $this->pairedItemsLinksHistory[$unpairedItem->getId()])) {
                    continue;
                }

                if ($pairedItem->isInGroupWith($unpairedItem)) {
                    $pairedItem->add($unpairedItem);
                    unset($this->unpairedItems[$i]);
                }
            }
        }

        // TODO: может быть стоит сделать динамическую чистку в процессе работы?
        $this->unpairedItems = array();
    }

    protected function mergePairedWithGroupedItems()
    {
        $pairedItemsCount = count($this->pairedItems);
        $groupedItemsCount = count($this->groupedItems);

        if ($pairedItemsCount === 0 || $groupedItemsCount === 0) {
            return;
        }

        for ($i = 0; $i < $pairedItemsCount; $i++) {
            $pairedItem = $this->pairedItems[$i];
            for ($j = 0; $j < $groupedItemsCount; $j++) {
                $groupedItem = $this->groupedItems[$j];

                if ($pairedItem->getInterval() == $groupedItem->getInterval()) {
                    foreach ($pairedItem->getLinkItems() as $pairedLinkItem) {
                        $groupedItem->add($pairedLinkItem);
                        $this->addGroupedItemsLinksHistory($pairedLinkItem->getId(), $groupedItem->getId());
                        $this->removePairedItemsLinksHistory($pairedLinkItem->getId(), $pairedItem->getId());
                    }
                }
            }
        }
    }

    protected function moveMultiPairedToGroupedItems()
    {
        $pairedItemsCount = count($this->pairedItems);

        if ($pairedItemsCount === 0) {
            return;
        }

        for ($i = 0; $i < $pairedItemsCount; $i++) {
            $pairedItem = $this->pairedItems[$i];

            $linkItems = $pairedItem->getLinkItems();

            if (count($linkItems) < 3) {
                return;
            }

            foreach ($linkItems as $linkItem) {
                $this->addGroupedItemsLinksHistory($linkItem->getId(), $pairedItem->getId());
                $this->removePairedItemsLinksHistory($linkItem->getId(), $pairedItem->getId());
            }

            $this->groupedItems[$pairedItem->getId()] = $pairedItem;

            unset($this->pairedItems[$i]);
        }

        // TODO: может быть стоит сделать динамическую чистку в процессе работы?
        $this->pairedItems = array();
    }
}

class PeriodSrcUserRequestsTask implements TaskInterface
{
    /** @var int */
    protected $usersCount = 5;

    /** @var Linker[] */
    protected $keyLinkerMap;

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
        $rowData = $row->getAsYArray();
        $rowData['_time'] = "\"{$rowData['_time']}\"";
        $row = new LinkFileRow(implode(',', $rowData));
        $srcUser = $row->getSrcUser();
        if ($srcUser == '') {
            return;
        }

        if (!isset($this->keyLinkerMap[$srcUser])) {
            $this->keyLinkerMap[$srcUser] = new Linker();
        }

        $this->keyLinkerMap[$srcUser]->add($row);
//        $this->keyLinkerMap[$srcUser]->link();
    }

    public function getResult()
    {
//        $resultData = array();
//        foreach ($this->dataStructs as $srcUser => $dataStruct) {
//            /** @var PeriodicRequest[] $periodicDataStructList */
//            $periodicDataStructList = $dataStruct->getPeriodicDataStructList();
//
//            foreach ($periodicDataStructList as $periodicDataStruct) {
//                $resultData[$srcUser][] = array(
//                    'from' => $periodicDataStruct->getFrom(),
//                    'to' => $periodicDataStruct->getTo(),
//                    'frequency' => $periodicDataStruct->getFrequency(),
//                    'count' => $periodicDataStruct->getCount(),
//                    'request' => $periodicDataStruct->getRequest()->getAsYArray(),
//                );
//            }
//        }
        $resultData = array();
        foreach ($this->keyLinkerMap as $srcUser => $linker) {
            $linker->link();
            $resultData[$srcUser] = $linker->getGroupedLinks();
        }

        return json_encode($resultData);
    }
}