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

use superbig\bring\Bring;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /** @var boolean */
    public $testMode = false;

    /** @var string */
    public $apiUsername;

    /** @var string */
    public $apiClientUrl;

    /** @var string */
    public $apiKey;

    /** @var array */
    public $boxes = [];

    public $cache = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apiUsername', 'apiClientUrl', 'apiKey'], 'required'],
        ];
    }
}
