<?php
/**
 * This file is part of the jontsa/prhadclient package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrhAdClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class YTJ {

    /**
     * Base URL to PRH API.
     * @var string
     */
    private $url = 'http://avoindata.prh.fi/bis/v1';

    /**
     * Lookup business information from Finnish Patent and Registration Office using Finnish business id.
     *
     * Method accepts Finnish business id in following formats:
     * FI12345678
     * 12345678
     * 1234567-8
     * 
     * @param  string $businessid Finnish business id
     * @param  array  $options    Array of additional options to pass to GuzzleHttp\Client
     * @return false|BusinessInformation BusinessInformation object or false if not found
     * @throws InvalidArgumentException When business id is not valid
     * @throws RuntimeException If response from PRH API is not valid JSON
     */
    public function findByBusinessId($businessid, array $options = []) {

        // Format business id
        $match = preg_match('/^[FI]{0,2}([0-9]{7})-?([0-9]{1})/i', trim($businessid), $matches);

        if(count($matches) < 3) {
            throw new \InvalidArgumentException('Argument is not a valid Finnish business id.');
        }

        $businessid = $matches[1] . '-' . $matches[2];

        // Get data from PRH
        try {
            $url = $this->url . "/{$businessid}";
            $client = new Client();
            $response = $client->get($url, $options);
        } catch(ClientException $e) {
            // 404
            if($e->getResponse()->getStatusCode() == '404') {
                return false;
            }
            throw $e;
        }

        $body = json_decode((string)$response->getBody(), true);

        // Invalid body
        if($body == false) {
            throw new \RuntimeException('Invalid response.');
        }

        // Empty result set
        if(empty($body['results']) == true) {
            return false;
        }

        return new BusinessInformation($body['results'][0]);
    }

}
