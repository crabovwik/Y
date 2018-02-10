<?php

namespace crabovwik\y\data;

use crabovwik\y\interfaces\GroupedLinkItemsInterface;
use crabovwik\y\interfaces\LinkItemInterface;

class GroupedLinkItems implements GroupedLinkItemsInterface
{
    protected static $id = 0;

    protected $selfId;

    /** @var LinkFileRow[] */
    protected $linkItems = array();

    protected $interval;

    public function getId()
    {
        if ($this->selfId === null) {
            $this->selfId = static::$id++;
        }

        return $this->selfId;
    }

    public function add(LinkItemInterface $linkItem)
    {
        $this->linkItems[] = $linkItem;
    }

    public function getLinkItems()
    {
        return $this->linkItems;
    }

    public function getInterval()
    {
        if (count($this->linkItems) < 2) {
            return null;
        }

        if ($this->interval === null) {
            $this->interval = abs($this->linkItems[0]->getUnixTimestamp() - $this->linkItems[1]->getUnixTimestamp());
        }

        return $this->interval;
    }

    public function isInGroupWith(LinkItemInterface $item)
    {
        if (!$item instanceof AbstractYRow) {
            throw new \RuntimeException("Parameter 'item' not instance of " . AbstractYRow::class);
        }

        return
            (abs($item->getUnixTimestamp() - reset($this->linkItems)->getUnixTimestamp()) === $this->getInterval()) ||
            (abs($item->getUnixTimestamp() - end($this->linkItems)->getUnixTimestamp()) === $this->getInterval());
    }
}
