
# 1.43 version Installation


```sh
git clone --branch REL1_43 https://gerrit.wikimedia.org/r/mediawiki/core.git mediawiki2
chown -R www-data:www-data mediawiki2

cd mediawiki2
sudo -u www-data composer install --no-dev
````

Enable Skin i LocalSettings.php

```bash
cd mediawiki2

wget https://extdist.wmflabs.org/dist/skins/Vector-REL1_43-a760979.tar.gz
tar -xzf Vector-REL1_43-a760979.tar.gz -C /var/www/mediawiki2/skins
```

```php
wfLoadSkin( 'Vector' );
$wgDefaultSkin = 'vector';
```

Update settings.

```bash
php maintenance/update.php
```

## Extension Installation Order 

1- Semantic MediaWiki (SMW)
2- Wikibase (https://www.mediawiki.org/wiki/Wikibase/Installation)

// Disabled - 4- Semantic Wikibase (It supports just 1.35 version of MediaWiki) (SWM Have many problems at this version. . )
// 5- PageForms ve SRF
6- Elastica
7- CirrusSearch & WikibaseCirrusSearch

## Semantic Wiki Extension

```bash
{
	"extra": {
		"merge-plugin": {
			"include": [
				"extensions/*/composer.json",
				"skins/*/composer.json"
			]
		}
	},
	"config": {
		"allow-plugins": {
			"composer/installers": true,
			"dealerdirect/phpcodesniffer-composer-installer": true,
			"wikimedia/composer-merge-plugin": true
		}
	},
	"require": {
		"mediawiki/semantic-media-wiki": "~6.0.1"
	}
}
```

```bash
composer update
```

```php
#
# Semantic Wiki Extension
#
wfLoadExtension( 'SemanticMediaWiki' );
enableSemantics( 'mediawiki2.local' ); // veya kendi domainin

## enable SMW debug and warnings ..
#
$wgShowExceptionDetails = true;
$wgDebugToolbar = true;
$wgDevelopmentWarnings = true;
```

```bash
php maintenance/update.php
```

## Wikibase Extension (1_43)

```bash
cd /var/www/mediawiki2/extensions/
rm -rf Wikibase
git clone --branch REL1_43 https://gerrit.wikimedia.org/r/mediawiki/extensions/Wikibase
cd Wikibase
composer install --no-dev
```

```bash
php maintenance/update.php
```

```php
# ====================================================
# Wikibase Configuration (Repo + Client in same wiki)
# ====================================================

wfLoadExtension( 'WikibaseRepository', "$IP/extensions/Wikibase/extension-repo.json" );
require_once "$IP/extensions/Wikibase/repo/ExampleSettings.php";

wfLoadExtension( 'WikibaseClient', "$IP/extensions/Wikibase/extension-client.json" );
require_once "$IP/extensions/Wikibase/client/ExampleSettings.php";

# Base URLs
$wgWBRepoSettings['baseUri'] = "$wgServer/wiki/"; 
$wgWBRepoSettings['entityNamespaces']['item'] = NS_MAIN;
$wgWBClientSettings['repoUrl'] = $wgServer . "/wiki/";
$wgWBClientSettings['repoDatabase'] = $wgDBname;

# Wikibase entity namespace ayarları (gerekiyorsa)
$wgExtraNamespaces[WB_NS_ITEM] = 'Item';
$wgExtraNamespaces[WB_NS_ITEM_TALK] = 'Item_talk';
```

## Elastica → CirrusSearch → WikibaseCirrusSearch

```bash
cd /var/www/mediawiki2/extensions
```

# 1️⃣ Elastica

```bash
git clone -b REL1_43 https://gerrit.wikimedia.org/r/mediawiki/extensions/Elastica
cd Elastica
composer install --no-dev
```

# 2️⃣ CirrusSearch

```bash
git clone -b REL1_43 https://gerrit.wikimedia.org/r/mediawiki/extensions/CirrusSearch
cd ../CirrusSearch
composer install --no-dev
```

# 3️⃣ WikibaseCirrusSearch

```bash
git clone -b REL1_43 https://gerrit.wikimedia.org/r/mediawiki/extensions/WikibaseCirrusSearch
cd ../WikibaseCirrusSearch
composer install --no-dev
```

Eğer hata alınırsa  wfLoadExtension( 'WikibaseCirrusSearch' );  satırını kaldırın ve  

LocalSettings.php dosyasının başına bunu ekleyin. 

```php
<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
```

Hatalar varsa onları düzeltin ve  wfLoadExtension( 'WikibaseCirrusSearch' );   satırını yeniden açın.

ElasticSearch sunucusunun çalıştığından emin olun.

```bash
curl -X GET "localhost:9200/"
```

```php
#
# Elastica → CirrusSearch → WikibaseCirrusSearch
#
# Orders of extension is important !!!
# 
wfLoadExtension( 'Elastica' );
wfLoadExtension( 'CirrusSearch' );
wfLoadExtension( 'WikibaseCirrusSearch' );

// # Elasticsearch Server
$wgCirrusSearchServers = [ '127.0.0.1' ]; // !!! Important don't use port .. default is 9000
$wgSearchType = 'CirrusSearch';
```
 
# Index yapılandırmasını güncelle (UpdateSearchIndexConfig.php)
# Tüm sayfaları indekse ekle (zaman alabilir) (ForceSearchIndex.php --skipParse)
# Wikibase öğeleri için indeks oluştur (ForceSearchIndex.php --skipLinks --indexOnSkip)

```bash
php maintenance/run.php ./extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php
php maintenance/run.php ./extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipParse
php maintenance/run.php ./extensions/CirrusSearch/maintenance/ForceSearchIndex.php --skipLinks --indexOnSkip
```


## Wikibase Rest API 1.0 Activation

```bash
$wgRestAPIAdditionalRouteFiles[] = 'extensions/Wikibase/repo/rest-api/routes.json';
```