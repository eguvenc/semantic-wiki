
## Semantic Result Formats


https://www.semantic-mediawiki.org/wiki/Extension:Semantic_Result_Formats/Installation

Semantic Result Formats, SMW allows you to display query results with visuals such as tables, graphs, maps, timelines, etc.

| Format                             | Açıklama                                                                   |
| ---------------------------------- | -------------------------------------------------------------------------- |
| `table`                            | HTML tablo                                                                 |
| `list`                             | Liste                                                                      |
| `timeline`                         | Etkinlikleri zamana göre gösterir (Google Timeline veya Simile Timeline)   |
| `graph`                            | RDF ilişkileri görselleştirir (force-directed graph)                       |
| `calendar`                         | Tarih verilerini takvim olarak gösterir                                    |
| `sparkline`                        | Küçük grafikler                                                            |
| `gallery`                          | Görsel galerisi                                                            |
| `map`                              | Coğrafi koordinatları haritada gösterir (OpenLayers, Leaflet, Google Maps) |
| `tagcloud`                         | Etiket bulutu oluşturur                                                    |
| `treemap`, `piechart`, `bar chart` | Görsel istatistikler                                                       |
| `eventline`, `eventcalendar`       | Zaman serileri ve etkinlik odaklı görseller                                |

Here’s a **step-by-step installation guide** for **Semantic Result Formats (SRF)** designed specifically for your setup — **MediaWiki 1.39.15** with **Semantic MediaWiki (SMW) 4.2**.

This guide follows the official documentation but is optimized for your MediaWiki version and typical Composer setup.


**For MediaWiki 1.39.15 + Semantic MediaWiki 4.2**

---

## 1. Prerequisites

Before installing SRF, make sure the following requirements are met:

* ✅ **MediaWiki 1.39.15** installed and working.
* ✅ **Semantic MediaWiki (SMW) 4.2** already installed and enabled.
* ✅ **Composer** is available (globally or as `composer.phar` in your MediaWiki root).
* ✅ You have command-line access and write permissions for `vendor/`, `extensions/`, and `LocalSettings.php`.

---

## 2. Installation Steps

Go to your MediaWiki installation directory 

```bash
cd /var/www/mediawiki
```

Add SRF to your composer configuration  


If you have a local Composer config file (recommended for MediaWiki setups):

```bash
COMPOSER=composer.local.json composer require --no-update mediawiki/semantic-result-formats "~4.2"
```

Install the dependency  

```bash
COMPOSER=composer.local.json composer update --no-dev
```

Enable the extension in MediaWiki

Edit your `LocalSettings.php` and **add this line after SMW is loaded**:

```php
wfLoadExtension( 'SemanticResultFormats' );
```

---

## 3. Verify Installation

After installation:

1. Visit **`Special:Version`** in your wiki.
   You should see **Semantic Result Formats** listed under installed extensions.
2. If SRF doesn’t appear, clear cache and run:

   ```bash
   php maintenance/update.php
   ```

---

## 4. Optional Configuration

By default, SRF activates a set of common result formats such as:

```
calendar, eventcalendar, timeline, vcard, bibtex, outline, gallery,
sum, average, min, max, median, tagcloud, tree, jqplotchart
```

You can manually specify which formats should be available using `$srfgFormats` in `LocalSettings.php`.

### Example — Restrict formats:

```php
$srfgFormats = [ 'calendar', 'timeline', 'eventcalendar' ];
```

### Example — Add additional format:

```php
$srfgFormats[] = 'googlebar';
```

---

## 5. Format Dependencies

Some formats require additional MediaWiki extensions or libraries.
Here are the most common dependencies:

| Format                   | Requires                                                              |
| ------------------------ | --------------------------------------------------------------------- |
| `array`                  | [Arrays extension](https://www.mediawiki.org/wiki/Extension:Arrays)   |
| `gantt`                  | [Mermaid extension](https://www.mediawiki.org/wiki/Extension:Mermaid) |
| `graph`, `process`       | GraphViz installed on the server                                      |
| `spreadsheet`            | [PHPSpreadsheet library](https://phpspreadsheet.readthedocs.io/)      |
| `googlebar`, `googlepie` | Sends data to Google servers – consider privacy implications          |

---

## 6. Testing SRF

You can test SRF by creating a page with the following content:

```wikitext
{{#ask:
 [[Category:Example]]
 | ?Has date
 | ?Has event
 | format=timeline
}}
```

If the **timeline** appears, SRF is working correctly.

---

## 7. Troubleshooting

| Problem                           | Possible Fix                                                                        |
| --------------------------------- | ----------------------------------------------------------------------------------- |
| SRF not listed in Special:Version | Check `wfLoadExtension('SemanticResultFormats');` placement (must come *after* SMW) |
| Composer dependency conflict      | Run `composer why mediawiki/semantic-result-formats` to check version mismatch      |
| PHP error on load                 | Ensure your PHP version is ≥ 7.4 (recommended 8.0+)                                 |
| Some formats missing              | Make sure `$srfgFormats` is not restricting available formats too much              |

---

## ✅ Summary

| Component               | Version                                       |
| ----------------------- | --------------------------------------------- |
| MediaWiki               | **1.39.15**                                   |
| Semantic MediaWiki      | **4.2**                                       |
| Semantic Result Formats | **4.2**                                       |
| Installation Method     | Composer (`composer.local.json` recommended)  |
| Load Command            | `wfLoadExtension( 'SemanticResultFormats' );` |

---

Would you like me to extend this guide with **an example `composer.local.json`** file tailored for MediaWiki 1.39 + SMW + SRF (so you can copy-paste it directly)?
