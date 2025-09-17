
## Wikibase Extension Installation For MediaWiki 1.44


* **SMW** → Focuses on generating meaningful content with semantic data, tables, and forms within MediaWiki.
* **Wikibase** → Provides a central database for structured data (publishing Wikidata).

👉 It is technically possible to use both together, but generally **either SMW or Wikibase** is preferred. Because they can logically conflict.

---

### Installation

https://www.mediawiki.org/wiki/Wikibase/Installation


1. **Switch to MediaWiki extension folder**

```bash
cd /var/www/mediawiki/extensions
```

2. **Download Wikibase**


```bash
wget https://extdist.wmflabs.org/dist/extensions/Wikibase-REL1_44-669ae44.tar.gz
tar -xzf Wikibase-REL1_44-669ae44.tar.gz -C /var/www/mediawiki/extensions
```

3. Run Composer to Install Dependencies

Then, from the root of your MediaWiki installation, run:

```bash
cd mediawiki/
rm composer.lock
```

Then, assuming composer is available as a binary:

```bash
composer install --no-dev
```

3. Update LocalSettings.php

```php
# -----------------------
# Wikibase Extension Start
# -----------------------
wfLoadExtension( 'WikibaseRepository', "$IP/extensions/Wikibase/extension-repo.json" );
require_once "$IP/extensions/Wikibase/repo/ExampleSettings.php";

wfLoadExtension( 'WikibaseClient', "$IP/extensions/Wikibase/extension-client.json" );
require_once "$IP/extensions/Wikibase/client/ExampleSettings.php";

$wgEnableWikibaseRepo = true;
$wgEnableWikibaseClient = true;

# Repo Settings
$wgWBRepoSettings['dataRightsUrl'] = "https://creativecommons.org/publicdomain/zero/1.0/";
$wgWBRepoSettings['siteGlobalID'] = 'test-wiki';

# Client Settings (To display repo)
$wgWBClientSettings['repoUrl'] = "http://mediawiki.local";
$wgWBClientSettings['repoScriptPath'] = "/w";  # Change according to MediaWiki installation
$wgWBClientSettings['repoDatabase'] = "wikidb";
$wgWBClientSettings['changesDatabase'] = "wikidb";

# -----------------------
# Wikibase Extension End
# -----------------------
```

4. **Run maintenance scripts**

```bash
php maintenance/run.php update
php maintenance/run.php ./extensions/Wikibase/lib/maintenance/populateSitesTable.php
php maintenance/run.php ./extensions/Wikibase/repo/maintenance/rebuildItemsPerSite.php
php maintenance/run.php populateInterwiki
```

**Install Dependencies**

Wikibase requires some dependent extensions, such as:

* CirrusSearch
* Elastica
* EntitySchema (optional)
* WikibaseLexeme (optional)

The CirrusSearch extension implements searching for MediaWiki using Elasticsearch.

```bash
git clone -b REL1_44 https://gerrit.wikimedia.org/r/mediawiki/extensions/CirrusSearch.git
git clone -b REL1_44 https://gerrit.wikimedia.org/r/mediawiki/extensions/Elastica.git
```

**Install Elasticsearch**

Elasticsearch is required for Wikibase's search/query engine:

https://www.digitalocean.com/community/tutorials/how-to-install-and-configure-elasticsearch-on-ubuntu-22-04

For Wikimedia 1.44 version only Elasticsearch 7.10.x SUPPORTED.

```bash
sudo systemctl stop elasticsearch
sudo apt remove elasticsearch -y
sudo rm -rf /var/lib/elasticsearch/* /etc/elasticsearch/*

cd /tmp
wget https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-7.10.2-amd64.deb
sudo dpkg -i elasticsearch-7.10.2-amd64.deb
sudo systemctl enable --now elasticsearch


sudo apt install elasticsearch
```

After installation, launch:

```bash
sudo systemctl enable elasticsearch
sudo systemctl start elasticsearch
```

### 1. Go to the MediaWiki root directory

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

### 3. Download Composer dependencies

```bash
rm -f composer.lock
composer install --no-dev
```

> CirrusSearch REL1\_44 dalı şu an `ruflin/elastica` 7.x sürümünü kullanıyor.


### 4.  **Add to LocalSettings.php**

```php
# -----------------------
# CirrusSearch + Elastica
# -----------------------
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

# -----------------------
# Default Lang
# -----------------------
$wgLanguageCode = "en";

# -----------------------
# Wikibase Extension End
# -----------------------
```

### 5. **Run database update**

```bash
php maintenance/update.php
```

### 6. **Create Elasticsearch index**

```bash
cd /var/www/mediawiki

php extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php
php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipLinks --indexOnSkip
php extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipParse
```


### 7. **Test**

* On the home page, check if you can add a new item (Q1, Q2, etc.) using **Special\:NewItem**.

👉 http://mediawiki.local/index.php/Special:NewItem

* Test if you can add a new property (P1, P2, etc.) using **Special\:NewProperty**.

👉 http://mediawiki.local/index.php/Special:NewProperty

* Test if search is working properly

👉 http://mediawiki.local/index.php/Special:Search

Write "test"  to search bar and click search.


Results should looks like this.

------------------------------------------

test (Q1)
a test value for elastic search
0 statements, 0 sitelinks - 10:37, 17 September 2025


### Important notes

* **SMW + Wikibase together** → Not recommended because they do the same thing with different logic.
* If you're building an **academic/institutional knowledge base** → Wikibase is more suitable.
* If you want **intra-wiki semantic forms + templates** → SMW is fine.