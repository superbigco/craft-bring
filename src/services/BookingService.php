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

use craft\commerce\elements\Order;
use DVDoug\BoxPacker\ItemTooLargeException;
use DVDoug\BoxPacker\PackedBox;
use DVDoug\BoxPacker\PackedBoxList;
use DVDoug\BoxPacker\Packer;
use GuzzleHttp\Client;
use superbig\bring\boxes\BringBox;
use superbig\bring\boxes\BringItem;
use superbig\bring\Bring;

use Craft;
use craft\base\Component;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class BookingService extends Component
{
    public const ENDPOINT = '/booking/api/customers.json';

    // Public Methods
    // =========================================================================

    public function getCustomer()
    {
        // @todo cache request
        $response = Bring::$plugin->api->get(static::ENDPOINT, [
            //'country' => $country ?? 'NO',
        ]);

        return $response;
    }
}
