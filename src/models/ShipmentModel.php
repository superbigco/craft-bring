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

use craft\helpers\Json;
use superbig\bring\Bring;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 *
 * @property int    $id
 * @property string $consignmentNumber
 * @property string $labelUrl
 * @property array  $request
 * @property array  $response
 */
class ShipmentModel extends Model
{
    // Public Properties
    // =========================================================================


    public $id;
    public $dateCreated;
    public $dateUpdated;
    public $orderId;
    public $consignmentNumber;
    public $labelUrl;
    public $request;
    public $response;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        if (\is_string($this->response)) {
            $this->response = Json::decodeIfJson($this->response);
        }
    }

    public function getShipmentTableCode()
    {
        return Bring::$plugin->booking->getShipmentInfoCode($this);
    }

    public function getPdfUrl(): string
    {
        return $this->labelUrl;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //['someAttribute', 'string'],
            //['someAttribute', 'default', 'value' => 'Some Default'],
        ];
    }
}
