<?php
/**
 * Bring plugin for Craft CMS 3.x
 *
 * Integrate Bring/Posten with Craft Commerce
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\bring;

use craft\commerce\events\RegisterAvailableShippingMethodsEvent;
use craft\commerce\services\ShippingMethods;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\UrlHelper;
use superbig\bring\services\ApiService;
use superbig\bring\services\BookingService;
use superbig\bring\services\Box as BoxService;
use superbig\bring\services\Item as ItemService;
use superbig\bring\services\PostalCodeService;
use superbig\bring\services\Shipping as ShippingService;
use superbig\bring\variables\BringVariable;
use superbig\bring\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class Bring
 *
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 *
 * @property  ApiService        $api
 * @property  PostalCodeService $postalCode
 * @property  BoxService        $box
 * @property  ItemService       $item
 * @property  ShippingService   $shipping
 * @property  BookingService    $booking
 *
 * @method  Settings getSettings()
 */
class Bring extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Bring
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'api'        => ApiService::class,
            'box'        => BoxService::class,
            'item'       => ItemService::class,
            'postalCode' => PostalCodeService::class,
            'shipping'   => ShippingService::class,
            'booking'    => BookingService::class,
        ]);

        $this->installEventListeners();

        Craft::info(
            Craft::t(
                'bring',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function installEventListeners()
    {
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_LOAD_PLUGINS,
            function() {
                // Add in our event listeners that are needed for every request
                $this->installGlobalEventListeners();

                // Only respond to non-console site requests
                $request = Craft::$app->getRequest();

                if ($request->getIsSiteRequest() && !$request->getIsConsoleRequest()) {
                    $this->handleSiteRequest();
                }

                // AdminCP magic
                if ($request->getIsCpRequest() && !$request->getIsConsoleRequest()) {
                    $this->handleCpRequest();
                }
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('bring', BringVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );
    }


    public function installGlobalEventListeners()
    {
        Event::on(ShippingMethods::class, ShippingMethods::EVENT_REGISTER_AVAILABLE_SHIPPING_METHODS, function(RegisterAvailableShippingMethodsEvent $event) {
            static::$plugin->shipping->onRegisterAvailableShippingMethods($event);
        });

        /*Event::on(OrderAdjustments::class, OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS, function(RegisterComponentTypesEvent $event) {
            $event->types[] = FreeShipping::class;
        });*/
    }

    public function handleCpRequest()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                // Register our AdminCP routes
                $event->rules = array_merge(
                    $event->rules,
                    $this->customAdminCpRoutes()
                );
            }
        );
    }

    public function handleSiteRequest(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['bring/postal-code-info'] = 'bring/postal-code/info';
                $event->rules['bring/service-points']   = 'bring/default/service-points';
            }
        );

        //dd(Bring::$plugin->booking->getCustomer());
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): array
    {
        $nav           = parent::getCpNavItem();
        $nav['subnav'] = [
            'boxes'    => [
                'label' => 'Boxes',
                'url'   => UrlHelper::cpUrl('bring/boxes'),
            ],
            'settings' => [
                'label' => 'Settings',
                'url'   => UrlHelper::cpUrl('bring/settings'),
            ],
        ];

        return $nav;
    }

    protected function customAdminCpRoutes(): array
    {
        return [
            //'bring'       => ['template' => 'bring/index'],
            'bring/boxes'    => 'bring/boxes/index',
            'bring/settings' => 'bring/settings',
        ];
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'bring/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }
}
