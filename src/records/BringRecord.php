<?php
/**
 * Bring plugin for Craft CMS 3.x
 *
 * Integrate Bring/Posten with Craft Commerce
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2018 Superbig
 */

namespace superbig\bring\records;

use superbig\bring\Bring;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class BringRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bring_bringrecord}}';
    }
}
