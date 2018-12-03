<?php

namespace superbig\bring\boxes;

use DVDoug\BoxPacker\Box;

/**
 * Class BringBox
 *
 * @package Bring
 */
class BringBox implements Box
{

    private $innerDepth;
    private $innerVolume;
    private $maxWeight;
    private $innerLength;
    private $reference;
    private $outerLength;
    private $outerWidth;
    private $innerWidth;
    private $outerDepth;
    private $emptyWeight;

    /**
     * BringBox constructor.
     *
     * @param string $reference
     * @param        $outerWidth
     * @param        $outerLength
     * @param        $outerDepth
     * @param        $emptyWeight
     * @param        $innerWidth
     * @param        $innerLength
     * @param        $innerDepth
     * @param        $maxWeight
     */
    public function __construct (
        $reference,
        $outerWidth,
        $outerLength,
        $outerDepth,
        $emptyWeight,
        $innerWidth,
        $innerLength,
        $innerDepth,
        $maxWeight
    )
    {
        $this->reference   = $reference;
        $this->outerWidth  = $outerWidth;
        $this->outerLength = $outerLength;
        $this->outerDepth  = $outerDepth;
        $this->emptyWeight = $emptyWeight;
        $this->innerWidth  = $innerWidth;
        $this->innerLength = $innerLength;
        $this->innerDepth  = $innerDepth;
        $this->maxWeight   = $maxWeight;
        $this->innerVolume = $this->innerWidth * $this->innerLength * $this->innerDepth;
    }

    /**
     * @return string
     */
    public function getReference (): string
    {
        return $this->reference;
    }

    public function getOuterWidth (): int
    {
        return $this->outerWidth;
    }

    public function getOuterLength (): int
    {
        return $this->outerLength;
    }

    public function getOuterDepth (): int
    {
        return $this->outerDepth;
    }

    public function getEmptyWeight (): int
    {
        return $this->emptyWeight;
    }

    public function getInnerWidth (): int
    {
        return $this->innerWidth;
    }

    public function getInnerLength (): int
    {
        return $this->innerLength;
    }

    public function getInnerDepth (): int
    {
        return $this->innerDepth;
    }

    public function getInnerVolume (): int
    {
        return $this->innerVolume;
    }

    public function getMaxWeight (): int
    {
        return $this->maxWeight;
    }
}