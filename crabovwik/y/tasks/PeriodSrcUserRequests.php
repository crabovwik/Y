<?php

namespace crabovwik\y\tasks;

use crabovwik\y\data\AbstractYRow;
use crabovwik\y\data\GroupedLinkItems;
use crabovwik\y\data\LinkFileRow;
use crabovwik\y\data\PairedLinkFileRow;
use crabovwik\y\interfaces\LinkItemInterface;
use crabovwik\y\interfaces\TaskInterface;

class Linker
{
    /** @var GroupedLinkItems[] */
    protected $groupedItems = array();

    /** @var GroupedLinkItems[] */
    protected $pairedItems = array();

    /** @var LinkItemInterface[] */
    protected $unpairedItems = array();

    protected $groupedItemsLinksHistory = array();

    protected $pairedItemsLinksHistory = array();

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

    public function newLogic()
    {
        $unpairedItemsCount = count($this->unpairedItems);

        if ($unpairedItemsCount < 3) {
            return;
        }

        $firstItemIndex     = 0;
        $secondItemIndex    = 1;
        $checkItemIndex     = 1;

        while ($checkItemIndex != $unpairedItemsCount - 1) {

            if (count($this->unpairedItems) < 3) {
                $this->unpairedItems = array();
                break;
            }

            $isIndexedWasForceChanged = false;

            if (!isset($this->unpairedItems[$firstItemIndex]) || !isset($this->unpairedItems[$secondItemIndex])) {
                $a = 'b';
            }

            $firstItem = $this->unpairedItems[$firstItemIndex];
            $secondItem = $this->unpairedItems[$secondItemIndex];

            unset($this->unpairedItems[$firstItemIndex]);
            unset($this->unpairedItems[$secondItemIndex]);

            $group = new GroupedLinkItems();
            $group->add($firstItem);
            $group->add($secondItem);

            $checkItemIndex = $secondItemIndex + 1;

            while ($checkItemIndex != $unpairedItemsCount - 1) {

                $checkItem = $this->unpairedItems[$checkItemIndex];
                if ($group->isInGroupWith($checkItem)) {
                    $group->add($checkItem);
                    unset($this->unpairedItems[$checkItemIndex]);

                    $isIndexedWasForceChanged = true;

                    if ($firstItemIndex == $checkItemIndex || $secondItemIndex == $checkItemIndex) {
                        $firstItemIndex++;
                        $secondItemIndex++;
                    }

                    if ($firstItemIndex + 2 == $checkItemIndex) {
                        $firstItemIndex += 3;
                        $secondItemIndex += 3;
                    }
                }

                $checkItemIndex++;
            }

            if (count($group->getLinkItems()) > 2) {
                $this->groupedItems[] = $group;
            }

            if (!$isIndexedWasForceChanged) {
                $firstItemIndex     += 2;
                $secondItemIndex    += 2;
                $checkItemIndex     = $secondItemIndex;
            }
        }
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
        static $num = 0;
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
        echo "\r" . (++$num);
//        $this->keyLinkerMap[$srcUser]->link();
    }

    public function getResult()
    {
        $resultData = array();
        foreach ($this->keyLinkerMap as $srcUser => $linker) {
            $linker->newLogic();
            $index = 0;

            $groupedLinks = $linker->getGroupedLinks();
            $groupedLinksCount = count($groupedLinks);

            if ($groupedLinksCount > 1) {
                echo "\n{$srcUser} => {$groupedLinksCount}\n";
            }

            foreach ($linker->getGroupedLinks() as $groupedLink) {
                foreach ($groupedLink->getLinkItems() as $linkItem) {
                    $resultData[$srcUser][$index][] = $linkItem->getAsYArray();
                }
                $index++;
            }
        }

        return json_encode($resultData);
    }
}