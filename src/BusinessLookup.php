<?php
/**
 * This file is part of the jontsa/prhadclient package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrhAdClient;

use GuzzleHttp\Client;

class BusinessLookup {

    /**
     * Base URL to PRH API.
     * @var string
     */
    private $url = 'http://avoindata.prh.fi/bis/v1';

    /**
     * Lookup business information from Finnish Patent and Registration Office using Finnish business id.
     * @param  string $businessid Finnish business id
     * @return false|null|BusinessInformation BusinessInformation object, false if not found or null if response was not valid
     * @todo   throw exception if response is not valid json
     * @todo   throw exception on connection errors
     */
    public function findByBusinessId($businessid) {
        // Strip all extra characters from business id
        $businessid = preg_replace('/[0-9a-z-]^/', '', $businessid);

        if(empty($businessid)) return false;

        // Get data from PRH
        $url = $this->url . "/{$businessid}";
        $client = new Client(['http_errors' => false]);
        $response = $client->get($url);

        // 404
        if($response->getStatusCode() == '404') {
            return false;
        }

        $body = json_decode((string)$response->getBody(), true);

        // Invalid body
        if($body == false) {
            return null;
        }

        // Empty result set
        if(empty($body['results']) == true) {
            return false;
        }

        return new BusinessInformation($body['results'][0]);

        return $body;
    }

}
