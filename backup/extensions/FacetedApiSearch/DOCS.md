Aşağıda **FacetQueryBuilder** yaklaşımında tarih alanlarının (P3, P4 gibi) **tamamen esnek**, **dışarıdan belirlenir**, **her sorgu için ayrı ayrı set edilir** bir mantıkla nasıl çalıştığını adım adım açıklıyorum.
Bu mantık Elasticsearch tarafı + parser + builder birleşimi için **en ideal mimaridir**.

---

# ✅ **1. Tarih Alanları Tamamen Esnektir**

Yani:

* ES tarafında **wbfs_P3**, **wbfs_P4**, **wbfs_P99** gibi alanlar olabilir.
* Sorgu sırasında **hangi tarih alanının kullanılacağı** kullanıcı tarafından belirlenir.
* FacetQueryBuilder sadece *kendisine verilen alanları* işler.

Örneğin UI’dan gelen JSON:

```json
{
  "dateFilters": {
    "P3": { "gte": "2024-01-01", "lte": "2024-12-31" },
    "P4": { "gte": "2025-01-01" }
  }
}
```

FacetQueryBuilder bunu alır ve Elasticsearch'e şu şekilde çevirir:

```json
{
  "range": { "wbfs_P3": { "gte": "2024-01-01", "lte": "2024-12-31" } }
},
{
  "range": { "wbfs_P4": { "gte": "2025-01-01" } }
}
```

### Yani:

**Hangi P property'sinin tarih olduğu tamamen esnek ve dinamik.**

---

# ✅ **2. Query Her Zaman Dışarıdan Belirlenir**

FacetQueryBuilder **hiçbir alanı bilmez**
Sadece şu formatta data alır:

```php
$builder->setDateFilters([
    'P3' => ['gte' => '2025-11-01', 'lte' => '2025-11-30'],
    'P4' => ['gte' => '2025-11-01']
]);
```

ES alan adını da şu şekilde otomatik türetir:

```
P3 → wbfs_P3
P4 → wbfs_P4
```

---

# ✅ **3. Pagination & Sort da Esnek Olarak Dışarıdan Set Edilir**

Bir UI isteği örneği:

```json
{
  "page": 2,
  "pageSize": 20,
  "sort": {
    "field": "timestamp",
    "order": "desc"
  }
}
```

FacetQueryBuilder bunu şu şekilde ES sorgusununa ekler:

### Pagination

```php
$builder->setPagination($page, $pageSize);
```

ES’e dönüşür:

```json
"from": 20,
"size": 20
```

### Sort

```php
$builder->setSort("timestamp", "desc");
```

ES’e dönüşür:

```json
"sort": [
  { "timestamp": { "order": "desc" } }
]
```

---

# ✅ **4. FacetQueryBuilder’ın Çalışma Mantığı**

## A. Builder’ın input aldığı alanlar:

* **textQuery** → "Test1"
* **entityType** → "Q2"
* **textFields** → ["wbfs_P1", "wbfs_P2"]
* **ngramFields** → ["wbfs_P1.ngram", "wbfs_P2.ngram"]
* **termFilters** → ["P5" => "Q2"]
* **dateFilters** → ["P3" => [gte/lte], "P4" => [gte/lte]]
* **aggregationFields** → ["P1", "P2", "P3", "P4", "P5"]
* **pagination** → page, pageSize
* **sort** → field, order

Bunların hepsi **tamamen dışarıdan, her sorguda serbest**

## B. Builder bunları Elasticsearch JSON’a çevirir

Örneğin “P5 = Q2” set edildiğinde:

```php
[
  "term" => [ "wbfs_P5.keyword" => "Q2" ]
]
```

Text query set edilirse:

```php
[
  "bool" => [
    "should" => [
      ["match" => ["wbfs_P1" => "Test1"]],
      ["match" => ["wbfs_P1.ngram" => "Test1"]],
      ["match" => ["wbfs_P2" => "Test1"]],
      ["match" => ["wbfs_P2.ngram" => "Test1"]]
    ]
  ]
]
```

Tarih filtreleri:

```php
[
  "range" => [
    "wbfs_P3" => [
      "gte" => "2025-11-01",
      "lte" => "2025-11-30"
    ]
  ]
]
```

---

# ✅ **5. Sonuç: API Faceted Search tamamen dışarıdan yönetilen esnek bir yapı haline geliyor**

Her sorgu için:

* Hangi facet alanları kullanılacak?
* Hangi tarih alanları kullanılacak?
* Hangi keyword alanları filtrelenecek?
* Text query var mı?
* Pagination?
* Sort?

Hepsini UI veya dış API belirliyor.

**FacetQueryBuilder sadece çeviri yapan bir arabirim haline geliyor.**

---

# İstersen şimdi →

## ✔️ FacetQueryBuilder sınıfının tam PHP implementasyonunu yazayım

## ✔️ + ApiFacetedSearch::execute içine nasıl entegre edileceğini göstereyim

Hazırım, devam edelim mi?
