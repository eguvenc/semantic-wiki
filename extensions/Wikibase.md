
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
cd /var/www/mediawiki/extensions
git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/Wikibase
cd Wikibase
git checkout REL1_44
git submodule update --init --recursive
composer install --no-dev
```

3. Run Composer to Install Dependencies

Then, from the root of your MediaWiki installation, run:

```bash
cd mediawiki/
rm composer.lock
composer install --no-dev
```

4. Update LocalSettings.php

```php
# ------------------------------------------------------------------------------------------------------------
# Wikibase Extension Start
# ------------------------------------------------------------------------------------------------------------

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

# ------------------------------------------------------------------------------------------------------------
# Wikibase Extension End
# ------------------------------------------------------------------------------------------------------------
```

## Update Database settings if Mysql Port Different like Below

```bash
$wgDBtype = "mysql";
$wgDBserver = "localhost:3308";
$wgDBname = "wikidb";
$wgDBuser = "admin";
$wgDBpassword = "Mbry8992@";
```

5. **Run maintenance scripts**

```bash
php maintenance/run.php update
php maintenance/run.php ./extensions/Wikibase/lib/maintenance/populateSitesTable.php
php maintenance/run.php ./extensions/Wikibase/repo/maintenance/rebuildItemsPerSite.php
php maintenance/run.php populateInterwiki
```

### 7. **Testing Wikibase Using UI**

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