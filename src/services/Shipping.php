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
use superbig\bring\shippingmethods\ShippingMethod;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class Shipping extends Component
{
    // Public Methods
    // =========================================================================

    const OPTIONS = [
        //'<a href='#home-delivery'>List of Codes</a>' => 'Home Delivery',
        'PAKKE_I_POSTKASSEN_A'         => 'Pakke i postkassen A',
        'PAKKE_I_POSTKASSEN_B'         => 'Pakke i postkassen B',
        'PAKKE_I_POSTKASSEN_A_SPORBAR' => 'Pakke i postkassen A sporbar',
        'PAKKE_I_POSTKASSEN_B_SPORBAR' => 'Pakke i postkassen B sporbar',
        'A-POST'                       => 'A-Prioritert',
        'B-POST'                       => 'B-Økonomi',
        'BPAKKE_DOR-DOR'               => 'Bedriftspakke',
        'BUSINESS_PARCEL'              => 'Business Parcel',
        'CARGO_GROUPAGE'               => 'Cargo Norway',
        'CARGO_INTERNATIONAL'          => 'Cargo International',
        'COURIER_1H'                   => 'Bud 1 time',
        'COURIER_2H'                   => 'Bud 2 timer',
        'COURIER_4H'                   => 'Bud 4 timer',
        'COURIER_6H'                   => 'Bud 6 timer',
        'COURIER_VIP'                  => 'Bud VIP',
        'EKSPRESS09'                   => 'Bedriftspakke Ekspress-Over natten 09',
        'EXPRESS_ECONOMY'              => 'Express Economy',
        'EXPRESS_INTERNATIONAL'        => 'Express International',
        'EXPRESS_INTERNATIONAL_0900'   => 'Express International 09:00',
        'EXPRESS_INTERNATIONAL_1200'   => 'Express International 12:00',
        'EXPRESS_NORDIC_0900'          => 'Express Nordic 0900',
        'EXPRESS_NORDIC_SAME_DAY'      => 'Express Nordic Same Day',
        'FRIGO'                        => 'Frigo',
        'OIL_EXPRESS'                  => 'Oil Express',
        'PA_DOREN'                     => 'På Døren',
        'PICKUP_PARCEL'                => 'PickUp Parcel',
        'SERVICEPAKKE'                 => 'Klimanøytral Servicepakke',
    ];

    const ENDPOINT          = 'https://api.bring.com';
    const TRACKING_ENDPOINT = 'http://sporing.bring.no/';

    protected $_packages;

    public function onRegisterAvailableShippingMethods(\craft\commerce\events\RegisterAvailableShippingMethodsEvent $event)
    {
        $order = $event->order;

        if ($shippingMethods = $this->getRates($order)) {
            $event->shippingMethods = $shippingMethods;
        }
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    public function getRates(Order $order)
    {
        $address = $order->getShippingAddress();

        if (empty($address->zipCode)) {
            // @todo default price?
            // @todo default product
            return null;
        }

        $options = [
            // From postal code.
            'fromcountry'         => 'NO',
            'frompostalcode'      => '0470',

            // To postal code
            'topostalcode'        => '0151',
            'tocountry'           => 'NO',

            // Grams
            //'weight'              => 1500,

            // Tells whether the parcel is delivered at a post office when it is shipped. A surcharge will be applied for SERVICEPAKKE and BPAKKE_DOR-DOR
            'postingatpostoffice' => 'false',

            'customernumber' => 'PARCELS_NORWAY-10041106237',

            'product'   => [
                'PA_DOREN',
                'BPAKKE_DOR-DOR',
                //'SERVICEPAKKE',
            ],

            // Weight of package in grams.
            //'weightInGrams'       => 1500,

            // Package width in centimeters.
            //'width'               => 40,

            // Package length in centimeters.
            //'length'              => 40,

            // Package height in centimeters.
            //'height'              => 40,

            // Package loading meters in meters.
            //'loadMeters'          => 1.22,

            // Number of pallets.
            //'numberOfPallets'     => 10,

            // Default false. Set to true if you know that pallet would be non-stackable.
            //'NonStackable'        => true,

            // Shipping date. Specifies which date the parcel will be delivered to Bring (within the time limit),
            // and is used to calculate the delivery date. Date is specified in ISO format, YYYY-MM-DD.
            //'date'                => '2015-11-18',

            //  Client URL. To use the Shipping Guide you must specify a client url parameter. The client url
            // should be set to the url of the web shop or application the end user is ordering from.
            // The client url can be sent as a url parameter, clientUrl, or as a header, X-Bring-Client-URL.
            'clientUrl' => 'https://www.superbig.co/',

            // EDI setting. Flag that tells if the parcel will be registred using EDI when it is shipped.
            // Note that this flag may affect price and which products are available.
            // 'edi' => true,

            // Optional. Flag that tells whether the parcel is delivered at a post office when it is shipped.
            // A surcharge will be applied for the following products:
            // - SERVICEPAKKE
            // - BPAKKE_DOR-DOR (Bedriftspakke)
            //'postingAtPostOffice' => false,

            // Optional. Additional services. Request additional services for the package. Specified by adding
            // one or more additional parameters to the request, e.g. &additional=postoppkrav&additional=evarsling.
            // See the additional services list. The Shipping Guide will match the additional service codes
            // to the requested products and ignore product / additional service codes that are not applicable.
            //'additional'    => '',

            // Optional. Price adjustments. Adjusts the price (w/o VAT) returned.
            // The format of it is [<product code>_][p(lus)|m(inus)]factor[p(ercentage)]
            //'priceAdjustments' => 'm20p'

            // Optional. Products. This parameter lets you specify which products you want to return information about.
            // If you omit this parameter, you get the default product list (not recommended for production usage).
            // Product is specified by adding one or more product parameters, e.g. product=servicepakke&product=bpakke_dor-dor.
            // 'product'       => 'bpakke_dor-dor',

            // Optional. Customer number. This parameter lets you specify which customer number to use to get additional
            // information about the specified product(s). This parameter requires user authentication.
            // If you are authenticated but omit this parameter, shipping guide will try to select an applicable customer
            // number for you. If there is only one applicable customer number, it will automatically be selected, but if t
            //here are multiple matches you have to specify which to use by using this parameter.
            // 'customerNumber'      => '',

            // Optional. Language. Which language the descriptive product texts should have. Supported languages
            // are English (en), Swedish (se), Finnish (fi), Danish (da) and Norwegian (no). If no language is
            // set, or text is not available in the requested language, norwegian text is returned.
            'language'  => 'no',

            // Optional. Special volume. Flag to indicate if the package has a shape that may require
            // ‘special handling fee’. product=servicepakke&product=bpakke_dor-dor.
            //'volumeSpecial' => false,
        ];

        $packedBoxes           = $this->packOrder($order);
        $packedOrderParameters = $this->getPackageParameters($packedBoxes);

        //BringPlugin::log('Acessing ' . self::ENDPOINT . '/shippingguide/products/all.json', LogLevel::Trace);
        $result = Bring::$plugin->api->get('/shippingguide/v2/products', array_merge($options, $packedOrderParameters));

        //dd($result);

        //Craft::info('Got back ' . json_encode($result), 'bring');

        if (empty($result)) {
            return null;
        }

        return $this->getPricesFromResponse($result, $order->paymentCurrency);
    }

    public function getPricesFromResponse(array $response, $paymentCurrency = 'USD')
    {
        $consigments = $response['consignments'] ?? null;
        $format      = collect($consigments)
            //->dd()
            ->map(function($consigment) use ($paymentCurrency) {
                $products = $consigment['products'] ?? null;

                return collect($products)
                    ->filter(function($product) {
                        $hasErrors = !empty($product['errors']);

                        if ($hasErrors) {
                            // Log error
                        }

                        return !$hasErrors;
                    })
                    ->map(function($product) use ($paymentCurrency) {
                        $gui = $product['guiInformation'];

                        // @todo get name from map
                        return new ShippingMethod([
                            'name'            => $gui['displayName'],
                            'handle'          => $product['id'],
                            'paymentCurrency' => $paymentCurrency,
                            //'productionCode'        => $product['productionCode'],
                            'description'     => $gui['descriptionText'],
                            'help'            => $gui['helpText'],
                            'data'            => $product,
                        ]);
                    });
            })
            ->collapse();

        return $format;
    }

    public function getPackages(Order $order)
    {
        return $this->packOrder($order);

        //return $this->getPackageParameters($order->getLineItems());
    }

    public function getPackageParameters(PackedBoxList $packedBoxes): array
    {
        $parameters = [];
        $index      = 0;

        /** @var PackedBox $packedBox */
        foreach ($packedBoxes as $packedBox) {
            /**
             * @var BringBox $boxType
             */
            $boxType = $packedBox->getBox(); // your own box object, in this case TestBox

            // Grams
            $parameters[ 'weight' . $index ] = $packedBox->getWeight();

            // Dimensions in cm
            $parameters[ 'width' . $index ]  = $boxType->getOuterWidth() / 10;
            $parameters[ 'length' . $index ] = $boxType->getOuterLength() / 10;
            $parameters[ 'height' . $index ] = $boxType->getOuterDepth() / 10;

            //"This box is a {$boxType->getReference()}, it is
            // {$boxType->getOuterWidth()}mm wide, {$boxType->getOuterLength()}mm long
            // and {$boxType->getOuterDepth()}mm high" . PHP_EOL);
            // ("The combined weight of this box and the items inside it is {$packedBox->getWeight()}g" . PHP_EOL);

            //echo("The items in this box are:" . PHP_EOL);
            $itemsInTheBox = $packedBox->getItems();

            foreach ($itemsInTheBox as $item) { // your own item object, in this case TestItem
                //echo($item->getItem()->getDescription() . PHP_EOL . PHP_EOL);
            }

            $index++;
        }

        return $parameters;
    }

    public function getShippingMethodsForOrder(Order $order)
    {
        $packed = $this->packOrder($order);

        // https://www.nettpilot.no/produkt/bring-fraktguiden-api-til-woocommerce/
        // https://api.bring.com/shippingguide/products/all.json?clientUrl=insertYourClientUrlHere&from=7041&to=0558&weightInGrams0=1500&volume1=33&length2=10&width2=20&height2=30
    }

    public function packOrder(Order $order)
    {
        // 40x20x24 cm
        /*
     * To figure out which boxes you need, and which items go into which box
     */
        $packer       = new Packer();
        $boxes        = Bring::$plugin->box->getAllBoxes();
        $items        = [];
        $toLargeItems = [];
        $finalized    = false;

        foreach ($boxes as $box) {
            $packer->addBox($box);
        }

        // Get items
        foreach ($order->getLineItems() as $lineItem) {
            /** @var \craft\commerce\models\LineItem $lineItem */

            // TODO: Add box
            $total = $lineItem->qty;

            foreach (range(1, $total) as $itemCount) {
                $key           = "{$lineItem->id}-{$itemCount}";
                $items[ $key ] = new BringItem(
                    $key,
                    $lineItem->getDescription(),
                    $lineItem->width,
                    $lineItem->length,
                    $lineItem->height,
                    (float)$lineItem->weight,
                    false);
            }

            // $packer->addItem(new TestItem('Item 3',
            // 250,
            // 250, 24,
            // 200, true)); //
            // you can even choose if an item needs to be kept flat (packed "this way up")
        }

        //dump($items);

        $packedBoxes = null;
        // $aReference, $aOuterWidth,$aOuterLength,$aOuterDepth,$aEmptyWeight,$aInnerWidth,$aInnerLength,$aInnerDepth,$aMaxWeight
        do {
            try {
                $packer->setItems($items);

                $packedBoxes = $packer->pack();
                $finalized   = true;
            } catch (ItemTooLargeException $e) {
                $largeItem           = $e->getItem();
                $id                  = $largeItem->getId();
                $toLargeItems[ $id ] = $largeItem;

                unset($items[ $id ]);
            }
        } while (!$finalized);

        //dump($items, $toLargeItems, $this->getPackageParameters($packedBoxes));
        //dump($packedBoxes);


        /*echo '<pre>';
        echo("These items fitted into " . count($packedBoxes) . " box(es)" . PHP_EOL);

        if ( !empty($toLargeItems) ) {
            echo 'There was ' . count($toLargeItems) . ' large items that did not fit into the predefined boxes' . PHP_EOL . PHP_EOL;
        }*/

        /*
         foreach ($packedBoxes as $packedBox) {
            $boxType = $packedBox->getBox(); // your own box object, in this case TestBox
            echo("This box is a {$boxType->getReference()}, it is {$boxType->getOuterWidth()}mm wide, {$boxType->getOuterLength()}mm long and {$boxType->getOuterDepth()}mm high" . PHP_EOL);
            echo("The combined weight of this box and the items inside it is {$packedBox->getWeight()}g" . PHP_EOL);

            echo("The items in this box are:" . PHP_EOL);
            $itemsInTheBox = $packedBox->getItems();

            foreach ($itemsInTheBox as $item) { // your own item object, in this case TestItem
                echo($item->getItem()->getDescription() . PHP_EOL . PHP_EOL);
            }

            echo(PHP_EOL);
        }
        */

        return $packedBoxes;
    }


    public function getPackageOptions()
    {
        return self::OPTIONS;
    }

    private function getBringProductList()
    {
        return self::OPTIONS;
    }

    private function _boxFromLineItem(Commerce_LineItemModel $lineItem)
    {
        $product = $lineItem->getPurchasable();

        if (!$product) {
            return false;
        }

        return new BringBox(
            'box-' . $lineItem->id,
            $lineItem->width,
            $lineItem->length,
            $lineItem->height,
            0,
            $lineItem->width,
            $lineItem->length,
            $lineItem->height,
            $lineItem->weight);
    }

    // Protected Methods
    // =========================================================================
    /**
     * Get order signature for caching algorithm
     *
     * @param string $handle
     * @param Order  $order
     *
     * @return string
     */
    protected function _getSignature($handle, Order $order): string
    {
        $totalQty               = $order->getTotalQty();
        $totalWeight            = $order->getTotalWeight();
        $totalWidth             = 20;
        $totalHeight            = 20;
        $totalLength            = 20;
        $shippingAddress        = $order->getShippingAddress();
        $shippingAddressDetails = '';

        if ($shippingAddress) {
            // use every single address detail instead of date updated because
            // the record gets updated every single time you select the address
            // in the frontend and creates a new signature even if the address
            // didn't change actually
            $shippingAddressDetails = $shippingAddress->attention;
            $shippingAddressDetails .= $shippingAddress->title;
            $shippingAddressDetails .= $shippingAddress->firstName;
            $shippingAddressDetails .= $shippingAddress->lastName;
            $shippingAddressDetails .= $shippingAddress->countryId;
            $shippingAddressDetails .= $shippingAddress->stateId;
            $shippingAddressDetails .= $shippingAddress->address1;
            $shippingAddressDetails .= $shippingAddress->address2;
            $shippingAddressDetails .= $shippingAddress->city;
            $shippingAddressDetails .= $shippingAddress->zipCode;
            $shippingAddressDetails .= $shippingAddress->phone;
            $shippingAddressDetails .= $shippingAddress->alternativePhone;
            $shippingAddressDetails .= $shippingAddress->businessName;
            $shippingAddressDetails .= $shippingAddress->businessTaxId;
            $shippingAddressDetails .= $shippingAddress->businessId;
            $shippingAddressDetails .= $shippingAddress->stateName;
        }

        return md5($handle . $totalQty . $totalWeight . $totalWidth . $totalHeight . $totalLength . $shippingAddressDetails);
    }

    /**
     * Box packing algorithm. Get the maximum width and length of all line items
     * and sum up the heights of all items
     *
     * @param Order $order
     *
     * @return array
     */
    private function _getPackageDimensions(Order $order)
    {
        $maxWidth  = 0;
        $maxLength = 0;
        foreach ($order->lineItems as $key => $lineItem) {
            $maxLength = $maxLength < $lineItem->length ? $maxLength = $lineItem->length : $maxLength;
            $maxWidth  = $maxWidth < $lineItem->width ? $maxWidth = $lineItem->width : $maxWidth;
        }

        return [
            'length' => (int)$maxWidth,
            'width'  => (int)$maxLength,
            'height' => (int)$order->getTotalHeight(),
        ];
    }

    private function getDefaultHeaders()
    {
        return [
            'accept'             => 'application/json',
            'x-mybring-api-uid'  => 'fred@superbig.co',
            'x-mybring-api-key'  => '9a732fd0-4b81-431c-ba2b-70b4fdbe6346',
            'x-bring-client-url' => 'https://superbig.co',
        ];
    }
}
