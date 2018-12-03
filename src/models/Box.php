<?php
/**
 * Bring plugin for Craft CMS 3.x
 *
 * Integrate Bring/Posten with Craft Commerce
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\bring\models;

use superbig\bring\Bring;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 *
 * @property int       $id
 * @property \DateTime $dateCreated
 * @property \DateTime $dateUpdated
 * @property int       $uid
 * @property int       $siteId
 * @property string    $name
 * @property string    $handle
 * @property int       $outerWidth
 * @property int       $outerLength
 * @property int       $outerHeight
 * @property int       $outerDepth
 * @property int       $innerWidth
 * @property int       $innerLength
 * @property int       $innerHeight
 * @property int       $innerDepth
 * @property int       $emptyWeight
 * @property int       $maxWeight
 * @property boolean   $enabled
 */
class Box extends Model implements \DVDoug\BoxPacker\Box
{
    // Public Properties
    // =========================================================================


    public $id;
    public $dateCreated;
    public $dateUpdated;
    public $uid;
    public $siteId;
    public $name;
    public $handle;
    public $outerWidth;
    public $outerLength;
    public $outerHeight;
    public $outerDepth;
    public $innerWidth;
    public $innerLength;
    public $innerHeight;
    public $innerDepth;
    public $emptyWeight;
    public $maxWeight;
    public $enabled;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'string'],
            ['name', 'default', 'value' => 'Some Default'],
        ];
    }

    /**
     * Reference for box type (e.g. SKU or description).
     *
     * @return string
     */
    public function getReference(): string
    {
        return $this->handle;
    }

    /**
     * Outer width in mm.
     *
     * @return int
     */
    public function getOuterWidth(): int
    {
        return $this->outerWidth;
    }

    /**
     * Outer length in mm.
     *
     * @return int
     */
    public function getOuterLength(): int
    {
        return $this->outerLength;
    }

    /**
     * Outer depth in mm.
     *
     * @return int
     */
    public function getOuterDepth(): int
    {
        return $this->outerDepth;
    }

    /**
     * Empty weight in g.
     *
     * @return int
     */
    public function getEmptyWeight(): int
    {
        return $this->emptyWeight;
    }

    /**
     * Inner width in mm.
     *
     * @return int
     */
    public function getInnerWidth(): int
    {
        return $this->innerWidth;
    }

    /**
     * Inner length in mm.
     *
     * @return int
     */
    public function getInnerLength(): int
    {
        return $this->innerLength;
    }

    /**
     * Inner depth in mm.
     *
     * @return int
     */
    public function getInnerDepth(): int
    {
        return $this->innerDepth;
    }

    /**
     * Max weight the packaging can hold in g.
     *
     * @return int
     */
    public function getMaxWeight(): int
    {
        return $this->maxWeight;
    }
}
