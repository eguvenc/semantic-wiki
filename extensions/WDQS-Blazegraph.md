
## Wikibase Query Service (WDQS / Blazegraph)

## 1. Ön Gereksinimler

* **MediaWiki 1.44** kurulmuş ve çalışıyor
* **Wikibase 1.44** kurulmuş ve RDF export açık
* **Java 11 (OpenJDK)** kurulu
* **wget, curl** yüklü

Kontrol et:

```bash
java -version
```

Java 11 (veya 17) görmelisin.

---

## 2. Blazegraph (SPARQL Backend) Kurulumu

1. Blazegraph indir:

```bash
mkdir -p /opt/wdqs-blazegraph && cd /opt/wdqs-blazegraph
wget https://github.com/blazegraph/database/releases/download/BLAZEGRAPH_2_1_6_RC/bigdata.jar
```

2. Çalıştır:

```bash
java -server -Xmx4g -jar bigdata.jar &
```

Servis Olarak Ayarlama 

Blazegraph’ı Ubuntu’da sürekli çalışacak bir **systemd servisi** haline getirmek için şu adımları uygulayabilirsin:

### 1. Blazegraph için kullanıcı oluştur (opsiyonel ama önerilir)

```bash
sudo useradd -r -s /bin/false blazegraph
```

### 2. Çalışma dizinini ayarla

```bash
sudo chown -R blazegraph:blazegraph /opt/wdqs-blazegraph
```

(Şu an sen zaten `bigdata.jar` dosyasını `/opt/wdqs-blazegraph` içine indirmişsin.)

---

### 3. Systemd servis dosyası oluştur

Yeni bir servis tanımı aç:

```bash
vim /etc/systemd/system/blazegraph.service
```

İçine şunu yapıştır:

```ini
[Unit]
Description=Blazegraph graph database
After=network.target

[Service]
User=blazegraph
WorkingDirectory=/opt/wdqs-blazegraph
ExecStart=/usr/bin/java -server -Xmx4g -jar /opt/wdqs-blazegraph/bigdata.jar
Restart=on-failure
SuccessExitStatus=143

[Install]
WantedBy=multi-user.target
```

---

### 4. Servisi aktif et ve başlat

```bash
sudo systemctl daemon-reload
sudo systemctl enable blazegraph
sudo systemctl start blazegraph
```

---

### 5. Servis durumunu kontrol et

```bash
sudo systemctl status blazegraph
```

---

### 6. Erişim

Blazegraph varsayılan olarak `http://localhost:9999/bigdata/` adresinden çalışır.
SPARQL endpoint:

```
http://localhost:9999/bigdata/sparql
```


3. Test et:


* http://localhost:9999/bigdata/sparql  → boş bir SPARQL endpoint açılmalı.

Bu komut Blazegraph’ı `http://localhost:9999/bigdata` üzerinde başlatır.

👉 Tarayıcıda `http://mediawiki.local:9999/bigdata/` açıp kontrol edebilirsin.



## 8. Çalışan Bileşenler

✅ Blazegraph → `http://localhost:9999/bigdata/sparql`
✅ RDF Export → `Special:EntityData/Q1.ttl`
✅ Cradle extension veya başka araçlar **SPARQL + API** üzerinden çalışabilir.
✅ `Cradle config.json` için:


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

## 3. MediaWiki RDF Çıkışı Doğrulama

→ Special:NewItem sayfasından bir item (örneğin Q1) oluşturmalısınız.
URL:

http://mediawiki.local/index.php/Special:NewItem


Basitçe label girip kaydedin. Böylece Q1 diye bir entity oluşur.


Test et:

```
https://mediawiki.local/index.php/Special:EntityData/Q1.ttl
```

RDF çıktısı görmelisin (Turtle format).

@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
......

data:Q1 a schema:Dataset ;
  schema:about wd:Q1 ;
  cc:license <http://creativecommons.org/publicdomain/zero/1.0/> ;
  schema:softwareVersion "1.0.0" ;
  schema:version "16"^^xsd:integer ;
  schema:dateModified "2025-09-17T10:37:45Z"^^xsd:dateTime ;
  wikibase:statements "0"^^xsd:integer ;
  wikibase:sitelinks "0"^^xsd:integer ;
  wikibase:identifiers "0"^^xsd:integer .

wd:Q1 a wikibase:Item ;
  rdfs:label "test"@en ;
  skos:prefLabel "test"@en ;
  schema:name "test"@en ;
  schema:description "a test value for elastic search"@en .


---

## 4. WDQS Updater (Değişiklikleri İşleyen Servis)

👉 Wikidata canlı değişiklikleri RDF’ye işlemek için WDQS Updater kurulması gerekir bunun →  için WDQS-Updater.md dosyasına bak.


---

## 8. Çalışan Bileşenler

✅ Blazegraph → `http://localhost:9999/bigdata/sparql`
✅ RDF Export → `Special:EntityData/Q1.ttl`
✅ Cradle extension veya başka araçlar **SPARQL + API** üzerinden çalışabilir.
✅ `Cradle config.json` için:


---

👉 Şimdi sana bir soru: Sen bu sistemi **tek sunucuda (hepsi bir arada)** mı kurmak istiyorsun, yoksa **MediaWiki ayrı, WDQS ayrı sunucu** şeklinde mi planlıyorsun? Ona göre sana `systemd service` dosyaları da yazabilirim.
