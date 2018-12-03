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

use Exception;
use superbig\bring\Bring;

use Craft;
use craft\base\Component;
use superbig\bring\models\Box as BoxModel;
use superbig\bring\records\BoxRecord;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class Box extends Component
{
    private $_boxes;

    // Public Methods
    // =========================================================================

    public function getAllBoxes()
    {
        if (!$this->_boxes) {
            $this->_boxes = collect(Bring::$plugin->getSettings()->boxes)
                ->map(function($row) {
                    return new BoxModel($row);
                });
        }

        return $this->_boxes;
    }

    public function getBoxById($id = null)
    {
        return $this->getAllBoxes()->firstWhere('id', $id);
    }

    /**
     * @param BoxModel $box
     *
     * @return bool
     * @throws Exception
     */
    public function save(BoxModel $box): bool
    {
        if ($box->id) {
            $record = BoxRecord::findOne($box->id);

            if (!$record) {
                throw new Exception(Craft::t('bring', "No package with id {id} was found!", [
                    'id' => $box->id,
                ]));
            }
        }
        else {
            $record = new BoxRecord();
        }

        $record->setAttributes([
            'name'        => $box->name,
            'handle'      => $box->handle,
            'outerWidth'  => $box->outerWidth,
            'outerLength' => $box->outerLength,
            'outerHeight' => $box->outerHeight,
            'outerDepth'  => $box->outerDepth,
            'innerWidth'  => $box->innerWidth,
            'innerLength' => $box->innerLength,
            'innerHeight' => $box->innerHeight,
            'innerDepth'  => $box->innerDepth,
            'emptyWeight' => $box->emptyWeight,
            'maxWeight'   => $box->maxWeight,
        ]);

        if ($record->validate() && $record->save()) {
            return true;
        }

        if ($record->hasErrors()) {
            $box->addErrors($record->getErrors());
        }

        return false;
    }
}
