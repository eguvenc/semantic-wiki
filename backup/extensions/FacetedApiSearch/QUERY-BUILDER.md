Harika — sıfırdan bir **parser + ES JSON builder** yazıyorum. Aşağıdaki PHP sınıfı *bağımsız*, test edilebilir ve MediaWiki `ApiFacetedSearch` veya başka bir yerden kolayca çağrılabilecek. Yapacağı:

* `haswbfacet:` sözdizimini parse eder (pozitif/negatif, `=`, `>`, `<`, çoklu `|` değerler),
* normal free-text parçalarını ayırır,
* tarih alanları için güvenlik kontrolü yapar,
* `multi_match` (text + ngram) için esnek full-text sorgusu üretir,
* aggregations (keyword + date_histogram) ekler,
* page/size/sort gibi temel opsiyonları destekleyecek şekilde çıktı verir.

Kopyala-yapıştır ile extension içine koyup kullanabilirsin. Sonunda kullanım örnekleri ve nasıl entegre edeceğine dair kısa yönerge de verdim.

---

## 1) `FacetQueryBuilder.php` — sınıf kodu

```php
<?php
namespace FacetedApiSearch;

/**
 * FacetQueryBuilder
 *
 * Parse a faceted query string (haswbfacet:...) and build an Elasticsearch query array.
 *
 * Usage:
 *   $builder = new FacetQueryBuilder();
 *   $esQuery = $builder->buildFromQueryString($queryString, [
 *       'type' => 'Q2',
 *       'size' => 20,
 *       'page' => 1
 *   ]);
 */
class FacetQueryBuilder {

    // fields configuration (change if your index uses different fields)
    protected $fulltextFields = [
        'wbfs_P1', 'wbfs_P1.ngram',
        'wbfs_P2', 'wbfs_P2.ngram'
    ];

    // facets config -> aggregation field names (use .keyword for text fields)
    protected $facetFields = [
        'P1' => 'wbfs_P1.keyword',
        'P2' => 'wbfs_P2.keyword',
        'P3' => 'wbfs_P3', // date
        'P4' => 'wbfs_P4', // date
        'P5' => 'wbfs_P5.keyword'
    ];

    // which properties are date fields
    protected $dateProperties = ['P3', 'P4'];

    // default size
    protected $defaultSize = 20;

    public function __construct(array $opts = []) {
        if (isset($opts['fulltextFields'])) {
            $this->fulltextFields = $opts['fulltextFields'];
        }
        if (isset($opts['facetFields'])) {
            $this->facetFields = $opts['facetFields'];
        }
        if (isset($opts['dateProperties'])) {
            $this->dateProperties = $opts['dateProperties'];
        }
    }

    /**
     * Main entry: builds ES query array from a faceted query string and options.
     *
     * @param string $queryString  e.g. "Test1 haswbfacet:P5=Q2 haswbfacet:P3>2025-11-01"
     * @param array $options       keys: type (Q2), size, page, index (optional)
     * @return array               Elasticsearch query DSL as PHP array
     */
    public function buildFromQueryString(string $queryString, array $options = []): array {
        $type = $options['type'] ?? null;
        $size = (int)($options['size'] ?? $this->defaultSize);
        $page = max(1, (int)($options['page'] ?? 1));
        $from = ($page - 1) * $size;

        // Parse tokens
        $parsed = $this->parseQueryString($queryString);

        // Build must / must_not arrays
        $must = [];
        $must_not = [];

        // Type filter from options (type param) - map to wbfs_P5.keyword
        if ($type) {
            $must[] = ['term' => ['wbfs_P5.keyword' => $type]];
        }

        // Term filters (equality) from parsed facets
        foreach ($parsed['terms'] as $prop => $vals) {
            // property like "P5" -> map to facetFields
            $field = $this->getFieldForProperty($prop, true); // prefer keyword where configured
            if ($field === null) {
                continue;
            }
            if (count($vals) === 1) {
                $must[] = ['term' => [$field => $vals[0]]];
            } else {
                $must[] = ['terms' => [$field => $vals]];
            }
        }

        // Range filters
        foreach ($parsed['ranges'] as $prop => $ranges) {
            $esField = $this->getFieldForProperty($prop, false);
            if ($esField === null) continue;
            foreach ($ranges as $r) {
                $rangeBody = [];
                if (isset($r['gt'])) $rangeBody['gt'] = $r['gt'];
                if (isset($r['gte'])) $rangeBody['gte'] = $r['gte'];
                if (isset($r['lt'])) $rangeBody['lt'] = $r['lt'];
                if (isset($r['lte'])) $rangeBody['lte'] = $r['lte'];
                if (!empty($rangeBody)) {
                    $must[] = ['range' => [$esField => $rangeBody]];
                }
            }
        }

        // Existence / negative facets
        foreach ($parsed['exists'] as $prop) {
            $field = $this->getFieldForProperty($prop, false);
            if ($field !== null) {
                $must[] = ['exists' => ['field' => $field]];
            }
        }
        foreach ($parsed['not_exists'] as $prop) {
            $field = $this->getFieldForProperty($prop, false);
            if ($field !== null) {
                $must_not[] = ['exists' => ['field' => $field]];
            }
        }

        // Full text: build a should clause combining match and ngram variants
        $shouldFulltext = [];
        if (!empty($parsed['fulltext'])) {
            $fulltext = trim($parsed['fulltext']);
            // prefer multi_match when multiple fields; use match for single-field fallback
            $shouldFulltext[] = [
                'multi_match' => [
                    'query' => $fulltext,
                    'fields' => $this->fulltextFields,
                    'type' => 'best_fields',
                    'operator' => 'and',
                    'fuzziness' => 'AUTO'
                ]
            ];
            // also add individual match clauses to increase chance of matches
            foreach ($this->fulltextFields as $f) {
                $shouldFulltext[] = ['match' => [$f => $fulltext]];
            }
        }

        // If there's any fulltext should clauses, embed them in a must -> bool -> should
        if (!empty($shouldFulltext)) {
            $must[] = ['bool' => ['should' => $shouldFulltext]];
        }

        // Build aggregations (facets)
        $aggs = $this->buildAggregations();

        // Compose final query
        $esQuery = [
            'from' => $from,
            'size' => $size,
            'query' => [
                'bool' => [
                    'must' => array_values($must),
                ]
            ],
            'aggs' => $aggs
        ];

        if (!empty($must_not)) {
            $esQuery['query']['bool']['must_not'] = array_values($must_not);
        }

        return $esQuery;
    }

    /**
     * Parse the incoming query string and extract:
     * - fulltext (string)
     * - terms: [ 'P5' => ['Q2'], ... ]
     * - ranges: [ 'P3' => [ ['gte'=>'2020-01-01'] , ... ] ]
     * - exists: ['P1', ...]
     * - not_exists: ['P1', ...]
     *
     * @param string $s
     * @return array
     */
    protected function parseQueryString(string $s): array {
        $parts = preg_split('/\s+/', trim($s));
        $fulltextParts = [];
        $terms = [];
        $ranges = [];
        $exists = [];
        $not_exists = [];

        foreach ($parts as $part) {
            if ($part === '') continue;

            // Negative existence: -haswbfacet:P1
            if (preg_match('/^-haswbfacet:(P\d+)$/i', $part, $m)) {
                $not_exists[] = strtoupper($m[1]);
                continue;
            }

            // haswbfacet with optional operator: haswbfacet:P1, haswbfacet:P1=Q2, haswbfacet:P3>2020
            if (preg_match('/^haswbfacet:(P\d+)([<>=])?(.+)?$/i', $part, $m)) {
                $prop = strtoupper($m[1]);
                $op = $m[2] ?? null;
                $valRaw = $m[3] ?? null;

                if ($op === null) {
                    // existence check
                    $exists[] = $prop;
                    continue;
                }

                // equality
                if ($op === '=') {
                    // support multi-values separated by |
                    $vals = explode('|', $valRaw);
                    // normalize values (trim)
                    $vals = array_map('trim', $vals);
                    if (!isset($terms[$prop])) $terms[$prop] = [];
                    foreach ($vals as $v) if ($v !== '') $terms[$prop][] = $v;
                    continue;
                }

                // range operators > or <
                if ($op === '>' || $op === '<') {
                    // interpret > as gt, >= not directly supported in syntax but could be added
                    $k = $op === '>' ? 'gt' : 'lt';
                    // validate date-like for dateProperties
                    if (in_array($prop, $this->dateProperties)) {
                        if ($this->isValidDate($valRaw)) {
                            $ranges[$prop][] = [$k => $valRaw];
                        } else {
                            // invalid date - ignore
                        }
                    } else {
                        // non-date range - treat as numeric range?
                        $ranges[$prop][] = [$k => $valRaw];
                    }
                    continue;
                }
            }

            // not a facet token -> part of fulltext
            $fulltextParts[] = $part;
        }

        return [
            'fulltext' => implode(' ', $fulltextParts),
            'terms' => $terms,
            'ranges' => $ranges,
            'exists' => $exists,
            'not_exists' => $not_exists
        ];
    }

    /**
     * Build aggregations for configured facets.
     *
     * @return array
     */
    protected function buildAggregations(): array {
        $aggs = [];
        foreach ($this->facetFields as $prop => $field) {
            if (in_array($prop, $this->dateProperties)) {
                // use date_histogram for date facets
                $aggs['facet_' . $prop] = [
                    'date_histogram' => [
                        'field' => $field,
                        'calendar_interval' => 'day'
                    ]
                ];
            } else {
                $aggs['facet_' . $prop] = [
                    'terms' => [
                        'field' => $field,
                        'size' => 50
                    ]
                ];
            }
        }
        return $aggs;
    }

    /**
     * Map P<id> property code to ES field
     *
     * @param string $prop   e.g. 'P5'
     * @param bool $preferKeyword  if true try to return keyword-subfield if available
     * @return string|null
     */
    protected function getFieldForProperty(string $prop, bool $preferKeyword = true) {
        if (!isset($this->facetFields[$prop])) return null;
        $field = $this->facetFields[$prop];

        // if configured field is keyword or date, return
        if ($preferKeyword) {
            // if it's a keyword already, return
            if (substr($field, -8) === '.keyword' || strpos($field, 'keyword') !== false) {
                return $field;
            }
            // if we have plain text field, and a .keyword exists (convention), return it
            if (strpos($field, '.') === false) {
                return $field . '.keyword';
            }
        }

        return $field;
    }

    /**
     * Rudimentary date validation. Accepts YYYY-MM-DD or ISO formats.
     */
    protected function isValidDate(string $v): bool {
        if (trim($v) === '') return false;
        // try strtotime
        $t = strtotime($v);
        return $t !== false;
    }
}
```

---

## 2) Nasıl kullanırsın — kısa örnekler

### A) Basit kullanım (CLI / test)

```php
require_once 'FacetQueryBuilder.php';

$builder = new \FacetedApiSearch\FacetQueryBuilder();

$esQuery = $builder->buildFromQueryString(
    'Test1 haswbfacet:P5=Q2 haswbfacet:P3>2025-11-01',
    ['type' => 'Q2', 'size' => 10, 'page' => 1]
);

// çıktıyı json olarak görmek
echo json_encode($esQuery, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
```

### B) Curl ile doğrudan ES test

Konsolda:

```bash
curl -X GET "http://wbs-deploy-elasticsearch-1:9200/wikibase_content_first_ngram/_search" \
-H 'Content-Type: application/json' \
-d 'PASTE_JSON_HERE'
```

(yerine `$esQuery` çıktısını yapıştır)

### C) MediaWiki `ApiFacetedSearch` içinde kullanma (snippet)

`execute()` içinde:

```php
$builder = new \FacetedApiSearch\FacetQueryBuilder();
$esQuery = $builder->buildFromQueryString($params['query'] ?? '', [
    'type' => $params['type'] ?? null,
    'size' => $params['limit'] ?? 20,
    'page' => $params['page'] ?? 1
]);

// send $esQuery to ES (curl or client)
```

---

## 3) Test durumları (neyi denemelisin)

1. `Test1 haswbfacet:P5=Q2` → fulltext Test1, term P5=Q2
2. `haswbfacet:P3>2025-11-01` → P3 range
3. `-haswbfacet:P2` → P2 yok olan kayıtları getir (must_not exists)
4. `foo haswbfacet:P1=ValA|ValB` → P1 in [ValA,ValB]
5. Tarih doğrulama: `haswbfacet:P3>notadate` → bu token atlanır (güvenlik)

---

## 4) İleri geliştirme önerileri (opsiyonel)

* `boost` (P1’e ağırlık) eklenebilir (`multi_match` içinde `fields` ile `wbfs_P1^3` gibi).
* `sort` parametresi desteklenebilir.
* `filter` (cacheable) kullanmak için term/range’leri `bool.filter` altına taşıyabilirsin (performans artar).
* Elastic PHP client ile `curl` yerine doğrudan client kullanılması tavsiye edilir.

---

Eğer istersen hemen şimdi:

* ben bu sınıfı senin mevcut `extensions/FacetedApiSearch` içine uyarlayıp `ApiFacetedSearch::execute` içine örnek entegrasyon kodu yazayım (tam dosya),
  veya
* senin kurduğun ES endpoint’e uygun şekilde (index adı, date format vb.) küçük uyarlamalar yapayım.

Hangi adımı istersin — doğrudan **MediaWiki entegrasyonu** (tam `ApiFacetedSearch.php` güncellemesi) mı, yoksa önce **lokal test + örnek curl** ile doğrulama mı?
