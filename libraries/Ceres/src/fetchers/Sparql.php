<?php 

namespace Ceres\Fetcher;

use Ceres\Exception\DataException;
use Ceres\Exception\Fetcher as FetcherException;

class Sparql extends AbstractFetcher {

    protected string $queryForm = 'SELECT DISTINCT'; // 'SELECT', 'CONSTRUCT', 'ASK', 'DESCRIBE'
    
    protected array $prefixes = [
        'dct' => 'http://purl.org/dc/terms/',
        'dc' => 'http://purl.org/dc/elements/1.1/',
        'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
        'owl' => 'http://www.w3.org/2002/07/owl#',
        'schema' => 'http://purl.org/net/schemas/',
        'foaf' => 'http://xmlns.com/foaf/0.1/',
        // @todo: move to a DbPediaFetcher
        // 'dbp' => 'http://dbpedia.org/property/',
        // 'dbr' => 'http://dbpedia.org/resource/',
        // 'dbo' => 'http://dbpedia.org/ontology/',
        'xsd' => 'http://www.w3.org/2001/XMLSchema#',
        'geo' => 'http://www.w3.org/2003/01/geo/wgs84_pos#',
        'geosparql' => 'http://www.opengis.net/ont/geosparql#',
        'skos' => 'http://www.w3.org/2004/02/skos/core#',

   // lookup service http://prefix.cc/ still works, but no idea if it's being updated
    ];

    protected string $lang = 'en'; // @todo: make an option???
    /**
     * whereQueryClauses
     * 
     * a list of base query clauses, before filter, optional, service, etc
     *
     * @var array
     */

    protected array $whereQueryClauses = [];

    protected array $optionalClauses = [];

    protected array $filterClauses;

    protected array $serviceClauses;

    protected array $orderByClauses;

    protected array $groupByClauses;

    protected array $bindClauses;

    protected array $unionClauses;

    protected array $valuesClauses;

    protected array $havingClauses;

    protected ?int $limit = null;

    protected array $resultVars;

    protected string $query = "";


    public function __construct() {
// anything new to do here?
        parent::__construct();
    }

    // abstract overrides
    public function buildQueryString(?array $queryOptions = null, ?array $queryParams = null): void {
    }

    public function setPaginationData() {
    }

    public function getItemDataById($itemId) {
    }

    public function parseItemsData(): void {
    }
    
    public function fetchPage(int $pageNumber) {
    }

    public function getPageUrl(int $pageNumber) {
    }

    // end abstract overrides
    

    public function addPrefixes(array $prefixes) {
        foreach ($prefixes as $prefix => $uri) {
            $this->addPrefix($prefix, $uri);
        }
    }

    public function addPrefix(string $prefix, string $uri) {
        $this->prefixes[$prefix] = $uri;
    }


    /**
     * addWhereQueryClause
     * 
     * Not necessarily a single line, to allow for multiline ttl bits
     *
     * @param string $clause
     * @return void
     */
    public function addWhereQueryClause(string $clause) {
        $this->whereQueryClauses[] = $clause;
    }

    public function addOptionalClause(string $clause) {
        $this->optionalClauses[] = $clause;
    }

    public function addServiceClause(string $clause) {
        $this->serviceClauses[] = $clause;
    }

    public function addFilterClause(string $clause) {
        $this->filterClauses[] = $clause;
    }

    public function addOrderByClause(string $clause) {
        $this->orderByClauses[] = $clause;
    }

    public function addBindClause(string $clause) {
        $this->bindClauses[] = $clause;
    }

    public function addUnionClause(string $clause) {
        $this->unionClauses[] = $clause;
    }

    public function addValuesClause(string $clause) {
        $this->valuesClauses[] = $clause;
    }

    public function addHavingClause(string $clause) {
        $this->havingClauses[] = $clause;
    }
    public function addGroupByClause(string $clause) {
        $this->groupByClauses[] = $clause;
    }


    // @todo make an option
    public function setLang(string $lang) {
        $this->fetcherOptions['lang'] = $lang;
    }

    public function getQuery(): string {
        return $this->query;
    }

    public function setQueryForm(string $queryForm) {
        $allowedQueryForms = ['SELECT DISTINCT', 'SELECT', 'ASK', 'DESCRIBE'];
        $queryForm = strtoupper($queryForm);
        if(! in_array($queryForm, $allowedQueryForms)) {
            throw new DataException("Invalid SPARQL query form: $queryForm ");
        }

        $this->queryForm = $queryForm;
    }

    public function build() {
        if (! is_null($this->fetcherOptions['rqFile'])) {
            throw new FetcherException("rqFile is set, preventing building");
        }
        $query = "";
        $query .= $this->buildPrefixes();
        $query .= $this->buildResultVars();
        $query .= $this->buildWhere();
        return $query;
    }

    public function addResultVar(string $resultVar) {
        $this->resultVars[] = $resultVar;
    }

    public function detectResponseFormat() {
        
    }

    public function buildResultVars() {
        $resultVarString = "";
        foreach ($this->resultVars as $resultVar) {
            $resultVarString = "?$resultVar ";
        }
        return $resultVarString;
    }

    protected function buildWhere() {
        $whereQuery = "";
        $whereQuery .= $this->buildWhereClauses();
        $whereQuery .= $this->buildOptionalClauses();
        $whereQuery .= $this->buildFilterClauses();
        $whereQuery .= $this->buildOrderByClauses();
        $whereQuery .= $this->buildServiceClauses();
        return $whereQuery;
    }

    protected function buildPrefixes() {
        $prefixString = "";
        foreach ($this->prefixes as $prefix=>$uri) {
            $prefixString .= "PREFIX $prefix <$uri>;";
        }
        return $prefixString;
    }

    protected function buildQueryBindings() {
        $bindingsString = "{$this->queryForm}";
        foreach($this->bindClauses as $bindClause) {
            $bindingsString .= "$bindClause ";
        }
    }

    protected function buildWhereClauses() {
        $whereClauses = "";
        foreach ($this->whereQueryClauses as $clause) {
            $whereClauses .= "$clause \r\n";
        }
        return $whereClauses;
    }

    protected function buildOptionalClauses() {
        $optionalClauses = "";
        foreach ($this->optionalClauses as $clause) {
            $optionalClauses .= "OPTIONAL $clause \r\n";
        }
        return $optionalClauses;
    }

    protected function buildFilterClauses() {
        $filterClauses = "";
        foreach ($this->filterClauses as $clause) {
            $filterClauses .= "\r\nFILTER $clause \r\n";
        }
        return $filterClauses;
    }


    protected function buildOrderByClauses() {
        $orderByClauses = "";
        foreach ($this->orderByClauses as $clause) {
            $orderByClauses .= "\r\nORDER BY $clause \r\n";
        }
        return $orderByClauses;
    }

    protected function buildServiceClauses() {
        $serviceClauses = "";
        foreach ($this->serviceClauses as $clause) {
            $serviceClauses .= "SERVICE $clause \r\n";
        }
    }

    
}
