<?php

namespace superbig\bring\boxes;

use DVDoug\BoxPacker\Item;

/**
 * Class BringItem
 *
 * @package Bring\Boxes
 */
class BringItem implements Item
{

    private $description;
    private $width;
    private $length;
    private $depth;
    private $weight;
    private $keepFlat;
    private $volume;
    private $id;

    public function __construct ($id, $description, $width, $length, $depth, $weight, $keepFlat)
    {
        $this->id          = $id;
        $this->description = $description;
        $this->width       = $width;
        $this->length      = $length;
        $this->depth       = $depth;
        $this->weight      = $weight;
        $this->keepFlat    = $keepFlat;
        $this->volume      = $this->width * $this->length * $this->depth;
    }

    public function getId (): string
    {
        return $this->id;
    }

    public function getDescription (): string
    {
        return $this->description;
    }

    public function getWidth (): int
    {
        return $this->width;
    }

    public function getLength (): int
    {
        return $this->length;
    }

    public function getDepth (): int
    {
        return $this->depth;
    }

    public function getWeight (): int
    {
        return $this->weight;
    }

    public function getVolume (): int
    {
        return $this->volume;
    }

    public function getKeepFlat (): bool
    {
        return $this->keepFlat;
    }
}