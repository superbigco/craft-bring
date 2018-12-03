<?php
/**
 * Shipping Zones plugin for Craft CMS 3.x
 *
 * Shipping Zones
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\bring\shippingmethods;

use craft\commerce\base\Model;
use craft\commerce\base\ShippingMethodInterface;
use superbig\shippingzones\models\AddressZoneModel;
use craft\commerce\Plugin as Commerce;

use Craft;

/**
 * @author    Superbig
 * @package   ShippingZones
 * @since     1.0.0
 */
class ShippingMethod extends Model implements ShippingMethodInterface
{
    // Public Static Methods
    // =========================================================================

    /**
     * @var int ID
     */
    public $id;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @var string Handle
     */
    public $handle = '';

    /**
     * @var string Description from Bring
     */
    public $description;

    /**
     * @var string Payment currency
     */
    public $paymentCurrency = 'USD';

    /**
     * @var string Help text from Bring
     */
    public $help;

    /**
     * @var bool Enabled
     */
    public $enabled = true;

    /**
     * @var array The product data from Bring
     */
    public $data = [];

    // Public Static Methods
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return Craft::t('bring', 'Bring Shipping');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function getHandle(): string
    {
        return (string)$this->handle;
    }

    /**
     * @inheritdoc
     */
    public function getShippingRules(): array
    {
        $shippingMethod = new ShippingRule([
            'name'        => $this->getName(),
            'methodId'    => $this->getHandle(),
            'description' => $this->getDescription(),
            'baseRate'    => $this->getPrice(),
        ]);

        // TODO: Get rule
        return [
            $shippingMethod,
        ];
    }

    public function getPrice()
    {
        $priceWithAdditionalServices = $this->data['price']['listPrice']['priceWithAdditionalServices'];
        $priceWithVat                = $priceWithAdditionalServices['amountWithVAT'] ?? null;
        $currencyCode                = $this->data['price']['listPrice']['currencyCode'] ?? $this->paymentCurrency;
        $price                       = (float)$priceWithVat;

        if ($this->paymentCurrency !== $currencyCode) {
            // @todo convert currency
            $price = round($price / 8.5);
            //$price = Commerce::getInstance()->getPaymentCurrencies()->convert($price, $currencyCode);
        }


        return $price;
    }

    /**
     * @inheritdoc
     */
    public function getIsEnabled(): bool
    {
        return (bool)$this->enabled;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl(): string
    {
        return '';

        //return UrlHelper::cpUrl('commerce/settings/shippingmethods/' . $this->id);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'handle'], 'required'],
            //[['name'], UniqueValidator::class, 'targetClass' => ShippingMethodRecord::class],
            //[['handle'], UniqueValidator::class, 'targetClass' => ShippingMethodRecord::class],
        ];
    }

    public function getDescription()
    {
        return $this->description;
    }
}
