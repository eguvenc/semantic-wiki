
## PageForms with Wikibase

---

## 1️⃣ Requirements

* MediaWiki 1.39 (your version)
* PHP ≥ 8.1 (recommended for MediaWiki 1.39)
* Wikibase extension installed
* Composer (optional, for dependencies)

---

## 2️⃣ Download the PageForms Extension

Go to your MediaWiki extensions directory:

```bash
cd /var/www/mediawiki/extensions
```

Clone the repository:

```bash
git clone -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-PageForms.git extensions/PageForms
```

> The `REL1_39` tag is compatible with MediaWiki 1.39.

Update composer

```bash
cd /var/www/mediawiki/

composer update
```

---

## 3️⃣ Update `LocalSettings.php`

Open your `LocalSettings.php` file and add:

```php
# PageForms Extension
wfLoadExtension( 'PageForms' );

# If you are using Wikibase, additional settings may be needed
# e.g., to use forms for Wikibase entities and items
```

PageForms allows you to create forms for editing Entity pages in Wikibase. Example:

```php
# Example form: Item form
# This form makes it easy to fill out Item pages in Wikibase
```

---

## 4️⃣ Update the Database

Run the following command to create the necessary database tables:

```bash
php maintenance/update.php
```

---

⚠️ **Tips / Notes:**

* PageForms + Wikibase can be tricky for certain fields such as **Property types**, **Item references**, or **Qualifiers**.
* Enable debug mode to check PHP errors if needed.
* Official documentation: [https://www.mediawiki.org/wiki/Extension:PageForms](https://www.mediawiki.org/wiki/Extension:PageForms)


### 1.2. Install ExternalData Extension (Optional)

You can use the ExternalData extension to display Wikibase data in form fields.

1. **Download and Place the Extension**:

	```bash
	git clone -b REL1_39 https://github.com/wikimedia/mediawiki-extensions-ExternalData.git extensions/ExternalData
	```

2. **Enable in `LocalSettings.php`**:

   ```php
   # ExternalData Extension
   # 
   wfLoadExtension( 'ExternalData' );
   ```

3. **Configure the Wikibase API Endpoint**:

   ```php
   $wgEDWikibaseEndpoint = 'https://www.wikidata.org/w/api.php';
   ```
---

Update composer

```bash
cd /var/www/mediawiki/

composer update
```

```bash
php maintenance/update.php
```

## 🧩 2. Integrating PageForms with Wikibase

* **Important:** PageForms cannot directly edit Wikibase items.
* However, with **ExternalData**, you can display Wikibase values in form fields.

**Example: Populate a dropdown field from a Wikibase property:**

```wikitext
{{{field|property_P31|input type=dropdown|values from external data|external data source=wikibase|wikibase property=P31}}}
```

---

## 🧪 4. Test and Troubleshoot

1. **Verify Installation**:

   * Visit [Special:Version](https://www.mediawiki.org/wiki/Special:Version) to confirm that PageForms and ExternalData are installed.

2. **Troubleshooting**:

   * Check the [PageForms Talk Page](https://www.mediawiki.org/wiki/Extension_talk:Page_Forms) and [ExternalData Talk Page](https://www.mediawiki.org/wiki/Extension_talk:ExternalData) for known issues and solutions.

---

## 💡 Extra Tips

* **Form Customization:** PageForms allows you to customize field types, display, and functionality. See [Defining Forms](https://www.mediawiki.org/wiki/Extension:Page_Forms/Defining_forms) for details.
* **Wikibase Data Synchronization:** PageForms cannot directly synchronize with Wikibase.

  * You can, however, use Lua modules or bots to push form data to Wikibase via the [Wikibase API](https://www.mediawiki.org/wiki/Wikibase/API).

---

If you want, I can also create a **ready-to-use example** for an Event form that pulls Wikibase data using ExternalData and PageForms.

Do you want me to do that next ?