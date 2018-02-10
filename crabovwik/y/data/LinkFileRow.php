<?php

namespace crabovwik\y\data;

use crabovwik\y\interfaces\LinkItemInterface;

class LinkFileRow extends FileRow implements LinkItemInterface
{
    protected static $id = 0;

    protected $selfId;

    public function __construct($line)
    {
        parent::__construct($line);
    }

    public function getId()
    {
        if ($this->selfId === null) {
            $this->selfId = static::$id++;
        }

        return $this->selfId;
    }
}
