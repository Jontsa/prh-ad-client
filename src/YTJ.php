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
     * Validates if given business id format fits Finnish business id.
     * Method does not validate checksum.
     * 
     * @param  string  $businessid
     * @todo   Update to support also older 6 number ids
     * @return boolean
     */
    public function isValidBusinessId($businessid) {
        return (bool) preg_match('/^[FI]{0,2}([0-9]{7})-?([0-9]{1})/i', trim($businessid));
    }

    /**
     * Check that business id is formated correctly and checksum matches.
     * 
     * @param  string $businessid
     * @return boolean
     */
    public function isValidFinnishBusinessId($businessid) {

        if($this->isValidBusinessId($businessid) == false) {
            return false;
        }

        // Remove all except numbers
        $businessid = preg_replace('/[^0-9]/', '', $businessid);

        // Some old business ids may have just 6 numbers + checksum
        $businessid = str_pad($businessid, 8, "0", STR_PAD_LEFT);

        // Calculate checksum
        $multipliers = [7,9,10,5,8,4,2];
        $checksum = 0;
        foreach(str_split($businessid) as $k => $v) {
            if(isset($multipliers[$k]) == false) break;

            $checksum += ((int)$v * $multipliers[$k]);
        }
        $checksum = $checksum % 11;

        if($checksum == 1) return false;
        if($checksum > 1) {
            $checksum = 11 - $checksum;
        }

        return (substr($businessid, -1) == $checksum);
    }

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
        if($this->isValidFinnishBusinessId($businessid) == false) {
            throw new \InvalidArgumentException('Argument is not a valid Finnish business id.');
        }

        // Format business id
        $match = preg_match('/^[FI]{0,2}([0-9]{7})-?([0-9]{1})/i', trim($businessid), $matches);

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
