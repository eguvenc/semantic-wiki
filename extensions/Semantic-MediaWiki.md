
## Installing Semantic MediaWiki Extension

```sh
cd /var/www/mediawiki
composer require mediawiki/semantic-media-wiki "^5.0"
```

Add the following lines to LocalSettings.php:

```php
## enable SMW extension
#
enableSemantics( 'mediawiki.local' ); // veya kendi domainin
wfLoadExtension( 'SemanticMediaWiki' );

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
```

## Check List

Sure! Here's the English version of your message:

---

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

On that page, you should see the **Semantic MediaWiki** extension and optionally **Semantic Result Formats**.

If Semantic MediaWiki does not appear at all, run the following command:

```bash
composer require mediawiki/semantic-media-wiki "^5.0"
```

---

🧼 **3. Summary Checklist**

* Is `enableSemantics('mediawiki.local')` set? ✅ / ❌
* Is `wfLoadExtension('SemanticMediaWiki')` present? ✅ / ❌
* Did you run `php maintenance/update.php`? ✅ / ❌
* Does **Special\:Version** show SMW? ✅ / ❌
* Does **Special\:ExportRDF/Page\_Title** work? ✅ / ❌

---

