
### Wikibase + CirrusSearch Custom Index Configuration

This setup allows searches not only on page titles but also on **Wikibase entities (Items, Properties, Descriptions, Statements)**.
Since your setup uses Elasticsearch 7.10.x and MediaWiki 1.44, the following configuration is **fully compatible**.

## Custom Index Types

CirrusSearch creates **three main indexes** inside MediaWiki:

| Index Name        | Content Type                      | Description                                     |
| ----------------- | --------------------------------- | ----------------------------------------------- |
| `content`         | Wiki page content                 | Standard text search                            |
| `general`         | MediaWiki metadata                | Page titles, summaries                          |
| `wikibase_entity` | Wikibase entities (item/property) | Labels, descriptions, aliases, statement values |

> The third index (`wikibase_entity`) is created **only if `$wgCirrusSearchWikibaseIndexEntityData = true`**.

Wikibase requires some dependent extensions, such as:

* CirrusSearch
* Elastica
* EntitySchema (optional)
* WikibaseLexeme (optional)


## 1. Cloning Repos


```bash
cd /var/www/mediawiki/extensions

rm -rf Elastica CirrusSearch
git clone -b REL1_44 https://gerrit.wikimedia.org/r/mediawiki/extensions/Elastica  # 1. Elasticsearch client
git clone -b REL1_44 https://gerrit.wikimedia.org/r/mediawiki/extensions/CirrusSearch   # 2. CirrusSearch (search engine)
```

### 2. **Add to LocalSettings.php**

```php
# ------------------------------------------------------------------------------------------------------------
# CirrusSearch + Elastica Extension Start
# ------------------------------------------------------------------------------------------------------------

wfLoadExtension( 'Elastica' );
wfLoadExtension( 'CirrusSearch' );

# Elasticsearch Server
$wgCirrusSearchServers = [ 'localhost' ];
$wgCirrusSearchClusters = [
    'default' => [ 'localhost' ]
];

# Additional Settings
$wgSearchType = 'CirrusSearch';
$wgCirrusSearchWikimediaExtraPlugin = true;
$wgCirrusSearchWikibaseIndexEntityData = true;
$wgCirrusSearchWikibaseEntityTypes = [
    'item' => true,
    'property' => true
];
# -----------------------
# Default Lang
# -----------------------
$wgLanguageCode = "en";

// http://127.0.0.1:9500  using different ports works only in MediaWiki 1.45 version.
// 
//
// wfLoadExtension( 'Elastica' );
// wfLoadExtension( 'CirrusSearch' );

// # Additional Settings
// $wgSearchType = 'CirrusSearch';
// $wgCirrusSearchWikimediaExtraPlugin = true;

// # Elasticsearch Server
// $wgCirrusSearchServers = [ 'http://127.0.0.1:9200' ];
// $wgCirrusSearchClusters = [
//     'default' => [ 'http://127.0.0.1:9200' ]
// ];
// // (optional) maximum request time
// $wgCirrusSearchConnectionTimeout = 5;
// $wgCirrusSearchClientSideTimeout = 10;

// //(optional) Wikibase custom indexing
// $wgCirrusSearchUseCompletionSuggester = true;

// // Wikibase entity indexing is active
// $wgCirrusSearchWikibaseIndexEntityData = true;
// $wgCirrusSearchWikibaseEntityTypes = [
//     'item' => true,
//     'property' => true
// ];
// // (Optional) include descriptions and aliases in searches
// $wgCirrusSearchWikibaseSearchEntityDescriptions = true;
// $wgCirrusSearchWikibaseSearchEntityAliases = true;
// $wgCirrusSearchIndexBaseName = 'confident';

//
//
// Load Balancing: If you add multiple Elasticsearch nodes in the future, MediaWiki will automatically load balance them all:
//
// $wgCirrusSearchClusters = [
//     'default' => [
//         'http://10.0.0.1:9200',
//         'http://10.0.0.2:9200',
//         'http://10.0.0.3:9200'
//     ]
// ];
//
//
# ------------------------------------------------------------------------------------------------------------
# CirrusSearch + Elastica Extension End
# ------------------------------------------------------------------------------------------------------------
```

3. **Install Elasticsearch**

Elasticsearch is required for Wikibase's search/query engine:

https://www.digitalocean.com/community/tutorials/how-to-install-and-configure-elasticsearch-on-ubuntu-22-04

💡 Note: For Wikimedia 1.44 version only Elasticsearch 7.10.x SUPPORTED.

```bash
sudo apt update
sudo systemctl stop elasticsearch
sudo apt remove --purge elasticsearch -y
sudo rm -rf /var/lib/elasticsearch /etc/elasticsearch /usr/share/elasticsearch
sudo rm -rf /var/lib/elasticsearch/* /etc/elasticsearch/*
sudo apt install openjdk-17-jdk -y

cd /tmp
wget https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-7.10.2-amd64.deb
sudo dpkg -i elasticsearch-7.10.2-amd64.deb
sudo apt install elasticsearch
sudo systemctl enable --now elasticsearch
```

## ⚡ 4. **Edit ElasticSearch Configuration**

Elasticsearch works on 9200 as a default. Open Elasticsearch (elasticsearch.yml) to change it:

vim /etc/elasticsearch/elasticsearch.yml

```bash
# /etc/elasticsearch/elasticsearch.yml
# 

# Elasticsearch cluster name — tek node için basit bir ad yeterli
cluster.name: mediawiki

# Node kimliği (tek bir Elasticsearch çalışıyorsa node-1 yeterli)
node.name: node-1

# Yalnızca yerel bağlantılara izin ver
network.host: 0.0.0.0  # or just localhost
http.port: 9200 # desired port, e.g., 9200  -- Run Elasticsearch on a Different Port (Prevent port Collision on your local Machine)
## transport.port: 9200  default transport port

# Close discovery because it is a single node
discovery.type: single-node
```

# Or if using Docker:

```bash
docker run -p 9200:9200 docker.elastic.co/elasticsearch/elasticsearch:8.11.1
```

> Note: If you are working with Docker, you must also set the container port mapping correctly: -p hostPort:containerPort.


Adjust elasticsearchHost and elasticsearchPort according to your setup. If using Docker Compose, make sure the port is correctly mapped in your docker-compose.yml.

After installation, launch:

```bash
sudo systemctl daemon-reload
sudo systemctl enable elasticsearch
sudo systemctl start elasticsearch
sudo systemctl status elasticsearch
```

---

💡 **Tip:**
Wikibase and Elasticsearch are often run together with **Docker Compose**. In that case, it’s easier to manage port and environment settings directly in the `docker-compose.yml`.


## ⚡ 3. Run Maintenance &  Download Composer Dependencies

### Go to the MediaWiki root directory

```bash
cd /var/www/mediawiki
```

### 2. Check composer.local.json

If you don't have this file, copy the example:

```bash
cp composer.local.json-sample composer.local.json
```

It should look something like this:

```json
{
  "extra": {
    "merge-plugin": {
      "include": [
        "extensions/*/composer.json"
      ]
    }
  }
}
```
👉 This setting automatically loads the `"ruflin/elastica"` dependency in the `extensions/Elastica/composer.json` file.

> CirrusSearch REL1\_44 branch currently uses `ruflin/elastica` version 7.x.

Test:

```bash
curl -X GET http://127.0.0.1:9200

Expected Output:

```json
{
  "name" : "your-node-name",
  "cluster_name" : "elasticsearch",
  "version" : {
    "number" : "7.10.2"
  }
}
```

Update mediawiki.

```bash
php maintenance/update.php
```

Install depdencies.

```bash
rm -f composer.lock
composer install --no-dev
```

## ⚡ 4. Building Indexes

Run the following commands from your MediaWiki root directory:

### a. Update Elasticsearch index configuration:

```bash
cd /var/www/mediawiki
php extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php
```

### b. Reindex Wikibase entities:  (Elasticsearch Full Reindex)

```bash
cd /var/www/mediawiki
php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipLinks --indexOnSkip
php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipParse
```

* --skipParse → only index the page content without parsing it.
* --indexOnSkip → also index the skipped pages.

> 🔹 After this step, Elasticsearch will contain the following indexes:
>
> ```
> wikibase_content_first
> wikibase_general_first
> wikibase_wikibase_entity_first
> ```


💡 Tip: If you want to see all Wikibase entities and pages in Elasticsearch, you can consider updating the index with a periodic rebuild or cron job.

💡 Note: MediaWiki 1.44 + CirrusSearch is generally compatible with Elasticsearch 7.10.x or 8.9.x.
7.10.2 has been the stable version used in Wikibase packages for a long time.


> This process may take a long time for large databases. Wait until you see the "all documents indexed" message.

### 9. **Testing Elasticsearch**

* Check that Elasticsearch is running on the new port:

```bash
curl http://localhost:9200

// output must be like this
{
  "name" : "node-1",
  "cluster_name" : "mediawiki",
  "cluster_uuid" : "xxxx",
  "version" : {
    "number" : "7.10.2",
    "build_flavor" : "default",
    ...
  }
}
```

## 🔍 4. Verifying Indexes (CirrusSearch)

Check if the indexes have been created:

```bash
curl -X GET "http://127.0.0.1:9200/_cat/indices?v"
```

Example output:

```
health status index                     uuid                   pri rep docs.count docs.deleted store.size pri.store.size
green  open   mw_cirrus_metastore_first BvXNu1ElQdyMBRWFBVhEKg   1   0         28            2     85.5kb         85.5kb
green  open   wikidb_general_first      cT0dQwTAS6OMcceekC1kLw   1   0          0            0       208b           208b
green  open   wikidb_content_first      n-NcXbYDSnCwY28oYUOu0g   1   0          2            1     48.3kb         48.3kb
```

---

## 🧠 5. Sample Search (for testing)

Search for a Wikibase item label:

```bash
curl -X GET "http://127.0.0.1:9200/wikidb_content_first/_search?q=Test&pretty"

# {
#   "took" : 7,
#   "timed_out" : false,
#   "_shards" : {
#     "total" : 1,
#     "successful" : 1,
#     "skipped" : 0,
#     "failed" : 0
#   },
#   "hits" : {
#     "total" : {
#       "value" : 1,
#       "relation" : "eq"
#     },
#     "max_score" : 0.7768086,
#     "hits" : [
#       {
#         "_index" : "wikidb_content_first",
#         "_type" : "_doc",
#         "_id" : "2",
#         "_score" : 0.7768086,
#         "_source" : {
#           "version" : 2,
#           "wiki" : "wikidb",
#           "page_id" : 2,
#           "namespace" : 120,
#           "namespace_text" : "Item",
#           "title" : "Q1",
#           "timestamp" : "2025-10-09T18:48:19Z",
#           "create_timestamp" : "2025-10-09T18:48:19Z",
#           "category" : [ ],
#           "external_link" : [ ],
#           "outgoing_link" : [ ],
#           "template" : [ ],
#           "text" : "Test\ntest",
#           "source_text" : "Test\ntest",
#           "text_bytes" : 276,
#           "content_model" : "wikibase-item",
#           "display_title" : null,
#           "redirect" : [ ],
#           "incoming_links" : 0
#         }
#       }
#     ]
#   }
# }
```

This returns JSON results for all `item` and `property` entities containing "Berlin".

---

## 💡 6. Test in the Search Box

Type “Berlin” (or any Wikibase entity label) in the MediaWiki search box.
Results will now include **both wiki pages and item/property entities**.

---

## 🔒 7. Optional Advanced Setup (Index Prefix)

If multiple MediaWiki installations share the same Elasticsearch, avoid conflicts by adding a prefix:

```php
$wgCirrusSearchIndexBaseName = 'confident';
```

Resulting Elasticsearch indexes:

```
tibconfident_content_first
tibconfident_general_first
tibconfident_wikibase_entity_first
```


## Deleting All Indices

```bash
curl -X DELETE "http://127.0.0.1:9200/confident_*"
```

💡 Tip:

If you want to see all Wikibase entities and pages in Elasticsearch, you can consider updating the index with a periodic rebuild or cron job.

---

## 🚀 Summary of Key Settings

| Setting                                           | Description                            |
| ------------------------------------------------- | -------------------------------------- |
| `$wgCirrusSearchWikibaseIndexEntityData = true`   | Enable indexing of Wikibase entities   |
| `$wgCirrusSearchWikibaseEntityTypes`              | Entity types to index (item/property)  |
| `$wgCirrusSearchWikibaseSearchEntityDescriptions` | Include descriptions in search results |
| `$wgCirrusSearchWikibaseSearchEntityAliases`      | Include aliases in search results      |
| `$wgCirrusSearchUseCompletionSuggester = true`    | Enable autocomplete suggestions        |
| `$wgCirrusSearchIndexBaseName`                    | (Optional) index name prefix           |

---

If you want, I can now show you **how to enable CirrusSearch autocomplete** so that Wikibase entity labels are suggested instantly as you type—just like on Wikidata.org.

Do you want me to add that?

