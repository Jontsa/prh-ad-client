<?php
/**
 * This file is part of the jontsa/prhadclient package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrhAdClient;

class BusinessInformation {

    private static $sources = [
        0 => 'Common',
        1 => 'Finnish Patent and Registration Office',
        2 => 'Tax Administration',
        3 => 'Business Information System'
    ];

    /**
     * @var array
     */
    private $data;

    /**
     * Constructor takes business data received from BusinessLookup::findByBusinessId().
     * 
     * @param array $data Business data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * Retrieves a variable from data received from PRH.
     * 
     * @param  string $k       Variable name
     * @param  mixed  $default Default value if variable is not found
     * @return mixed
     */
    public function get($k, $default = null) {
        return array_key_exists($k, $this->data) ? $this->data[$k] : $default;
    }

    /**
     * Returns all data gathered from BusinessLookup.
     * 
     * @return array
     */
    public function getAll() {
        return $this->data;
    }

    private function getPrimaryCompanyName() {
        return $this->data['name'];
    }

    public function getSourceAsText($source_id) {
        return isset($this->sources[$source_id]) ? $this->sources[$source_id] : $source_id;
    }

}
