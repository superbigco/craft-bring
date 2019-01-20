<?php
/**
 * Bring plugin for Craft CMS 3.x
 *
 * Integrate Bring/Posten with Craft Commerce
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\bring\variables;

use craft\commerce\elements\Order;
use superbig\bring\Bring;

use Craft;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class BringVariable
{
    // Public Methods
    // =========================================================================

    public function getBoxes()
    {
        return Bring::$plugin->box->getAllBoxes();
    }


    public function packOrder(Order $order)
    {
        return Bring::$plugin->shipping->packOrder($order);
    }

    public function getRates(Order $order)
    {
        return Bring::$plugin->shipping->getRates($order);
    }
}
