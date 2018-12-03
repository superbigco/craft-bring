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
use craft\helpers\Json;
use DVDoug\BoxPacker\ItemTooLargeException;
use DVDoug\BoxPacker\PackedBox;
use DVDoug\BoxPacker\PackedBoxList;
use DVDoug\BoxPacker\Packer;
use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\build_query;
use Psr\Http\Message\ResponseInterface;
use superbig\bring\boxes\BringBox;
use superbig\bring\boxes\BringItem;
use superbig\bring\Bring;

use Craft;
use craft\base\Component;

/**
 * @author    Superbig
 * @package   Bring
 * @since     1.0.0
 */
class ApiService extends Component
{
    // Public Methods
    // =========================================================================

    const ENDPOINT          = 'https://api.bring.com';
    const TRACKING_ENDPOINT = 'http://sporing.bring.no/';

    private $_client;

    /**
     * @param string $url
     * @param array  $query
     *
     * @return array|null
     */
    public function get($url = '', $query = [])
    {
        try {
            $client   = $this->getClient();
            $response = $client->get($url, [
                //'headers' => $this->getDefaultHeaders(),
                'query' => build_query($query),
            ]);
            $body     = (string)$response->getBody();
            $json     = Json::decodeIfJson($body);

            //if (!empty($query)) {
            // $request->getQuery()->set()
            // }

            // Cache the response
            //craft()->fileCache->set($url, $json);
            // Apply the limit and offset
            //$items = array_slice($items, $offset, $limit);


            return $json;
        } catch (\Exception $e) {
            dd([
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        if (!$this->_client) {
            $this->_client = new Client([
                'base_uri' => static::ENDPOINT,
                'headers'  => $this->getDefaultHeaders(),
            ]);
        }

        return $this->_client;
    }

    // Protected Methods
    // =========================================================================

    private function getDefaultHeaders(): array
    {
        $settings = Bring::$plugin->getSettings();

        return [
            'accept'             => 'application/json',
            'x-mybring-api-uid'  => $settings->apiUsername,
            'x-mybring-api-key'  => $settings->apiKey,
            'x-bring-client-url' => $settings->apiClientUrl,
        ];
    }
}
