<?php
/**
 * Bring plugin for Craft CMS 3.x
 *
 * Integrate Bring/Posten with Craft Commerce
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\bring\controllers;

use superbig\bring\Bring;

use Craft;
use craft\web\Controller;
use craft\commerce\Plugin as Commerce;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class BookingController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['info'];

    // Public Methods
    // =========================================================================

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionBookShipment(): \yii\web\Response
    {
        //$this->requireAcceptsJson();
        //$this->requirePostRequest();

        $orderId = Craft::$app->getRequest()->getRequiredParam('orderId');
        $order   = Commerce::getInstance()->getOrders()->getOrderById($orderId);
        $result  = Bring::$plugin->booking->bookShipment($order);

        return $this->asJson([
            'success' => !empty($result),
            'info'    => $result,
        ]);
    }
}
