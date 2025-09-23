
# 📌 Adım 1: Cradle’ı Kur

```bash
cd /var/www/mediawiki/extensions
git clone https://github.com/wikimedia/cradle.git Cradle
```

Eğer `git` yoksa:

```bash
apt install git -y
```

Sonra `LocalSettings.php` içine:

```php
wfLoadExtension( 'Cradle' );
```

---

# 📌 Adım 2: Cradle Yapılandırma

`/var/www/mediawiki/extensions/Cradle/config.json` dosyasını oluştur ve şunu koy:

```json
{
  "api": "https://mediawiki.local/w/api.php",
  "sparql": "https://mediawiki.local/query/sparql",
  "prefixes": {
    "wd": "http://mediawiki.local/entity/",
    "wds": "http://mediawiki.local/entity/statement/"
  }
}
```

👉 Burada `mediawiki.local` yerine kendi MediaWiki/Wikibase alan adını yaz.

---

# 📌 Adım 3: Kitap Formu Tanımla

Yeni bir form dosyası oluştur:
`/var/www/mediawiki/extensions/Cradle/forms/book.json`

```json
{
  "title": "Yeni Kitap Ekle",
  "description": "Bir kitap entity’si oluşturur",
  "instanceOf": "Q571", 
  "fields": [
    {
      "property": "P50",
      "label": "Yazar",
      "datatype": "wikibase-item"
    },
    {
      "property": "P212",
      "label": "ISBN",
      "datatype": "string"
    },
    {
      "property": "P577",
      "label": "Yayın Tarihi",
      "datatype": "time"
    }
  ]
}
```

### Açıklama:

* `instanceOf: Q571` → Kitap (senin Wikibase’de kitap için farklı bir Q-ID varsa onu koyman lazım).
* `P50` → Yazar (özellik ID’si senin Wikibase’de farklı olabilir).
* `P212` → ISBN
* `P577` → Yayın tarihi

👉 Eğer Wikibase’inde bu property’ler yoksa önce **Properties** oluşturarak (örneğin P50: Yazar) ID’lerini öğrenip JSON’a yazman gerekir.

---

# 📌 Adım 4: Kullanım

Artık şu URL’ye gidebilirsin:

```
https://mediawiki.local/extensions/Cradle/#/book
```

Orada form açılır → bilgileri doldur → **Kaydet** → Cradle otomatik olarak Wikibase API’sine POST atar ve yeni kitap entity’si oluşur.


# 📌 Adım 5: İsteğe Bağlı – QuickStatements ile Entegre

Cradle formunu doldurduktan sonra, girişi **QuickStatements** uyumlu formatta dışa aktarabilirsin. Böylece tek tek ya da toplu ekleme yapabilirsin.


MediaWiki’nin API uç noktası ise her zaman:

```
http://mediawiki.local/w/api.php
```

Wikibase’in **SPARQL Query Service** (WDQS / Blazegraph) uç noktası ise eğer kuruluysa:

```
http://mediawiki.local:9999/bigdata/sparql
```

(Portu ve path’i senin WDQS kurulumuna göre değişebilir — eğer henüz SPARQL kurmadıysan burayı şimdilik boş bırakabilirsin.)

---

✅ Yani senin **Cradle config.json** dosyan örnek olarak şöyle olmalı:

```json
{
  "api": "http://mediawiki.local/w/api.php",
  "sparql": "http://mediawiki.local:9999/bigdata/sparql",
  "prefixes": {
    "wd": "http://mediawiki.local/entity/",
    "wds": "http://mediawiki.local/entity/statement/"
  }
}
```

---

👉 Eğer sen SPARQL Query Service’i (WDQS) kurmadıysan, `sparql` alanını ya boş bırakabilir ya da şimdilik kendi local MediaWiki adresini koyabilirsin.

İstersen sana **WDQS (Wikidata Query Service) kurulumunu MediaWiki 1.44 + Wikibase için baştan sona** anlatayım mı?
