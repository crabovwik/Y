<?php

namespace crabovwik\y\data;

use crabovwik\y\interfaces\LinkItemInterface;
use crabovwik\y\interfaces\PairedLinkItemsInterface;

class PairedLinkFileRow implements PairedLinkItemsInterface
{
    /** @var LinkFileRow */
    protected $first;

    /** @var LinkFileRow */
    protected $second;

    /** @var int */
    protected $interval;

    public function __construct(LinkItemInterface $first, LinkItemInterface $second)
    {
        $this->first = $first;
        $this->second = $second;
    }

    public function isInGroup(AbstractYRow $row)
    {
        return
            (abs($row->getUnixTimestamp() - $this->second->getUnixTimestamp()) === $this->interval) ||
            (abs($row->getUnixTimestamp() - $this->first->getUnixTimestamp()) === $this->interval);
    }

    public function getInterval()
    {
        if ($this->interval === null) {
            $this->interval = abs($this->first->getUnixTimestamp() - $this->second->getUnixTimestamp());
        }

        return $this->interval;
    }
}
