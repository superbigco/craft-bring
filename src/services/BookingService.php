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
use craft\db\Query;
use craft\helpers\DateTimeHelper;
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
use superbig\bring\models\ShipmentModel;
use superbig\bring\records\ShipmentRecord;
use yii\db\Exception;

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

    public function getShipmentByOrderId($orderId = null)
    {
        $query = $this
            ->_createQuery()
            ->where('orderId = :orderId', [':orderId' => $orderId])
            ->one();

        if (!$query) {
            return null;
        }

        return new ShipmentModel($query);
    }

    public function getShipmentInfoCode(ShipmentModel $model = null): string
    {
        $view = Craft::$app->getView();

        return $view->renderTemplate('bring/_order-details/order-info-table', [
            'shipment' => $model,
        ]);
    }

    public function bookShipment(Order $order)
    {
        $address        = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $payload        = [
            'testIndicator' => true,
            'schemaVersion' => 1,
            'consignments'  => [
                [
                    'shippingDateTime' => DateTimeHelper::toIso8601((new \DateTime())->modify('next tuesday')),
                    'parties'          => [
                        'sender'    => [
                            'name'        => $address->getFullName(),
                            'addressLine' => 'Brochmanns gate 4a',
                            'postalCode'  => '0470',
                            'city'        => 'Oslo',
                            'countryCode' => 'NO',
                            'contact'     => [
                                'name'        => $address->getFullName(),
                                'email'       => 'fred@superbig.co',
                                'phoneNumber' => '47651126',
                            ],
                        ],
                        'recipient' => [
                            'name'         => $address->getFullName(),
                            'addressLine'  => $address->address1,
                            'addressLine2' => $address->address2,
                            'postalCode'   => $address->zipCode,
                            'city'         => $address->city,
                            'countryCode'  => $address->country->iso,
                            'reference'    => $order->getCustomer()->id ?? null,
                            'contact'      => [
                                'name'        => $address->getFullName(),
                                'email'       => $order->getEmail(),
                                'phoneNumber' => $billingAddress->phone ?? $address->phone ?? '',
                            ],
                        ],
                    ],
                    'correlationId'    => $order->getShortNumber(),
                    'product'          => [
                        'id'             => $order->shippingMethodHandle,
                        'customerNumber' => 'PARCELS_NORWAY-10041106237',
                    ],
                    //'additionalAddressInfo'      => '',
                    //'customsDeclarationType' => '',
                    //'customsDeclarationText' => '',
                    //'purchaseOrderNumber'    => '',
                    //'goodsDescription'       => '',
                    //'whoPaysInvoice'            => '',

                    // Specify account number for cash on delivery. This parameter can be maximum 35 character long.
                    'accountNumber'    => '12345678903',
                    //'accountType'      => '',

                    'currencyCode' => 'NOK',
                    //'correlationId' => $order->getShortNumber(),
                    'reference'    => $order->getShortNumber(),
                    'packages'     => $this->getOrderWeight($order),
                ],
            ],
        ];

        //dd($payload);

        $response = Bring::$plugin->api->post('/booking/api/booking', $payload);

        if (!$response) {
            return null;
        }

        $model = $this->parseBookingResponse($response);

        if (!$model) {
            return null;
        }

        $model->orderId = $order->id;

        $this->saveRecord($model);

        return $model->toArray();
    }

    public function parseBookingResponse(array $response = []): ShipmentModel
    {
        $consignment = $response['consignments'][0] ?? null;
        $info        = $consignment['confirmation'] ?? null;

        if (!$info) {
            return null;
        }

        $errors = $consignment['errors'];
        $links  = $info['links'];
        $model  = new ShipmentModel([
            'consignmentNumber' => $info['consignmentNumber'],
            'labelUrl'          => $links['labels'],
            'request'           => [],
            'response'          => $response,
        ]);

        return $model;
    }

    public function getOrderWeight(Order $order)
    {
        $packedBoxes = Bring::$plugin->shipping->packOrder($order);
        $params      = $this->getPackageParameters($packedBoxes);

        return $params;
    }

    public function getPackageParameters(PackedBoxList $packedBoxes): array
    {
        $parameters = [];
        $packages   = [];
        $index      = 0;

        /** @var PackedBox $packedBox */
        foreach ($packedBoxes as $packedBox) {
            /**
             * @var BringBox $boxType
             */
            $boxType = $packedBox->getBox(); // your own box object, in this case TestBox

            $packages[] = [
                // Grams
                'weightInKg'    => $packedBox->getWeight() / 1000,

                // Dimensions in cm
                'dimensions'    => [
                    'widthInCm'  => $boxType->getOuterWidth() / 10,
                    'lengthInCm' => $boxType->getOuterLength() / 10,
                    'heightInCm' => $boxType->getOuterDepth() / 10,
                    //'volumeInDm3' => $boxType->getInnerVolume() / 10,
                ],
                'numberOfItems' => \count($packedBox->getItems()),
                // @todo add correlation id
            ];

            $index++;
        }

        return $packages;
    }

    public function saveRecord(ShipmentModel $model): bool
    {
        $isNew = true;
        if (!$isNew) {
            $orderRecord = ShipmentRecord::findOne($model->id);

            if (!$orderRecord) {
                throw new Exception('Invalid shipment ID: ' . $this->id);
            }
        }
        else {
            $orderRecord = new ShipmentRecord($model->getAttributes());
        }

        return $orderRecord->save();
    }

    private function _createQuery()
    {
        return (new Query())
            ->from(ShipmentRecord::TABLE_NAME)
            ->select(['id', 'dateCreated', 'dateUpdated', 'orderId',
                      'consignmentNumber',
                      'labelUrl',
                      'request',
                      'response',
            ]);
    }
}
