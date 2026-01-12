<?php
/**
 * Maintenance script: Rebuild suggest fields for all documents, skipping empty fields
 *
 * Usage:
 * php maintenance/run.php extensions/FacetedApiSearch/maintenance/RebuildSuggestAllDocs.php --index=wikibase_content_first
 */

$IP = getenv('MW_INSTALL_PATH') ?: __DIR__ . '/../../..';
require_once "$IP/maintenance/Maintenance.php";

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;

class RebuildSuggestIndex extends Maintenance {
    public function __construct() {
        parent::__construct();
        $this->addOption('index', 'Elasticsearch index name', true);
    }

    public function execute(): void {
        global $wgFacetedSuggestProperties;

        if (empty($wgFacetedSuggestProperties) || !is_array($wgFacetedSuggestProperties)) {
            $this->fatalError(
                "ERROR: \$wgFacetedSuggestProperties not defined in LocalSettings.php\n" .
                "Example:\n\$wgFacetedSuggestProperties = ['P1','P2','P3'];\n"
            );
        }

        $index = $this->getOption('index');
        $esBaseUrl = rtrim($this->getConfig()->get('ElasticsearchBaseUrl'), '/');

        $this->output("Rebuilding suggestions for ALL documents\n");
        $this->output("Elasticsearch: $esBaseUrl\n");
        $this->output("Suggest Properties: " . implode(', ', $wgFacetedSuggestProperties) . "\n\n");

        // Scroll üzerinden tüm dokümanları al
        $scrollId = null;
        $batchSize = 200;
        $updatedDocs = 0;

        do {
            $query = [
                'size' => $batchSize,
                '_source' => array_map(fn($p) => "wbfs_$p", $wgFacetedSuggestProperties),
                'query' => $scrollId ? ['scroll_id' => $scrollId] : ['match_all' => (object)[]]
            ];

            $cmd = "curl -s -X GET '$esBaseUrl/$index/_search?scroll=1m' " .
                "-H 'Content-Type: application/json' -d '" . json_encode($query, JSON_UNESCAPED_SLASHES) . "'";
            $res = shell_exec($cmd);
            $json = json_decode($res, true);

            if (empty($json['hits']['hits'])) break;

            $bulk = "";
            foreach ($json['hits']['hits'] as $hit) {
                $docId = $hit['_id'];
                $source = $hit['_source'];
                $suggestData = [];

                foreach ($wgFacetedSuggestProperties as $prop) {
                    $field = "wbfs_$prop";
                    if (!isset($source[$field]) || empty($source[$field])) continue;

                    $value = (array)$source[$field];
                    $value = array_filter(array_map('strval', $value), fn($v) => trim($v) !== "");
                    if (!$value) continue;

                    $suggestData["{$field}_suggest"] = ["input" => array_values($value)];
                }

                if (!$suggestData) continue;

                // Bulk update
                $bulk .= json_encode(["update" => ["_index" => $index, "_id" => $docId]]) . "\n";
                $bulk .= json_encode(["doc" => $suggestData]) . "\n";

                $updatedDocs++;
            }

            // Bulk gönder
            if (!empty($bulk)) {
                $this->sendBulk($esBaseUrl, $bulk);
                $bulk = "";
            }

            $scrollId = $json['_scroll_id'] ?? null;

        } while (!empty($json['hits']['hits']));

        $this->output("\nRebuild completed. Updated documents: $updatedDocs\n");
    }

    private function sendBulk(string $esBaseUrl, string $payload): void {
        $tmp = tempnam(sys_get_temp_dir(), 'esbulk');
        file_put_contents($tmp, $payload);

        shell_exec(
            "curl -s -X POST '$esBaseUrl/_bulk' -H 'Content-Type: application/x-ndjson' --data-binary '@$tmp'"
        );

        unlink($tmp);
    }
}

$maintClass = RebuildSuggestIndex::class;
require_once RUN_MAINTENANCE_IF_MAIN;
