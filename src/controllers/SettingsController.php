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
class SettingsController extends Controller
{

    // Protected Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetCustomerNumbers(): \yii\web\Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();
        $this->requireAdmin();

        $order  = Commerce::getInstance()->cart->getCart();
        $result = Bring::$plugin->shipping->getRates($order);

        return $this->asJson([
            'success' => !empty($result),
            'rates'   => $result,
        ]);
    }
}
