<?php
/**
 * Bring plugin for Craft CMS 3.x
 *
 * Integrate Bring/Posten with Craft Commerce
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\bring\services;

use craft\commerce\models\LineItem;
use superbig\bring\boxes\BringItem;
use superbig\bring\Bring;

use Craft;
use craft\base\Component;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class Item extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (Bring::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }

    public function getItemsFromLineItem(LineItem $lineItem)
    {
        $items = [];
        $total = $lineItem->qty;

        foreach (range(1, $total) as $itemCount) {
            $key           = "{$lineItem->id}-{$itemCount}";
            $items[ $key ] = new BringItem(
                $key,
                $lineItem->getDescription(),
                $lineItem->width,
                $lineItem->length,
                $lineItem->height,
                $lineItem->weight,
                false);
        }

        return $items;
    }

    public function convertUnits()
    {
        // @todo check if units is not mm and g
    }
}
