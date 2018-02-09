<?php

namespace crabovwik\y\data;

abstract class AbstractYRow extends AbstractRow
{
    protected $time;
    protected $srcUser;
    protected $srcIp;
    protected $srcPort;
    protected $dstUser;
    protected $dstIp;
    protected $dstPort;
    protected $inputByte;
    protected $outputByte;

    protected $unixTimestamp;

    public function getUnixTimestamp()
    {
        if ($this->unixTimestamp === null) {
            // 2018-01-19T19:46:00.000+0300
            $time = str_replace('T', '', $this->getTime());
            if (version_compare(phpversion(), '7.0.0', '>=')) {
                $parseFormat = 'Y-m-dH:i:s.vO';
            } else {
                $time = preg_replace('#\.\d\d\d#', '', $time);
                $parseFormat = 'Y-m-dH:i:sO';
            }

            $this->unixTimestamp = \DateTime::createFromFormat($parseFormat, $time,
                new \DateTimeZone('Europe/Moscow'))->getTimestamp();
        }

        return $this->unixTimestamp;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getSrcUser()
    {
        return $this->srcUser;
    }

    public function getSrcIp()
    {
        return $this->getSrcIp();
    }

    public function getSrcPort()
    {
        return $this->srcPort;
    }

    public function getDstUser()
    {
        return $this->dstUser;
    }

    public function getDstIp()
    {
        return $this->dstIp;
    }

    public function getDstPort()
    {
        return $this->dstPort;
    }

    public function getInputByte()
    {
        return $this->inputByte;
    }

    public function getOutputByte()
    {
        return $this->outputByte;
    }

    public function getAsYArray()
    {
        return array(
            '_time'         => $this->time,
            'src_user'      => $this->srcUser,
            'src_ip'        => $this->srcIp,
            'src_port'      => $this->srcPort,
            'dest_user'     => $this->dstUser,
            'dest_ip'       => $this->dstIp,
            'dest_port'     => $this->dstPort,
            'input_byte'    => $this->inputByte,
            'output_byte'   => $this->outputByte,
        );
    }
}
