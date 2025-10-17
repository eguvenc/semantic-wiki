
## Installing Semantic MediaWiki Extension

* **SMW** → Focuses on generating meaningful content with semantic data, tables, and forms within MediaWiki.
* **Wikibase** → Provides a central database for structured data (publishing Wikidata).

👉 It is technically possible to use both together, but generally **either SMW or Wikibase** is preferred. Because they can logically conflict.

```sh
rm -rf vendor/
rm composer.lock
composer clear-cache

cd /var/www/mediawiki
rm -rf extensions/SemanticMediaWiki
rm -rf vendor/mediawiki/semantic-media-wiki
rm -rf vendor/mediawiki/semantic-result-formats
composer clear-cache

cd /var/www/mediawiki
```

https://www.semantic-mediawiki.org/wiki/Help:Installation/Quick_guide


With this method, you continue to use global composer, but the installation is done via composer.local.json.

```bash
COMPOSER=composer.local.json composer require --no-update mediawiki/semantic-media-wiki:"~4.2"
composer update
```

Add the following lines to LocalSettings.php:

```php
#
# Semantic Wiki Extension
#
wfLoadExtension( 'SemanticMediaWiki' );
enableSemantics( 'mediawiki.local' ); // veya kendi domainin

## enable SMW debug and warnings ..
#
$wgShowExceptionDetails = true;
$wgDebugToolbar = true;
$wgDevelopmentWarnings = true;
```

Start SMW database:

```sh
cd /var/www/mediawiki
php maintenance/update.php
php extensions/SemanticMediaWiki/maintenance/setupStore.php
```

## Installatipn Test

🧪 **1. MediaWiki arayüzüne gir ve:**

“Special:Version” sayfasını aç (örnek: https://localhost/wiki/Special:Version)  “Semantic MediaWiki” eklentisinin listede yer aldığını görmelisin.


The following extensions are very useful after installation:

PageForms
— Semantic data entry with forms

SemanticResultFormats
— Query views such as lists, tables, and maps

SemanticDrilldown
— Filtering interface


🧪 **1. Direct RDF Export Link for Testing**

If the "Export RDF" link still does not appear, you can manually access it like this:

Assume your page title is: **The Grand Budapest Hotel**

Manually enter the following URL in your browser:

```
http://mediawiki.local/index.php/Special:ExportRDF/The_Grand_Budapest_Hotel
```

Use underscores (`_`) instead of spaces. This is important for page URLs in MediaWiki.

---

🧩 **2. If the SMW Version is Missing or Outdated**

To check the installed version of Semantic MediaWiki:

Go to the **Special\:Version** page:

```
👉 http://mediawiki.local/index.php/Special:Version
```

🧼 **3. Summary Checklist**

* Is `enableSemantics('mediawiki.local')` set? ✅ / ❌
* Is `wfLoadExtension('SemanticMediaWiki')` present? ✅ / ❌
* Did you run `php maintenance/update.php`? ✅ / ❌
* Does **Special\:Version** show SMW? ✅ / ❌
* Does **Special\:ExportRDF/Page\_Title** work? ✅ / ❌

---

