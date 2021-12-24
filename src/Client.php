<?php
namespace Itrack\Anaf;

use Itrack\Anaf\Models\Company;
use stdClass;

/**
 * Implementare API ANAF V6
 * https://webservicesp.anaf.ro/PlatitorTvaRest/api/v6/
 * @package Itrack\Anaf
 */
class Client
{
    /** @var array CIFs List */
    protected $cifs = [];

    /** @var string API URL for v6 */
    public $apiURL = 'https://webservicesp.anaf.ro/PlatitorTvaRest/api/v6/ws/tva';

    /**
     * Client constructor.
     * @param string $apiURL
     */
    public function __construct($apiURL = null)
    {
        // let's check if apiURL is set and is valid url
        if(!is_null($apiURL) && filter_var($apiURL, FILTER_VALIDATE_URL))
        {
            $this->apiURL = $apiURL;
        }
    }

    /**
     * Add one or more cifs
     * @param string|array $cifs
     * @param string|null $date
     * @return $this
     */
    public function addCif($cifs, string $date = null): Client
    {
        // If not have set date return today
        if(is_null($date)) {
            $date = date('Y-m-d');
        }

        // Convert to array
        if(!is_array($cifs)) {
            $cifs = [$cifs];
        }

        foreach($cifs as $cif) {
            // Keep only numbers from CIF
            $cif = preg_replace('/\D/', '', $cif);

            // Add cif to list
            $this->cifs[] = [
                "cui" => $cif,
                "data" => $date
            ];
        }

        return $this;
    }

    /**
     * @return Company[]
     * @throws Exceptions\LimitExceeded
     * @throws Exceptions\RequestFailed
     * @throws Exceptions\ResponseFailed
     */
    public function get(): array
    {
        $companies = [];
        $results = Http::call($this->cifs, $this->apiURL);
        foreach ($results as $result) {
            $companies[] = new Company(new Parser($result));
        }
        return $companies;
    }

    /**
     * @return Company
     * @throws Exceptions\LimitExceeded
     * @throws Exceptions\RequestFailed
     * @throws Exceptions\ResponseFailed
     */
    public function first(): Company
    {
        $results = Http::call($this->cifs, $this->apiURL);
        return new Company(new Parser($results[0]));
    }
}
