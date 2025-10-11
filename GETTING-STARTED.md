
# Wikibase Installation Guide (For Event Projects like TIB Confident)

This guide explains how to set up Wikibase from scratch to develop an academic/event project similar to TIB Confident.

---

## 1️⃣ Prerequisites

You need the following before starting:

* **Server:** Ubuntu 22.04 or Debian 12 recommended
* **Web Server:** Apache or Nginx (Apache is more common)
* **PHP:** 8.1+ (PHP-FPM recommended)
* **Database:** MySQL/MariaDB 10.5+
* **Composer:** PHP package manager
* **Git:** For fetching source code
* **Node.js & npm:** Needed for frontend and WDQS UI
* **Memcached / Redis:** Optional for caching/performance

---

## 2️⃣ MediaWiki Installation

Wikibase is built on MediaWiki, so first we need to install MediaWiki.

1. INSTALLATION.md
2. MYSQL.md

* Then run the **MediaWiki web installer** to generate `LocalSettings.php`.

---

## 3️⃣ Install Wikibase Extensions

The main packages for Wikibase:

* **Wikibase Repository:** Core functionality
* **Wikibase Search:** ElasticSearch and CirrusSearch
* **Wikibase Client:** For sharing data with other Wikibases  (Optional)
* **WDQS / Blazegraph:** Querying and SPARQL support

### 3.1 Wikibase Repository

  * [extensions/Wikibase.md](extensions/Wikibase.md)

### 3.2 Wikibase Elastic Search (Search Extension)

  * [extensions/Wikibase-ElasticSearchAndCirrusSearch.md](extensions/Wikibase-ElasticSearchAndCirrusSearch.md)

### 3.3 Wikibase Client (This extension needed for connect with other providers)

  * [extensions/Wikibase-Client-Installation.md](extensions/Wikibase-Client-Installation.md)

### 3.4 WDQS / Blazegraph (Blazegraph powers the SPARQL endpoint)

  * [extensions/WDQS-Blazegraph.md](extensions/WDQS-Blazegraph.md)

- Use Apache/Nginx reverse proxy to make Blazegraph accessible at `/wdqs`.

---

## 4️⃣ WDQS UI (Optional)

If you want a visual interface for SPARQL queries:

  * extension/WDQS-UD.md

* Serve the build files via Apache.

---

## 5️⃣ MediaWiki + Wikibase Settings

* **Cache:** Connect Memcached or Redis:

```php
$wgMainCacheType = CACHE_MEMCACHED;
$wgMemCachedServers = [ '127.0.0.1:11211' ];
```

* **Uploads:** Secure `images` directory:

```bash
chown -R www-data:www-data images
chmod 755 images
```

* **Security:** Apply PHP and MediaWiki security best practices.

---

## 6️⃣ Test and Create Initial Entities

1. Open Wikibase in a browser.
2. Create an **Item** (e.g., “TIB Confident Event”).
3. Create **Properties** (e.g., “Event Date”, “Location”).
4. Test WDQS with a SPARQL query:

```sparql
SELECT ?item ?itemLabel WHERE {
  ?item wdt:P31 wd:Q1 .
} LIMIT 10
```

---

## 7️⃣ Development Tips

* For an event project, define all entity types: Event, Speaker, Location, Session, etc.
* Prepare SPARQL queries and enable WDQS.
* Optionally, build a frontend in Vue/React that queries REST or SPARQL endpoints.
* For TIB Confident-like projects, **Wikibase Repository + WDQS** is sufficient; Client/UI are optional.

---

✅ **Summary**

1. Install MediaWiki
2. Install Wikibase Repository + Client
3. Install Blazegraph / WDQS
4. Configure cache and security
5. Create initial items and properties
6. Test with SPARQL queries
7. Optional: integrate REST or frontend for development

---

I can also prepare a **Docker-based all-in-one development setup** for Wikibase, so you can spin up the full environment with a single command.

Do you want me to create that Docker setup?
