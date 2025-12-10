
# Wikibase Relase Pipeline

https://github.com/wmde/wikibase-release-pipeline/blob/main/deploy/README.md

## Installation

--> https://github.com/wmde/wikibase-release-pipeline/blob/main/deploy/README.md

```sh
git clone https://github.com/wmde/wikibase-release-pipeline
git checkout deploy@5.0.1

cd deploy
cp template.env .env
```

Example Env

```
METADATA_CALLBACK=true
#METADATA_CALLBACK=false

# Public hostname configuration.

WIKIBASE_PUBLIC_HOST=wikibase.everydayjazz.com
WDQS_PUBLIC_HOST=query.everydayjazz.com

# MediaWiki / Wikibase user configuration.

MW_ADMIN_NAME=admin
MW_ADMIN_EMAIL=admin@everydayjazz.com
MW_ADMIN_PASS=set-a-password

# MediaWiki / Wikibase database configuration.

DB_NAME=wikibase
DB_USER=admin
DB_PASS=set-a-password
```

```sh
docker compose up --build -d
```

## Set MediaWiki Debugs On

```php
$wgDevelopmentWarnings = true;
$wgShowExceptionDetails = true;
$wgShowDBErrorBacktrace = true;
```

## Extensions Çıktısı (SEMANTIC JSON FORMAT)

```bash
https://wikibase.everydayjazz.com/w/api.php?action=query&meta=siteinfo&siprop=extensions
```

Docker Composer.yml

CLOUDFLARE FREE SSL CONFIG.

```yml
name: wbs-deploy

services:
  # --------------------------------------------------
  # A. CORE WIKIBASE SUITE SERVICES
  # --------------------------------------------------

  wikibase:
    image: wikibase/wikibase:5
      - "traefik.enable=true"
      - "traefik.http.routers.wikibase.rule=Host(`wikibase.everydayjazz.com`)"
      - "traefik.http.routers.wikibase.entrypoints=web"
    depends_on:
      mysql:
        condition: service_healthy
      elasticsearch:
        condition: service_healthy
    restart: unless-stopped
    volumes:
      - ./config:/config:z
      - ./config/extensions:/var/www/html/extensions/extensions:z
      - ./config/Extensions.php:/var/www/html/LocalSettings.d/90_UserDefinedExtensions.php:z
      - wikibase-image-data:/var/www/html/images
      - quickstatements-data:/quickstatements/data
    environment:
      METADATA_CALLBACK: ${METADATA_CALLBACK}
      MW_ADMIN_NAME: ${MW_ADMIN_NAME}
      MW_ADMIN_PASS: ${MW_ADMIN_PASS}
      MW_ADMIN_EMAIL: ${MW_ADMIN_EMAIL}
      MW_WG_SERVER: https://${WIKIBASE_PUBLIC_HOST}
      DB_SERVER: mysql:3306
      DB_USER: ${DB_USER}
      DB_PASS: ${DB_PASS}
      DB_NAME: ${DB_NAME}
      ELASTICSEARCH_HOST: elasticsearch
      QUICKSTATEMENTS_PUBLIC_URL: https://${WIKIBASE_PUBLIC_HOST}/tools/quickstatements
      WDQS_PUBLIC_ENDPOINT_URL: https://${WDQS_PUBLIC_HOST}/sparql
      WDQS_PUBLIC_FRONTEND_URL: https://${WDQS_PUBLIC_HOST}
    healthcheck:
      test: curl --silent --fail localhost/wiki/Main_Page
      interval: 10s
      start_period: 5m

  wikibase-jobrunner:
    image: wikibase/wikibase:5
    command: /jobrunner-entrypoint.sh
    depends_on:
      wikibase:
        condition: service_healthy
    restart: unless-stopped
    volumes_from:
      - wikibase

  mysql:
    image: mariadb:10.11
    restart: unless-stopped
    volumes:
      - mysql-data:/var/lib/mysql
    environment:
      MYSQL_DATABASE: ${DB_NAME}
      MYSQL_USER: ${DB_USER}
      MYSQL_PASSWORD: ${DB_PASS}
      MYSQL_RANDOM_ROOT_PASSWORD: yes
    healthcheck:
      test: healthcheck.sh --connect --innodb_initialized
      start_period: 1m
      interval: 20s
      timeout: 5s

  # --------------------------------------------------
  # B. EXTRA WIKIBASE SUITE SERVICES
  # --------------------------------------------------

  # To disable Elasticsearch and use default MediaWiki search functionality remove
  # the elasticsearch service, and the MW_ELASTIC_* vars from wikibase_variables
  # at the top of this file.
  elasticsearch:
    image: wikibase/elasticsearch:1
    restart: unless-stopped
    volumes:
      - elasticsearch-data:/usr/share/elasticsearch/data
    environment:
      discovery.type: single-node
      ES_JAVA_OPTS: -Xms512m -Xmx512m -Dlog4j2.formatMsgNoLookups=true
    healthcheck:
      test: curl --silent --fail localhost:9200
      interval: 10s
      start_period: 2m

  wdqs:
    image: wikibase/wdqs:2
    command: /runBlazegraph.sh
    depends_on:
      wikibase:
        condition: service_healthy
    restart: unless-stopped
    # Set number of files ulimit high enough, otherwise blazegraph will abort with:
    # library initialization failed - unable to allocate file descriptor table - out of memory
    # Appeared on Docker 24.0.5, containerd 1.7.9, Linux 6.6.6, NixOS 23.11
    ulimits:
      nofile:
        soft: 32768
        hard: 32768
    volumes:
      - wdqs-data:/wdqs/data
    healthcheck:
      test: curl --silent --fail localhost:9999/bigdata/namespace/wdq/sparql
      interval: 10s
      start_period: 2m

  wdqs-updater:
    image: wikibase/wdqs:2
    command: /runUpdate.sh
    depends_on:
      wdqs:
        condition: service_healthy
    restart: unless-stopped
    # Set number of files ulimit high enough, otherwise blazegraph will abort with:
    # library initialization failed - unable to allocate file descriptor table - out of memory
    # Appeared on Docker 24.0.5, containerd 1.7.9, Linux 6.6.6, NixOS 23.11
    ulimits:
      nofile:
        soft: 32768
        hard: 32768
    environment:
      WIKIBASE_CONCEPT_URI: https://${WIKIBASE_PUBLIC_HOST}

  wdqs-frontend:
    image: wikibase/wdqs-frontend:2
    restart: unless-stopped
    volumes:
      - ./config:/config:z
    environment:
      WDQS_PUBLIC_URL: https://${WDQS_PUBLIC_HOST}/sparql
      WIKIBASE_PUBLIC_URL: https://${WIKIBASE_PUBLIC_HOST}/w/api.php
    healthcheck:
      test: curl --silent --fail localhost
      interval: 10s
      start_period: 2m

  quickstatements:
    image: wikibase/quickstatements:1
    depends_on:
      wikibase:
        condition: service_healthy
    restart: unless-stopped
    volumes:
      - quickstatements-data:/quickstatements/data
    environment:
      QUICKSTATEMENTS_PUBLIC_URL: https://${WIKIBASE_PUBLIC_HOST}/tools/quickstatements
      WIKIBASE_PUBLIC_URL: https://${WIKIBASE_PUBLIC_HOST}
    healthcheck:
      test: curl --silent --fail localhost
      interval: 10s
      start_period: 2m

  # --------------------------------------------------
  # C. REVERSE PROXY AND SSL SERVICES
  # --------------------------------------------------

  # This is the reverse proxy and SSL service
  traefik:
    image: traefik:3
    command:
      # traefik static configuration via command line
      # enable accesslog
      - "--accesslog.format=common"
      # http endpoint
      - "--entrypoints.web.address=:80"
      # https endpoint
      #- "--entrypoints.websecure.address=:443"
      #- "--entrypoints.websecure.asdefault"
      #- "--entrypoints.websecure.http.tls.certresolver=letsencrypt"
      # http to https redirect
      #- "--entrypoints.web.http.redirections.entryPoint.to=websecure"
      #- "--entrypoints.web.http.redirections.entryPoint.scheme=https"
      #- "--entrypoints.web.http.redirections.entrypoint.permanent=true"
      # ACME SSL certificate generation
      #- "--certificatesresolvers.letsencrypt.acme.httpchallenge=true"
      #- "--certificatesresolvers.letsencrypt.acme.httpchallenge.entrypoint=web"
      #- "--certificatesresolvers.letsencrypt.acme.email=${MW_ADMIN_EMAIL}"
      #- "--certificatesresolvers.letsencrypt.acme.storage=/letsencrypt/acme.json"
      # additionial traefik dynamic configuration via config file
      - "--providers.file.filename=/etc/traefik/dynamic.yml"
      # Uncomment this line to only test ssl generation first, makes sure you don't run into letsencrypt rate limits
      # - "--certificatesresolvers.letsencrypt.acme.caserver=https://acme-staging-v02.api.letsencrypt.org/directory"
      # Uncomment the following line for debugging, also expose port 8080 below
      # - "--api.dashboard=true"
      # - "--api.insecure=true"
      # - "--log.level=DEBUG"
    restart: unless-stopped
    ports:
      - 80:80
      #- 443:443
      # traefik dashboard
      # - 8080:8080
    volumes:
      - ./config/traefik-dynamic.yml:/etc/traefik/dynamic.yml:ro
      - traefik-letsencrypt-data:/letsencrypt
    environment:
      WIKIBASE_PUBLIC_HOST: ${WIKIBASE_PUBLIC_HOST}
      WDQS_PUBLIC_HOST: ${WDQS_PUBLIC_HOST}

volumes:
  # A. CORE WIKIBASE SUITE SERVICES DATA
  wikibase-image-data:
  mysql-data:
  # B. EXTRA WIKIBASE SUITE SERVICES DATA
  wdqs-data:
  elasticsearch-data:
  quickstatements-data:
  # C. REVERSE PROXY AND SSL SERVICES DATA
  traefik-letsencrypt-data:
```

## Watching logs:

```sh
docker compose logs wikibase --tail=100
```

## Installing Wikibase Faceted Search Extension


```sh
ls -l /var/www/html/composer.local.json
```

## Installing Composer

Install necessary PHP extensions and tools

```bash
apt-get update && apt-get install -y \
    php-cli \
    php-json \
    php-zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*
```

Download and Install Composer

```php
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

'e21205b207c372993fd87812bcfa0ab98573a39d84d851b9699a547811bc7c68ab6e1e64ad677a7fe7b8bf978d54708c') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

php composer-setup.php --install-dir=/usr/local/bin --filename=composer

php -r "unlink('composer-setup.php');"
```

## Allowing Remote Write Access From .VsCode

Giving write permissions to Local Json file


```sh
chown root:root /var/www/html/composer.local.json
```

## Installation FACETED SEARCH Extension

Load the extension using Composer:


```sh
COMPOSER=composer.local.json composer require --no-update professional-wiki/wikibase-faceted-search:~1.0
```

Updating composer.local.json.


```json
{
  "require": {
        "professional-wiki/wikibase-faceted-search": "~1.0" 
    },
  "extra": {
    "merge-plugin": {
      "include": [ "extensions/*/composer.json", "skins/*/composer.json" ]
    }
  }
}
```

Enable the extension by adding the following to your LocalSettings.php:

```php
wfLoadExtension( 'WikibaseFacetedSearch' );
```

Updating composer and local packages

```bash
rm -rf composer.lock
composer update --no-dev
```

```bash
php maintenance/update.php
php maintenance/showJobs.php
php maintenance/runJobs.php
```

Rebuild index

```bash
php extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php
php extensions/CirrusSearch/maintenance/ForceSearchIndex.php
```

Available Scripts:

```bash
CheckIndexes.php
ExpectedIndices.php
RunSearch.php
UpdateOneSearchIndexConfig.php
CirrusNeedsToBeBuilt.php
ForceSearchIndex.php
Saneitize.php
UpdateSearchIndexConfig.php
CopySearchIndex.php
IndexNamespaces.php
SaneitizeJobs.php
UpdateSuggesterIndex.php
DumpIndex.php
Metastore.php
UpdateDYMIndexTemplates.php
UpdateWeightedTags.php
```

## Creating Index with Json Configuration

Go to this page

```bash
https://wikibase.everydayjazz.com/wiki/MediaWiki:WikibaseFacetedSearch
```

Add date type ranges using json file and click to save. 

P5: instance of Event
Q2: is category Event item  (https://wikibase.everydayjazz.com/wiki/Item:Q2)

```json
{
  "itemTypeProperty": "P5",
  "configPerItemType": {
    "Q2": {
      "icon": "calendar",
      "facets": {
        "P3": {
          "type": "range"
        },
        "P4": {
          "type": "range"
        },
        "P1": {
          "type": "list",
          "showAnyFilter": true,
          "showNoneFilter": false
        }
      }
    }
  }
}
```

Update FacetedSearch Index.


```bash
php maintenance/run.php CirrusSearch:UpdateSearchIndexConfig
php maintenance/run.php CirrusSearch:ForceSearchIndex --skipParse
php maintenance/run.php CirrusSearch:ForceSearchIndex --skipLinks --indexOnSkip
php maintenance/run.php WikibaseCirrusSearch:UpdateSearchIndex  # wikibase itemlarını indexler
```

## User Defined Config File

```php
<?php
// ************************************************************************
// Wikibase Suite Deploy Extension.php
// ************************************************************************
//
// File to load MediaWiki extension.
//
// This file will be loaded after all other extensions have been loaded,
// just like as if this code would be at the end of LocalSettings.php.
//
// Make sure to prefix the extensions name with "extensions/" when loading.
// e.g. when extension installation instructions state you need to put
//   wfLoadExtension( 'WikibaseLexeme' );
// here in Wikibase Suite Deploy you need to put
//   wfLoadExtension( 'extensions/WikibaseLexeme' );

wfLoadExtension( 'WikibaseFacetedSearch' );
wfLoadExtension( 'FacetedApiSearch' );

# Development error & debug settings
$wgDevelopmentWarnings = true;
$wgShowExceptionDetails = true;
$wgShowDBErrorBacktrace = true;

# Elasticsearch configuration for FacetedApiSearch and WikibaseSearch
$wgWikibaseRootPath = "/var/www/html";
$wgElasticsearchIndexName = "wikibase_content_first";
$wgElasticsearchBaseUrl = "http://wbs-deploy-elasticsearch-1:9200";
$wgFacetedSuggestProperties = [
    'P1',
];
```

## QUICK STATEMENTS

https://wikibase.everydayjazz.com/tools/quickstatements/#/

## ElasticSearch

Cirrus Search Configuration

```php
if (isset($elasticsearchHost)) {
    // https://www.mediawiki.org/wiki/Extension:WikibaseCirrusSearch
    wfLoadExtension( 'WikibaseCirrusSearch' );

    $wgCirrusSearchServers = [ $elasticsearchHost ];
    $wgSearchType = 'CirrusSearch';
    $wgCirrusSearchExtraIndexSettings['index.mapping.total_fields.limit'] = 5000;
    $wgWBCSUseCirrus = true;
    
    // Enable completion suggester
    // $wgCirrusSearchCompletionSuggester = true; 
}
```

Mevcut index’i silmek

(Elastic tarafındaki mapping değiştirilemediği için index’i silip yeniden oluşturmak gerekiyor.)

```bash
curl -X DELETE "http://localhost:9200/wikibase_content_first"
```

MediaWiki’den index şemasını yeniden oluştur

```bash
php extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php
```

Bu işlem tüm wikibase item’larını yeniden Elastic’e aktarır.

```bash
php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipLinks --indexOnSkip
```

## Entity Dump

```bash
php maintenance/run.php /var/www/html/extensions/Wikibase/repo/maintenance/dumpRdf.php --format ttl --output /var/www/html/dump_entities.ttl --entity-type item
```

### Dependencies

Look at for vendor folder if not exixsts install composer dependencies

```sh
cd extensions/WikibaseFacetedSearch/
ls  
composer install --no-dev
```

### Creating Tab Header

1. Şu sayfaya git:

   ```
   https://wikibase.everydayjazz.com/w/index.php?title=MediaWiki:WikibaseFacetedSearch-item-type-Q2&action=edit
   ```
2. İçine örneğin şunu yaz:

   ```
   Event
   ```

   (Eğer Q2 senin “Event” item’ına karşılık geliyorsa — senin önceki örneklerinde öyleydi.)
3. Kaydet.
4. Ardından şu sayfaya git:

   ```
   https://wikibase.everydayjazz.com/wiki/Special:FacetedSearch
   ```

   ve sayfayı yenile.

---

### 💡 Ek kontrol

Benzer şekilde aşağıdaki mesajların da olması gerekir (boş olsa bile):

```
MediaWiki:WikibaseFacetedSearch-header
MediaWiki:WikibaseFacetedSearch-footer
MediaWiki:WikibaseFacetedSearch-no-results
```

## Header

https://wikibase.everydayjazz.com/wiki/MediaWiki:WikibaseFacetedSearch-header

```
<h2>Faceted Search</h2>
<p>Use the filters on the left to explore the items.</p>
```

## Footer

```
<p>Footer</p>
```

## No Results

```
<p>No Results</p>
```

## SPARQL SORGULARI ÇALIŞMIYORSA !!!

Olması gereken... servisler ..

root@ubuntu-4gb-nbg1-5:~# docker ps


CONTAINER ID   IMAGE                      COMMAND                  CREATED        STATUS                            PORTS                                 NAMES
ae3e17dc218a   wikibase/wdqs:2            "/entrypoint.sh /run…"   15 hours ago   Up 43 seconds                     9999/tcp                              wbs-deploy-wdqs-updater-1
8760e38ae9e0   wikibase/wdqs:2            "/entrypoint.sh /run…"   15 hours ago   Up 2 seconds (health: starting)   9999/tcp                              wbs-deploy-wdqs-1
547adb06b455   wikibase/wikibase:5        "/bin/bash /entrypoi…"   15 hours ago   Up 11 hours (healthy)             80/tcp                                wbs-deploy-wikibase-1
c83a22dabcad   wikibase/wdqs-frontend:2   "/entrypoint.sh ngin…"   15 hours ago   Up 11 hours (healthy)             80/tcp                                wbs-deploy-wdqs-frontend-1
0cc7afa44304   mariadb:10.11              "docker-entrypoint.s…"   15 hours ago   Up 11 hours (healthy)             3306/tcp                              wbs-deploy-mysql-1
730a77a62aed   wikibase/elasticsearch:1   "/tini -- /usr/local…"   15 hours ago   Up 11 hours (healthy)             9200/tcp, 9300/tcp                    wbs-deploy-elasticsearch-1
f9e6a98fe7d7   traefik:3                  "/entrypoint.sh --ac…"   15 hours ago   Up 11 hours                       0.0.0.0:80->80/tcp, [::]:80->80/tcp   wbs-deploy-traefik-1
root@ubuntu-4gb-nbg1-5:~# 


wdqs ve updater yoksa ...

```
docker start wbs-deploy-wdqs-1
docker start wbs-deploy-wdqs-updater-1
```

## WDQS Updater DEBUG - Check the Configuration


```sh
cd /wikibase-release-pipeline/deploy/

docker ps
docker exec -it wbs-deploy-wdqs-updater-1 bash
```

## WATCH ERROR LOGS


```bash
docker compose logs wdqs-updater -f
```

Starting Container


```bash
docker compose up -d wdqs-updater
```

Test 

```sh
blazegraph@f313b2ab2abb:/wdqs$ curl -I http://wdqs:9999/bigdata/namespace/wdq/sparql
```


BlazeGraph Çalışıyor mu Kontrol et: SPARQL

https://query.everydayjazz.com/

```sh
SELECT ?s ?p ?o
WHERE { ?s ?p ?o }
LIMIT 10
```

Manual olarak RDF DUMP al

Expected Output:
-------------------------------
s p o
 <https://wikibase.everydayjazz.com/entity/Q3>  <https://wikibase.everydayjazz.com/prop/P3> <https://wikibase.everydayjazz.com/entity/statement/Q3-2ABAD7B8-D4B2-44C3-936A-3280ACB4A3D2>
 <https://wikibase.everydayjazz.com/entity/Q3>  <https://wikibase.everydayjazz.com/prop/P4> <https://wikibase.everydayjazz.com/entity/statement/Q3-901A4D48-59F4-4A1D-8B8B-094254B8B8E4>
 <https://wikibase.everydayjazz.com/entity/Q3>  <https://wikibase.everydayjazz.com/prop/P5> <https://wikibase.everydayjazz.com/entity/statement/Q3-1FD4717F-F4FA-4067-92EF-DEFAFB907D2C>
 <https://wikibase.everydayjazz.com/entity/Q3>  <https://wikibase.everydayjazz.com/prop/direct/P6>   <https://wikibase.everydayjazz.com/entity/Q299>
 <https://wikibase.everydayjazz.com/entity/Q3>  <https://wikibase.everydayjazz.com/prop/P6> <https://wikibase.everydayjazz.com/entity/statement/Q3-9D7C4735-ED02-49E5-9C4F-FDE78A64E1DC>
 <https://wikibase.everydayjazz.com/entity/Q3>  <https://wikibase.everydayjazz.com/prop/direct/P9>  1
 <https://wikibase.everydayjazz.com/entity/Q3>  <https://wikibase.everydayjazz.com/prop/P9> <https://wikibase.everydayjazz.com/entity/statement/Q3-28782DEF-61B1-428A-BDE1-989A8CEFEA33>
 <https://wikibase.everydayjazz.com/entity/Q3>  schema:version  597
 <https://wikibase.everydayjazz.com/entity/Q3>  schema:dateModified 27 November 2025
 <https://wikibase.everydayjazz.com/entity/Q3>  rdfs:label  Test1 Conference (Created By API)


```bash
php extensions/Wikibase/repo/maintenance/dumpRdf.php --format ttl --dir /var/www/html/dumps
```

WDQS Updater ı durdur

```bash
root@ubuntu-4gb-nbg1-5:~/wikibase-release-pipeline/deploy#

docker stop wbs-deploy-wdqs-updater-1
docker stop wbs-deploy-wdqs-1
```

Dumping RDF data for BlazeGraph


```sh
/var/www/html# php maintenance/run.php /var/www/html/extensions/Wikibase/repo/maintenance/dumpRdf.php \
    --format ttl \
    --output /var/www/html/dump.ttl


/var/www/html# php maintenance/run.php /var/www/html/extensions/Wikibase/repo/maintenance/dumpRdf.php \
  --format ttl \
  --output /var/www/html/dump_entities.ttl \
  --entity-type item

Dumping entities of type item, property
Dumping shard 0/1
Processed 7 entities.
```

Copy dump.ttl and paste it to wbs-deploy-wdqs-1:/wdqs/data/dump.ttl

```sh
docker cp wbs-deploy-wikibase-1:/var/www/html/dump.ttl /tmp/dump.ttl  
docker cp wbs-deploy-wikibase-1:/var/www/html/dump_entities.ttl /tmp/dump_entities.ttl  
```

Paste it to wbs-deploy-wdqs-1:/wdqs/data folder

```sh
docker cp /tmp/dump.ttl wbs-deploy-wdqs-1:/wdqs/data/dump.ttl
docker cp /tmp/dump_entities.ttl wbs-deploy-wdqs-1:/wdqs/data/dump_entities.ttl
```

```sh
docker exec -it wbs-deploy-wdqs-1 bash
```

Yükleme tamamlandıktan sonra Updater container’ı çalıştırabilirsin.


2️⃣ WDQS container’ını durdur

```sh
docker stop wbs-deploy-wdqs-updater-1
docker stop wbs-deploy-wdqs-1
```

3️⃣ WDQS container’ını sıfırdan başlat (dump otomatik yüklenir)

WDQS container’ı başlatıldığında /data/\*.ttl dosyalarını otomatik olarak okur ve Blazegraph’a yükler:

```sh
docker start wbs-deploy-wdqs-1
```

Logları takip et:

```sh
docker logs -f wbs-deploy-wdqs-1
```

“Loaded data from /data/dump.ttl” gibi bir mesaj görene kadar bekle.

4️⃣ Updater’ı çalıştır

```sh
docker start wbs-deploy-wdqs-updater-1
docker logs -f wbs-deploy-wdqs-updater-1
```

Artık updater crash yapmamalı ve SPARQL endpoint veri döndürmeli.


Yüklemeyi doğrula sonuç geliyorsa sorgu doğrudur.

```sql
SELECT ?s ?p ?o
WHERE { ?s ?p ?o }
LIMIT 10
```


blazegraph@760a9dc86693:/wdqs$ ls
RWStore.properties    forAllCategoryWikis.sh       loadRestAPI.sh        reconcile_items.py
allowlist.txt     jetty-runner-9.4.12.v20180830.jar  logback.xml           runBlazegraph.sh
blazegraph-service-0.3.142.war  ldf-config.json        munge.sh          runStreamingUpdater.sh
createNamespace.sh    lib          mw-oauth-proxy-0.3.142.war  runUpdate.sh
data        loadCategoryDaily.sh       mwservices.json         summarizeEvents.sh
default.properties    loadCategoryDump.sh      prefixes-sdc.conf         wcqs-data-reload.sh
docs        loadData.sh        prefixes.conf
blazegraph@760a9dc86693:/wdqs$ bash loadData.sh -n wikidata -d /wdqs/data/
File /wdqs/data//wikidump-000000001.ttl.gz not found, terminating
blazegraph@760a9dc86693:/wdqs$ bash loadData.sh -n wikidata -d /wdqs/data 
File /wdqs/data/wikidump-000000001.ttl.gz not found, terminating
blazegraph@760a9dc86693:/wdqs$ mv /wdqs/data/dump_entities.ttl.gz /wdqs/data/wikidump-000000001.ttl.gz
blazegraph@760a9dc86693:/wdqs$ bash loadData.sh -n wikidata -d /wdqs/data
Processing wikidump-000000001.ttl.gz



## Searching on Special:Search Page

```
https://wikibase.everydayjazz.com/wiki/Special:Search
```

When you click to Event tab you need to see the ALL event results in Event Categoryu like as below.


Test1 Conference (Q3)
5 statements, 0 sitelinks - 07:16, 11 November 2025


# The Query String
This documentation is only for advanced users. You do not need to understand what the query string is to use Wikibase Faceted Search.

# Advanced users might wish to understand the query string, manipulate it directly, or use it in their own code. The query string is a string that represents the current state of the facets in the sidebar. It is used by the extension to filter search results.

# Example query string:

foo haswbfacet:P1=Q123 haswbfacet:P2=a|b|c haswbfacet:P3

This query matches all pages that contain foo, have a statement for P1 with value Q123, have a statement for P2 with value a, b, or c, and have at least one statement for P3.

haswbfacet Keyword Reference
haswbfacet:P1 - There is at least one value for property P1
-haswbfacet:P1 - There is no value for property P1
haswbfacet:P1=A|B|C - There is at least one value for property P1 that is either A, B, or C
haswbfacet:P1=A haswbfacet:P1=B - P1 has at least one value that is A and at least one value that is B
haswbfacet:P1>2020 - P1 has at least one value that is greater than 2020
haswbfacet:P1<2020 haswbfacet:P1>2010 - P1 has at least one value that is between 2010 and 2020

