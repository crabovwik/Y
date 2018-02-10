<?php

namespace crabovwik\y\interfaces;

interface GroupedLinkItemsInterface
{
    public function getId();
    public function add(LinkItemInterface $linkItem);
    public function getLinkItems();
    public function isInGroupWith(LinkItemInterface $item);
}
