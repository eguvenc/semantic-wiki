

# Wikibase Client Installation Guide (Safe and Modern Method)

This guide explains how to install **Wikibase Client** from scratch in a secure and sustainable way. Wikibase Client is used to **consume data from other Wikibase instances**.

---

## 1️⃣ Prerequisites

Make sure the server meets the following requirements:

* **Server:** Ubuntu 22.04 / Debian 12
* **Web Server:** Apache or Nginx (Apache recommended)
* **PHP:** 8.1+ (PHP-FPM recommended)
* **Database:** MySQL/MariaDB 10.5+
* **Composer** (PHP package manager)
* **Git**
* **Node.js & npm** (needed for WDQS UI or frontend, optional)

> Note: Client is a read-only extension. No additional database configuration is needed, but a Wikibase Repository must already be installed.

---

## 2️⃣ Install Wikibase Client

1. **Go to the extensions directory:**

```bash
cd /var/www/mediawiki/extensions
```

2. **Clone the Client repository:**

```bash
git clone https://gerrit.wikimedia.org/r/p/mediawiki/extensions/WikibaseClient.git
```

3. **Install dependencies via Composer:**

```bash
cd WikibaseClient
composer install --no-dev
```

> Using Composer ensures compatibility with modern MediaWiki and PHP versions.

---

## 3️⃣ Enable Client in LocalSettings.php

```php
wfLoadExtension( 'WikibaseClient' );

// Configure the external repository
$wgWBClientSettings['clientRepoUrl'] = 'https://www.wikidata.org';
$wgWBClientSettings['clientRepoType'] = 'wikibase';
$wgWBClientSettings['entityNamespaces'] = [
    'Item' => 120,
    'Property' => 122
];
```

* **clientRepoUrl:** URL of the external Wikibase to consume data from
* **clientRepoType:** Usually `wikibase`
* **entityNamespaces:** Assign namespaces for Item and Property pages

---

## 4️⃣ Security and Best Practices

1. **Permissions:**

   * `extensions/WikibaseClient` and MediaWiki root must be readable by the web server user (`www-data`).
   * Write permissions are **not required**; Client only reads data.

2. **Cache & Performance:**

   * External data requests benefit from caching:

```php
$wgMainCacheType = CACHE_MEMCACHED;
$wgMemCachedServers = [ '127.0.0.1:11211' ];
```

3. **Use HTTPS:**

   * Always use HTTPS in `clientRepoUrl` to ensure secure data transfer.

4. **Keep Client Updated:**

   * Regularly update via Git and Composer:

```bash
git pull
composer install --no-dev
```

5. **PHP Settings:**

   * For production: `display_errors=Off`, `log_errors=On`
   * Enable Opcache for better performance

---

## 5️⃣ Testing

* Example using `#property` in a MediaWiki page:

```wiki
{{#property:P31|from=wikidata:Q5}}
```

* Example using Lua module to fetch a label:

```lua
local entity = mw.wikibase.getEntity('Q5', 'wikidata')
local label = entity:getLabel('en')
return label
```

* If the data displays correctly, the Client is installed and working properly.

---

## ✅ Summary

* **Wikibase Client** is read-only and consumes data from other Wikibase instances.
* Use **Composer** for installation and updates to ensure compatibility.
* Enable **cache**, HTTPS, and PHP best practices for performance and security.
* Test with simple `#property` or Lua module calls to verify functionality.

---

If you want, I can prepare the **next step**: a practical example for a **TIB Confident-style project**, showing how to fetch event, speaker, and venue information from Wikidata and display it on your site using Wikibase Client.

Do you want me to do that?
