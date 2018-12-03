<?php
/**
 * Bring plugin for Craft CMS 3.x
 *
 * Integrate Bring/Posten with Craft Commerce
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\bring\assetbundles\Bring;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class BringAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@superbig/bring/assetbundles/bring/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Bring.js',
        ];

        $this->css = [
            'css/Bring.css',
        ];

        parent::init();
    }
}
