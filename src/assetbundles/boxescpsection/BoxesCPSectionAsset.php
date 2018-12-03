<?php
/**
 * Bring plugin for Craft CMS 3.x
 *
 * Integrate Bring/Posten with Craft Commerce
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\bring\assetbundles\boxescpsection;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class BoxesCPSectionAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@superbig/bring/assetbundles/boxescpsection/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Boxes.js',
        ];

        $this->css = [
            'css/Boxes.css',
        ];

        parent::init();
    }
}
