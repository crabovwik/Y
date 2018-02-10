<?php

namespace crabovwik\y\interfaces;

interface PairedLinkItemsInterface
{
    public function __construct(LinkItemInterface $first, LinkItemInterface $second);
    public function isInGroupWith(LinkItemInterface $item);
}
