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
class ShippingRatesController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['rates'];

    // Public Methods
    // =========================================================================

    /**
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionRates(): \yii\web\Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();
        $order  = Commerce::getInstance()->cart->getCart();
        $result = Bring::$plugin->shipping->getRates($order);

        return $this->asJson([
            'success' => !empty($result),
            'rates'   => $result,
        ]);
    }
}
