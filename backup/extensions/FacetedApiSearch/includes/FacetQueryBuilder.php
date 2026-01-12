<?php

namespace FacetedApiSearch;

class FacetQueryBuilder {

    private array $must = [];
    private array $filter = [];
    private array $aggregations = [];

    private int $page = 1;
    private int $pageSize = 20;

    private ?array $sort = null;

    /**
     * TEXT QUERY → query_string + wildcard
     */
    public function setTextQuery(string $text, array $fields, $exactMatch = false): self {

        if (!$text) {
            return $this; // boşsa ekleme
        }
        if ($exactMatch) {
            $this->must[] = [
                "query_string" => [
                    "query" => "{$text}",
                    "fields" => $fields,
                    "default_operator" => "OR"
                ]
            ];
            return $this;
        }
        if ($text !== '') {
            // If string contains spaces, use multi_match
            if (str_contains(trim((string)$text), ' ')) {
                $this->must[] = [
                    "multi_match" => [
                        "query" => $text,
                        "fields" => $fields,
                        "type" => "best_fields",
                        "operator" => "or"
                    ]
                ];
            } else { // Single word → wildcard
                $this->must[] = [
                    "query_string" => [
                        "query" => "*{$text}*",
                        "fields" => $fields,
                        "default_operator" => "OR"
                    ]
                ];
            }
        }
        return $this;
    }

    /**
     * TERM FILTERS → must içinde term
     */
    public function setTermFilters(array $filters): self {
        foreach ($filters as $prop => $value) {
            $this->must[] = [
                "term" => [
                    "wbfs_" . $prop => $value
                ]
            ];
        }
        return $this;
    }

    /**
     * DATE FILTERS (range → filter context)
     */
    public function setDateFilters(array $filters): self {
        foreach ($filters as $prop => $range) {
            $esField = "wbfs_" . $prop;
            $rangeQuery = [];
            foreach (['gte', 'lte'] as $key) {
                if (isset($range[$key])) {
                    $rangeQuery[$key] = $range[$key];
                }
            }
            if (!empty($rangeQuery)) {
                $this->filter[] = [
                    "range" => [
                        $esField => $rangeQuery
                    ]
                ];
            }
        }
        return $this;
    }

    /**
     * FACETS → tarih alanları için date_histogram, diğerleri için terms
     * $dateFields parametresi → URL'den gelen 'dates' parametresindeki alanlar
     */
    public function setFacetAggregations(array $fields, array $dateFields = []): self {
        foreach ($fields as $prop) {
            $esField = "wbfs_" . $prop;

            if (in_array($prop, $dateFields)) {
                // date_histogram
                $this->aggregations["facet_" . $prop] = [
                    "date_histogram" => [
                        "field" => $esField,
                        "calendar_interval" => "day"
                    ]
                ];
            } else {
                // terms
                $this->aggregations["facet_" . $prop] = [
                    "terms" => [
                        "field" => $esField,
                        "size" => 50
                    ]
                ];
            }
        }
        return $this;
    }


    /**
     * PAGINATION
     */
    public function setPagination(int $page, int $pageSize): self {
        $this->page = max(1, $page);
        $this->pageSize = max(1, $pageSize);
        return $this;
    }

    /**
     * SORTING
     */
    public function setSort(string $field, string $order = "asc"): self {
        $this->sort = [
            [
                $field => [
                    "order" => $order
                ]
            ]
        ];
        return $this;
    }

    /**
     * BUILD FINAL QUERY
     */
    public function build(): array {
        $bool = [];

        if (!empty($this->must)) {
            $bool["must"] = $this->must;
        }

        if (!empty($this->filter)) {
            $bool["filter"] = $this->filter;
        }
        $result = [
            "from" => ($this->page - 1) * $this->pageSize,
            "size" => $this->pageSize,
            "query" => [
                "bool" => $bool
            ],
        ];

        if (!empty($this->aggregations)) {
            $result["aggs"] = $this->aggregations;
        }

        if ($this->sort !== null) {
            $result["sort"] = $this->sort;
        }

        return $result;
    }
}