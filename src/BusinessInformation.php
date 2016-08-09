<?php
namespace PrhAdClient;

use GuzzleHttp\Client;

class BusinessInformation {

    private $url = 'http://avoindata.prh.fi/bis/v1';

    /**
     * Lookup business information from Finnish Patent and Registration Office using Finnish business id.
     * @param  string $businessid Finnish business id
     * @return false|null|array Business information as an array, false if not found or null if response was not valid
     * @todo   throw exception if response is not valid json
     * @todo   throw exception on connection errors
     */
    public function findByBusinessId($businessid) {
        // Strip all extra characters from business id
        $businessid = preg_replace('/[0-9a-z-]^/', '', $businessid);

        // Get data from PRH
        $url = $this->url . "/{$businessid}";
        $client = new Client();
        $response = $client->get($url);

        // 404
        if($response->getStatusCode() == '404') {
            return false;
        }

        $body = json_decode((string)$response->getBody(), true);

        return $body;
    }

}
