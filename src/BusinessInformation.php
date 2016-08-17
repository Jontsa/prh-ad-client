<?php
/**
 * This file is part of the jontsa/prhadclient package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrhAdClient;

/**
 * Class containing data received from PRH API about single business.
 */
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

    /**
     * Returns company primary name.
     *
     * By default it will lookup primary company name. You can give a two letter language code to see if
     * there is a name for that language. If translation is not found, the primary name is returned.
     * 
     * @param  string $language Optional two letter language code
     * @return string
     */
    public function getPrimaryCompanyName($language = null) {
        if($language) {
            $names = array_merge($this->data['names'], $this->data['auxiliaryNames']);
            foreach($names as $name) {
                if(isset($name['language']) && $name['language'] == $language && $name['order'] >= 0 && $name['version'] == 1 && $this->hasExpired($name) == false) {
                    return $name['name'];
                }
            }
        }
        return $this->data['name'];
    }

    /**
     * Returns all active auxiliary names for business.
     * @return array
     */
    public function getAuxiliaryNames() {
        $retval = [];
        foreach($this->data['auxiliaryNames'] as $name) {
            if($name['order'] <> 0 && $name['version'] == 1 && $this->hasExpired($name) == false) {
                $retval[] = $name;
            }
        }

        return $retval;
    }

    /**
     * Check if business has entries indicating that it has been liquidated or filed for bankrupt.
     * 
     * @return false|string Either false if not or liquidation type code
     */
    public function isBusinessLiquidated() {
        foreach($this->data['liquidations'] as $v) {
            if($this->hasExpired($v) == false) {
                return $v['type'];
            }
        }

        return false;
    }

    /**
     * Check if business company form entries that have not ended.
     * 
     * @return boolean
     */
    public function hasActiveCompanyForms() {
        foreach($this->data['companyForms'] as $v) {
            if($this->hasExpired($v) == false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Translates source-integer in data to a human readable form.
     * 
     * @param  int $source_id Source id from data
     * @return mixed Either source as string or $source_id if unknown
     */
    public static function getSourceAsText($source_id) {
        return isset(self::$sources[$source_id]) ? self::$sources[$source_id] : $source_id;
    }

    /**
     * Takes endDate value from array to check if date has expired.
     * @param  array   $data
     * @return boolean
     */
    private function hasExpired(array $data) {
        if(empty($data['endDate'])) return false;

        $date1 = new \DateTime($data['endDate']);
        $date2 = new \DateTime();
        return ((int)$date2->format('Ymd') >= (int)$date1->format('Ymd'));
    }

}
