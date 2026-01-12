<?php

namespace FacetedApiSearch;

use ApiBase;

class ApiFacetedSearch extends ApiBase {

    public function execute() {
        global $wgFacetedSecretToken;
        $params = $this->extractRequestParams();
        //
        // Do CURL request in the PROXY APP to run maintenance scripts:
        // https://wikibase.everydayjazz.com/w/api.php?action=facetedsearch&runJobs=1&secretToken=SECRET_TOKEN&itemId=Q3
        //
        if ( isset( $params['runJobs'] ) && isset( $params['secretToken'] )) {
            if ( $params['secretToken'] !== $wgFacetedSecretToken ) {
                $this->getResult()->addValue( null, $this->getModuleName(), [
                    'success' => 0,
                    'status' => 'ERROR: Invalid secret token.'
                ] );
                return;
            }
            $this->runJobs(); // İş kuyruğunu çalıştır.
        }
        if (!empty($params['suggest'])) {
            $this->executeSuggest($params);
        } else {
            $this->executeFacetSearch($params);
        }
    }
    
    private function executeFacetSearch($params) {
        $index = trim($this->getConfig()->get('ElasticsearchIndexName'));
        $esUrlBase = rtrim($this->getConfig()->get('ElasticsearchBaseUrl'), '/');
        $esUrl = $esUrlBase . "/" . $index . "/_search";

        $searchText = $params['query'] ?? '';
        $type = $params['type'] ?? '';

        $params['terms'] = json_decode($this->getRequest()->getVal('terms'), true) ?: [];
        $params['dates'] = json_decode($this->getRequest()->getVal('dates'), true) ?: [];
        $params['facets'] = json_decode($this->getRequest()->getVal('facets'), true) ?: [];
        // $params['textFields'] = ['all'];
        $params['textFields'] = json_decode($this->getRequest()->getVal('textFields'), true) ?: ['wbfs_P1','wbfs_P2'];

        // EXACT match flag
        if (isset($params['exactMatch']) && $params['exactMatch']) {
            $exactMatch = true;
        } else {
            $exactMatch = false;
        }
        // TERM filters
        $termFilters = [];
        foreach ($params['terms'] as $k => $v) {
            $termFilters[$k] = $v;
        }

        // DATE filters
        $dateFilters = [];
        foreach ($params['dates'] as $prop => $range) {
            $dateFilters[$prop] = $range;
        }

        // FACET alanları
        $facetFields = $params['facets'] ?? [];

        // Pagination
        $page = intval($params['page'] ?? 1);
        $limit = intval($params['limit'] ?? 20);

        // Sorting
        $sortField = $params['sortField'] ?? null;
        $sortOrder = $params['sortOrder'] ?? 'asc';

        // Query builder (mevcut FacetQueryBuilder kullanılıyor)
        $builder = new FacetQueryBuilder();
        $builder->setTextQuery($searchText, $params['textFields'], $exactMatch);
        $builder->setTermFilters($termFilters);
        $builder->setDateFilters($dateFilters);
        $builder->setFacetAggregations($facetFields, array_keys($dateFilters));
        $builder->setPagination($page, $limit);

        if ($sortField) {
            $builder->setSort($sortField, $sortOrder);
        }
        $esQuery = $builder->build();

        // print_r($esQuery);
        // die;
        $data = $this->callElastic($esUrl, $esQuery);

        $hits = [];
        foreach ($data['hits']['hits'] ?? [] as $hit) {
            $hits[] = $hit['_source'];
        }

        $this->getResult()->addValue(null, $this->getModuleName(), [
            'success' => 1,
            'hits' => $hits,
            'total' => $data['hits']['total']['value'] ?? 0,
            'aggregations' => $data['aggregations'] ?? [],
        ]);
    }

    private function executeSuggest($params) {
        $index = trim($this->getConfig()->get('ElasticsearchIndexName'));
        $esUrlBase = rtrim($this->getConfig()->get('ElasticsearchBaseUrl'), '/');
        $esUrl = $esUrlBase . "/" . $index . "/_search";

        $searchText = $params['query'] ?? '';
        if (!$searchText) {
            $this->getResult()->addValue(null, $this->getModuleName(), ['success' => 0, 'suggestions' => []]);
            return;
        }
        // TERM filters
        $terms = json_decode($this->getRequest()->getVal('terms'), true) ?: [];
        $termFilters = [];
        foreach ($terms as $prop => $value) {
            $termFilters[] = [
                'term' => [
                    'wbfs_' . $prop => $value  // keyword varsa wbfs_'.$prop.'.keyword kullanabilirsin
                ]
            ];
        }

        // multi_match + bool_prefix query
        /*
        $esQuery = [
            'query' => [
                'multi_match' => [
                    'query' => $searchText,
                    'fields' => ['suggest^3', 'all'], // mapping'deki copy_to alanları
                    'type' => 'bool_prefix',
                    'operator' => 'or'
                ]
            ],
            '_source' => ['title', 'text', 'wbfs_P1', 'wbfs_P2'] // response'dan çekmek istediğin alanlar
        ];
        */

        // ES query
        $esQuery = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'multi_match' => [
                                'query' => $searchText,
                                'fields' => ['suggest^3', 'all'],
                                'type' => 'bool_prefix',
                                'operator' => 'or'
                            ]
                        ]
                    ],
                    'filter' => $termFilters // burada term filterleri ekledik
                ]
            ],
            '_source' => ['title', 'text', 'wbfs_P1', 'wbfs_P2']
        ];
        // print_r($esQuery);
        // die;

        $data = $this->callElastic($esUrl, $esQuery);
        /*
        echo "<pre>";
        echo print_r($data, true);
        echo "</pre>";
        die;
        */
        $suggestions = [];
        if (isset($data['hits']['hits'])) {
            foreach ($data['hits']['hits'] as $hit) {
                // Örnek olarak text alanını alıyoruz
                if (isset($hit['_source']['text'])) {
                    $suggestions[] = ['id' => $hit['_source']['title'], 'title' => $hit['_source']['text'] ?? ''];
                }
            }
        }

        $this->getResult()->addValue(null, $this->getModuleName(), [
            'success' => 1,
            'suggestions' => $suggestions
        ]);
    }

    private function callElastic($url, $query) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function runJobs() {
        $ROOT = trim($this->getConfig()->get('WikibaseRootPath'));
        $maintenancePath = $ROOT . '/maintenance';
        $logFile = '/tmp/apache_maintenance.log'; // Log dosyası yolu
    
        // Çıktıyı ($logFile) dosyasına EKLE (>>) ve hata çıktısını (2>&1) aynı yere yönlendir.
        // ARKA PLANA AT (&)
        $cmd = "php maintenance/run.php runJobs.php >> $logFile 2>&1 &";

        // --- TAM KOMUT ZİNCİRİ ---
        // 1. $logFile'a bir zaman damgası ve bilgi notu ekle
        $startLog = "echo '\n---\nMaintenance Scripts Started: " . date('Y-m-d H:i:s') . "' >> $logFile";
        $fullCmd = "$startLog && cd $ROOT && $cmd";
        shell_exec( $fullCmd );
    }

    public function getAllowedParams() {
        return [
            'type' => [
                'type' => 'string',
                'default' => 'Q2'
            ],
            'query' => [
                'type' => 'string',
                'description' => 'Search query string / suggest prefix'
            ],
            'suggest' => [
                'type' => 'boolean',
                'default' => false,
                'description' => 'If true, run autocomplete suggestion'
            ],  
            'runJobs' => [
                'type' => 'boolean',
                'default' => false,
                'description' => 'If true, run job queue'
            ],
            'exactMatch' => [
                'type' => 'boolean',
                'default' => false,
                'description' => 'If true, perform exact match search'
            ],
            'updateSuggestions' => [
                'type' => 'boolean',
                'default' => false,
                'description' => 'If true, run update index and job queue'
            ],
            'itemId' => [
                'type' => 'string',
                'description' => 'Item ID of document to update'
            ],
            'secretToken' => [
                'type' => 'string',
                'description' => 'Secret token for api write operations'
            ],
            'textFields' => [
                'type' => 'string'
            ],
            'terms' => [
                'type' => 'string'
            ],
            'dates' => [
                'type' => 'string'
            ],
            'facets' => [
                'type' => 'string'
            ],
            'page' => [
                'type' => 'integer',
                'default' => 1
            ],
            'limit' => [
                'type' => 'integer',
                'default' => 20
            ],
            'sortField' => [
                'type' => 'string'
            ],
            'sortOrder' => [
                'type' => 'string',
                'default' => 'asc'
            ],
        ];
    }
}