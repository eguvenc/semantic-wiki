<?php
/**
 * Maintenance script: Bulk update suggest fields from job queue using Wikibase API
 *
 * Usage:
 * php maintenance/run.php extensions/FacetedApiSearch/maintenance/UpdateSuggestIndex.php --index=wikibase_content_first
 */

$IP = getenv('MW_INSTALL_PATH') ?: __DIR__ . '/../../..';
require_once "$IP/maintenance/Maintenance.php";

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;

class UpdateSuggestIndex extends Maintenance {

    public function __construct() {
        parent::__construct();
        $this->addOption('index', 'Elasticsearch index name', true);
    }

    public function execute(): void {
        global $wgServer, $wgFacetedSuggestProperties;

        if (empty($wgFacetedSuggestProperties) || !is_array($wgFacetedSuggestProperties)) {
            $this->fatalError(
                "ERROR: \$wgFacetedSuggestProperties not defined in LocalSettings.php\n" .
                "Example:\n\$wgFacetedSuggestProperties = ['P1','P2','P3'];\n"
            );
        }

        $index = $this->getOption('index');

        // Elasticsearch Base URL
        $esBaseUrl = rtrim($this->getConfig()->get('ElasticsearchBaseUrl'), '/');
        $server = rtrim($wgServer, '/'); // web server URL

        $this->output("Elasticsearch: $esBaseUrl\n");
        $this->output("Wikibase Server: $server\n");
        $this->output("Suggest Properties: " . implode(', ', $wgFacetedSuggestProperties) . "\n\n");

        // Collect changed items from job queue
        $group = MediaWikiServices::getInstance()->getJobQueueGroup();
        $itemIds = [];

        foreach ($group->getQueueTypes() as $type) {
            $queue = $group->get($type);
            foreach ($queue->getAllQueuedJobs() as $job) {
                $params = $job->getParams();
                if (!empty($params['title']) && str_starts_with($params['title'], 'Q')) {
                    $itemIds[] = $params['title'];
                }
            }
        }

        $itemIds = array_unique($itemIds);

        if (empty($itemIds)) {
            $this->output("No changed items in job queue.\n");
            return;
        }

        $this->output("Found " . count($itemIds) . " changed items.\n\n");

        $bulk = "";
        $batchSize = 0;
        $updatedDocs = 0;

        foreach ($itemIds as $qid) {

            /**
             * 1) WIKIBASE'TEN GÜNCEL ITEM VERİSİ ÇEKİLİYOR
             */
            $url = "$server/wiki/Special:EntityData/$qid.json";

            $jsonData = file_get_contents($url);
            if (!$jsonData) {
                $this->output("ERROR: Failed to fetch data for $qid\n");
                continue;
            }

            $item = json_decode($jsonData, true);
            if (empty($item['entities'][$qid])) {
                $this->output("Skip: $qid invalid entity\n");
                continue;
            }

            $entity = $item['entities'][$qid];

            /**
             * 2) İşlenecek alan: claims
             */
            $claims = $entity['claims'] ?? [];

            $suggestData = [];

            foreach ($wgFacetedSuggestProperties as $prop) {
                if (empty($claims[$prop])) continue;

                $values = [];

                foreach ($claims[$prop] as $claim) {
                    $mainsnak = $claim['mainsnak'] ?? null;

                    if (!$mainsnak || $mainsnak['snaktype'] !== 'value') continue;

                    $datavalue = $mainsnak['datavalue']['value'] ?? null;

                    if (is_string($datavalue)) {
                        $values[] = $datavalue;
                    } elseif (is_array($datavalue)) {
                        // Value might be entity-id, time, string, monolingualtext, etc.
                        if (!empty($datavalue['text'])) {
                            $values[] = $datavalue['text'];
                        } elseif (!empty($datavalue['id'])) {
                            $values[] = $datavalue['id'];
                        }
                    }
                }

                $values = array_filter(array_map('strval', $values), fn($v) => trim($v) !== "");

                if ($values) {
                    $suggestData["wbfs_{$prop}_suggest"] = ["input" => array_values($values)];
                }
            }

            if (!$suggestData) continue;

            /**
             * 3) DOCUMENT ID'yi bulmak için ES'e minimum bir sorgu yolluyoruz
             * (Sadece ID için, source’a ihtiyacımız yok)
             */
            $esQuery = [
                "_source" => false,
                "query" => [ "term" => [ "title.keyword" => $qid ] ]
            ];

            $cmd = "curl -s -X GET '$esBaseUrl/$index/_search' -H 'Content-Type: application/json' -d '" .
                json_encode($esQuery, JSON_UNESCAPED_SLASHES) . "'";

            $res = shell_exec($cmd);
            $json = json_decode($res, true);

            if (empty($json['hits']['hits'][0])) {
                $this->output("Skip: $qid not found in ES index\n");
                continue;
            }

            $docId = $json['hits']['hits'][0]['_id'];

            /**
             * 4) Bulk DATA oluştur
             */
            $bulk .= json_encode(["update" => ["_index" => $index, "_id" => $docId]]) . "\n";
            $bulk .= json_encode(["doc" => $suggestData]) . "\n";

            $batchSize++;
            $updatedDocs++;

            if ($batchSize >= 200) {
                $this->sendBulk($esBaseUrl, $bulk);
                $bulk = "";
                $batchSize = 0;
            }
        }

        if ($batchSize > 0) {
            $this->sendBulk($esBaseUrl, $bulk);
        }

        $this->output("\nBulk update completed. Updated: $updatedDocs documents.\n");
    }

    private function sendBulk(string $esBaseUrl, string $payload): void {
        $tmp = tempnam(sys_get_temp_dir(), 'esbulk');
        file_put_contents($tmp, $payload);

        shell_exec(
            "curl -s -X POST '$esBaseUrl/_bulk' " .
            "-H 'Content-Type: application/x-ndjson' " .
            "--data-binary '@$tmp'"
        );

        unlink($tmp);
    }
}

$maintClass = UpdateSuggestIndex::class;
require_once RUN_MAINTENANCE_IF_MAIN;
