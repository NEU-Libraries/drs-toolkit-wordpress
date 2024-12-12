<?php 

namespace Ceres\Fetcher;

use Ceres\Util\DataUtilities as DataUtil;

class Wdqs extends Sparql {

    //protected ?string $endpoint = 'http://ec2-34-227-69-60.compute-1.amazonaws.com:8834/proxy/wdqs/bigdata/namespace/wdq/sparql';
    protected string $endpoint = 'https://query.wikidata.org/sparql';


    public function __construct() {
        parent::__construct();
    }

    public function getValueForFetcherOption(string $optionName): string {
        if (array_key_exists( $optionName, $this->fetcherOptions)) {
            $optionValue = $this->fetcherOptions[$optionName];
            if (is_null($optionValue)) {
                $optionValue = '';
            }
        }
        return $optionValue;
    }

    // @TODO reconcile with AbstractFetcher
    public function fetchData(string $url = null, bool $returnWithoutSetting = false) {
        //@todo fold this into the DataUtil optionValues array
        $fetchLocalData = $this->getValueForFetcherOption('fetchLocalData');

        if ($fetchLocalData) {
            $jsonFilePath = $this->getValueForFetcherOption('localResponseDataPath');
            $responseData = $this->fetchDataFromJsonFile($jsonFilePath);
            if($returnWithoutSetting) {
                return $responseData;
            }
            
            $this->responseData = $responseData;
            //@todo remove the repetition from the bottom of this function
        }
        
        
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Accept: application/sparql-results+json',
                    'User-Agent: CERES/develop p.murray-john@northeastern.edu'
                ],
                "ignore_errors" => true, // catches non 2xx -- not sure I want that?
            ],
        ];
        $context = stream_context_create($opts);
        $url = $this->endpoint . '?query=' . urlencode($this->query);

        $response = file_get_contents($url, false, $context);
        //status digup thanks to https://stackoverflow.com/questions/15620124/http-requests-with-file-get-contents-getting-the-response-code
        $status_line = $http_response_header[0];

        preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
    
        $status = $match[1];
        if ($status !== "200") {
            throw new \RuntimeException("unexpected response status: {$status_line}\n " . $response);
        }

        if (! $returnWithoutSetting) {
            $this->setResponseData($response);
        }
        return $response;

    }

    public function setResponseData($response) {
        // if (is_string($response)) {
        //     $response = json_encode($response);
        // }
        $this->responseData = $response;
    }
}


/*


The full list of built in prefixes is WDQS
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wds: <http://www.wikidata.org/entity/statement/>
PREFIX wdv: <http://www.wikidata.org/value/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX wikibase: <http://wikiba.se/ontology#>
PREFIX p: <http://www.wikidata.org/prop/>
PREFIX ps: <http://www.wikidata.org/prop/statement/>
PREFIX pq: <http://www.wikidata.org/prop/qualifier/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX bd: <http://www.bigdata.com/rdf#>

PREFIX wdref: <http://www.wikidata.org/reference/>
PREFIX psv: <http://www.wikidata.org/prop/statement/value/>
PREFIX psn: <http://www.wikidata.org/prop/statement/value-normalized/>
PREFIX pqv: <http://www.wikidata.org/prop/qualifier/value/>
PREFIX pqn: <http://www.wikidata.org/prop/qualifier/value-normalized/>
PREFIX pr: <http://www.wikidata.org/prop/reference/>
PREFIX prv: <http://www.wikidata.org/prop/reference/value/>
PREFIX prn: <http://www.wikidata.org/prop/reference/value-normalized/>
PREFIX wdno: <http://www.wikidata.org/prop/novalue/>
PREFIX wdata: <http://www.wikidata.org/wiki/Special:EntityData/>

PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX prov: <http://www.w3.org/ns/prov#>
PREFIX bds: <http://www.bigdata.com/rdf/search#>
PREFIX gas: <http://www.bigdata.com/rdf/gas#>
PREFIX hint: <http://www.bigdata.com/queryHints#>
  */   


