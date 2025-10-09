
What is Wiki Client ?

**Wikibase Client** is essentially a **“Wikibase data consumer”** extension. That is, it is used if you don’t have your own Wikibase Repository or if you want to fetch data from another Wikibase. More specifically, it can be explained as follows:

## 1️⃣ Core Function

* **Fetching data from other Wikibases:**
  If your project needs to integrate with a central Wikibase (e.g., Wikidata or another academic data repository), the Client handles this.

* **Displaying Items and Properties:**
  You can pull an entity that is not in your Repository via the Client and display it on your site.

* **Read-focused:**
  The Client mainly **consumes data**. Writing or editing permissions are generally limited. The Repository is the “main” part where you have full write access.

## 2️⃣ Use Cases

1. **Wikidata integration:**
   If your project, like “TIB Confident Event,” wants to fetch entities from Wikidata.

2. **Mirror data display:**
   If your main database resides in another Wikibase, you can use the Client to show that data on your MediaWiki pages.

3. **Fetching data via SPARQL queries:**
   The Client works with WDQS SPARQL endpoints and can render external Wikibase data on your pages.

---

## 3️⃣ Summary

| Feature                          | Repository | Client           |
| -------------------------------- | ---------- | ---------------- |
| Data writing                     | ✅          | ❌ / limited      |
| Data reading                     | ✅          | ✅                |
| Integration with other Wikibases | ❌          | ✅                |
| SPARQL queries                   | ✅          | ✅ (read-focused) |

💡 **In short:**

* **Repository** = your own database, where you can write and manage data.
* **Client** = used to fetch and display data from other Wikibases.


Great! Let’s go through a practical example of using **Wikibase Client** to fetch an entity from another Wikibase. I’ll keep it simple and conceptual so you can adapt it to your TIB Confident project.

---

## Example: Fetching an Entity from Wikidata

### 1️⃣ Setup

1. Make sure you have **Wikibase Client** installed in your MediaWiki instance.
2. You need to define the external Wikibase repository. In your `LocalSettings.php`:

```php
$wgWBClientSettings['repositories'] = [
    'wikidata' => [
        'name' => 'Wikidata',
        'url' => 'https://www.wikidata.org',
        'repoUrl' => 'https://www.wikidata.org/wiki/Special:EntityData/',
    ],
];
```

> This tells the Client that “wikidata” is an external repository we can fetch data from.

---

### 2️⃣ Fetching an Entity

Let’s say you want to display the entity **Q42 (Douglas Adams)** from Wikidata.

**Example in a wiki page using parser function:**

```wikitext
{{#askentity: Q42 | repository=wikidata }}
```

Or, if using **Scribunto/Lua**:

```lua
local client = require('WikibaseClient')
local entity = client.getEntity('Q42', 'wikidata')

-- Now you can access entity data
local label = entity.labels['en'].value
local description = entity.descriptions['en'].value

return string.format("Entity: %s - %s", label, description)
```

---

### 3️⃣ Using SPARQL Queries

You can also fetch data via **WDQS SPARQL endpoint**:

```php
$endpoint = 'https://query.wikidata.org/sparql';
$query = <<<SPARQL
SELECT ?item ?itemLabel WHERE {
  ?item wdt:P31 wd:Q5.
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
} LIMIT 5
SPARQL;

$results = \Wikibase\SPARQL\Client::query($endpoint, $query);

foreach ($results as $row) {
    echo $row['itemLabel'] . "\n";
}
```

> This example fetches 5 human (`wd:Q5`) entities from Wikidata and prints their labels.

---

### ✅ Summary

* **Client fetches entities** from other Wikibases using repository configuration.
* Can fetch **specific entities** or **bulk data via SPARQL**.
* Mostly **read-only**, perfect for showing external data on your pages.

--------------------------------

TIB EVENTS.

Perfect! Let’s create a TIB Confident–specific Wikibase Client example. The goal: fetch an Event entity from an external Wikibase and display it in your MediaWiki pages.


Perfect! Let’s create a **TIB Confident–specific Wikibase Client example**. The goal: fetch an **Event entity** from an external Wikibase and display it in your MediaWiki pages.

---

## 1️⃣ Define the External Repository

In your `LocalSettings.php`, configure the external Wikibase:

```php
$wgWBClientSettings['repositories'] = [
    'externalWikibase' => [
        'name' => 'External TIB Repository',
        'url' => 'https://external-tib-wikibase.org',
        'repoUrl' => 'https://external-tib-wikibase.org/wiki/Special:EntityData/',
    ],
];
```

* `externalWikibase` is an identifier for your client to reference the repository.
* `url` is the base URL of the external Wikibase.
* `repoUrl` is used to fetch entity data in JSON form.

---

## 2️⃣ Fetch a TIB Confident Event Entity

Assume an Event entity has the ID `E123` in the external Wikibase.

**Using parser function in a wiki page:**

```wikitext
{{#askentity: E123 | repository=externalWikibase }}
```

This will pull the entity from the external Wikibase and render its basic data.

---

**Using Lua/Scribunto for more control:**

```lua
local client = require('WikibaseClient')
local eventEntity = client.getEntity('E123', 'externalWikibase')

-- Access labels and descriptions in English
local label = eventEntity.labels['en'] and eventEntity.labels['en'].value or 'No label'
local description = eventEntity.descriptions['en'] and eventEntity.descriptions['en'].value or 'No description'

-- Access statements (e.g., event date or location)
local statements = eventEntity.claims or {}
local date = statements['P123'] and statements['P123'][1].mainsnak.datavalue.value or 'No date'
local location = statements['P456'] and statements['P456'][1].mainsnak.datavalue.value or 'No location'

return string.format("Event: %s\nDescription: %s\nDate: %s\nLocation: %s", label, description, date, location)
```

> Replace `P123` with the **property ID for event date** and `P456` with **location** as defined in the external Wikibase.

---

## 3️⃣ Using SPARQL for Multiple Events

You can fetch all upcoming events from the external Wikibase SPARQL endpoint:

```php
$endpoint = 'https://external-tib-wikibase.org/query/sparql';
$query = <<<SPARQL
SELECT ?event ?eventLabel ?date ?locationLabel WHERE {
  ?event wdt:P789 wd:QEventType.  # Replace QEventType with the event type entity
  ?event wdt:P123 ?date.          # Event date property
  ?event wdt:P456 ?location.      # Event location property
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
} ORDER BY ?date
SPARQL;

$results = \Wikibase\SPARQL\Client::query($endpoint, $query);

foreach ($results as $row) {
    echo $row['eventLabel'] . " - " . $row['date'] . " @ " . $row['locationLabel'] . "\n";
}
```

> This will print a list of all upcoming events, with their date and location.

---

### ✅ Key Points

* **Client fetches external entities** without modifying them.
* You can **display single entities** or **lists via SPARQL**.
* Great for TIB Confident if you want to **mirror or aggregate event data** from other Wikibases.
* Can be combined with **templates or Lua modules** to create rich, user-friendly displays.

---

If you like, I can also create a **ready-to-use MediaWiki template** for TIB Confident events that automatically fetches data from the external Wikibase and displays it nicely on your wiki pages.

Do you want me to do that next?
