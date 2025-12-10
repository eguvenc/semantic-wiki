
# 🔧 WDQS Updater Kurulumu (Wikibase 1.44 için) (Değişiklikleri İşleyen Servis)

### 1. Gereksinimler

* MediaWiki + Wikibase kurulmuş olmalı.
* Blazegraph (`bigdata.jar`) çalışıyor olmalı.
* PHP CLI ve Java yüklü olmalı.

---

### 2. RDF İhracatını Test Et

```
https://wikibase.olobase.dev/index.php/Special:EntityData/Q1.ttl
```

Eğer `ttl` dosyası açılıyorsa RDF export aktif demektir.

---

### 3. WDQS Updater Betiğini Çek

Wikidata Query Service reposunu indir:

```bash
cd /opt
git clone https://gerrit.wikimedia.org/r/wikidata/query/rdf wdqs-updater
cd wdqs-updater
```

**WDQS Updater** kısmı Blazegraph’ın kendi `bigdata.jar`’ından çıkmaz. O, ayrı bir projedir: **wikidata/query/rdf**.

Onları kendimiz **build etmemiz** gerekiyor. Aşamaları net olarak anlatayım:

---

## 🔨 WDQS Updater’ı Derleme (Docker’sız)

### 1. Bağımlılıkları Yükle

Ubuntu üzerinde:

```bash
sudo apt update
sudo apt install openjdk-17-jdk
```

> Not: WDQS upstream artık **Java 17** istiyor.

## Maven’ı Güncelle

### 1. Eski Maven’ı kaldır

```bash
sudo apt remove maven -y
```

### 2. Yeni Maven (3.9.9 gibi) indir

```bash
cd /opt
wget https://archive.apache.org/dist/maven/maven-3/3.9.9/binaries/apache-maven-3.9.9-bin.tar.gz
tar -xvzf apache-maven-3.9.9-bin.tar.gz
sudo mv apache-maven-3.9.9 /opt/maven
```

### 3. Ortam değişkenlerini ayarla

```bash
sudo vim /etc/profile.d/maven.sh
```

İçine şunu ekle:

```bash
export M2_HOME=/opt/maven
export PATH=$M2_HOME/bin:$PATH
```

Kaydet çık. Sonra:

```bash
source /etc/profile.d/maven.sh
```

### 4. Test et

```bash
mvn -v
```

Beklenen çıktı (örnek):

```
Apache Maven 3.9.9 (8e8579a9e76f7d015ee5ec7bfcdc97d260186937)
Maven home: /opt/maven
Java version: 1.8.0_462, vendor: Private Build, runtime: /usr/lib/jvm/java-8-openjdk-amd64/jre
Default locale: en_US, platform encoding: UTF-8
OS name: "linux", version: "6.8.0-71-generic", arch: "amd64", family: "unix"
```

### 1. Wdqs-Updater için kullanıcı oluştur (opsiyonel ama önerilir)

```bash
sudo useradd -r -s /bin/false wdqs-updater
```

### 2. Çalışma dizinini ayarla

```bash
sudo chown -R wdqs-updater:wdqs-updater /opt/wdqs-updater
```

---

WDQS’yi sürekli güncel tutmak için iki parça gerekiyor:

1. Updater (Consumer)

MediaWiki’den gelen RDF değişikliklerini alır → Blazegraph’a yazar. Normalde runUpdate.sh ile çalışır.

Kaynağı ya:
MediaWiki job queue (HTTP polling), ya da Kafka stream (büyük kurulumlarda). Basit kurulum için MediaWiki job queue (HTTP polling) yeterli olur.


### 3. Derleme

```bash
cd /opt/wdqs-updater
mvn clean package -DskipTests -pl tools -am
```

Bittiğinde şunlar oluşmalı:

```
wdqs-updater/tools/target/query-service-*-SNAPSHOT.jar
wdqs-updater/tools/runUpdate.sh
```

### Wdq Namespace inin Oluşturulması


Blazegraph kurulduğunda otomatik olarak wdq namespace oluşturulmaz. Blazegraph, boş bir RDF triple store sunucusudur. Kurulum sırasında sadece Blazegraph servisi gelir, fakat WDQS’nin ihtiyaç duyduğu wdq namespace’i sizin oluşturmanız gerekir.
wdq namespace’i, Wikidata Query Service (WDQS) Updater’ın verileri yazacağı özel bir namespace’tir. Bu namespace’in içinde SPARQL endpoint ve diğer ayarlar (RDF store özellikleri) yer alır.

Kısaca: Blazegraph sadece motoru sağlar, WDQS Updater için gerekli namespace’i manuel veya script ile oluşturmanız gerekir.


```bash
sudo vim /opt/wdqs-updater/RWStore.properties
```


* Paste below the items


```bash
com.bigdata.rdf.sail.truthMaintenance=false
com.bigdata.rdf.store.AbstractTripleStore.textIndex=false
com.bigdata.rdf.store.AbstractTripleStore.justify=false
com.bigdata.rdf.store.AbstractTripleStore.statementIdentifiers=false
com.bigdata.rdf.store.AbstractTripleStore.axiomsClass=com.bigdata.rdf.axioms.NoAxioms
com.bigdata.namespace.wdq.spo.com.bigdata.btree.BTree.branchingFactor=1024
com.bigdata.rdf.sail.namespace=wdq

com.bigdata.rdf.sail.lexemes=true

com.bigdata.rdf.store.AbstractTripleStore.quads=true
com.bigdata.rdf.store.AbstractTripleStore.geoSpatial=true
com.bigdata.namespace.wdq.lex.com.bigdata.btree.BTree.branchingFactor=400
com.bigdata.journal.Journal.groupCommit=false
com.bigdata.rdf.sail.isolatableIndices=true
```


```bash
com.bigdata.rdf.sail.truthMaintenance=false
com.bigdata.rdf.store.AbstractTripleStore.textIndex=false
com.bigdata.rdf.store.AbstractTripleStore.justify=false
com.bigdata.rdf.store.AbstractTripleStore.statementIdentifiers=false
com.bigdata.rdf.store.AbstractTripleStore.axiomsClass=com.bigdata.rdf.axioms.NoAxioms
com.bigdata.namespace.wdq.spo.com.bigdata.btree.BTree.branchingFactor=1024
com.bigdata.rdf.sail.namespace=wdq
com.bigdata.rdf.sail.lexemes=true
com.bigdata.rdf.store.AbstractTripleStore.quads=true
com.bigdata.rdf.store.AbstractTripleStore.geoSpatial=true
com.bigdata.namespace.wdq.lex.com.bigdata.btree.BTree.branchingFactor=400
com.bigdata.journal.Journal.groupCommit=false
com.bigdata.rdf.sail.isolatableIndices=true
```

Triples / Quads: quads seçili (RDF + SPARQL seçtiğin için otomatik quads modunda olur)
Inference: İstediğin inference yoksa boş bırakabilirsin
Isolatable indices: İşaretle ✅ (güvenli ve WDQS önerisi)
Full text index: İstersen devre dışı bırakabilirsin ❌ (WDQS için gerekli değil)
Enable geospatial: Eğer coğrafi sorgu yapmayacaksan ❌ bırak



* Kaydet ve çık.
* Wdq namespace i yaratalım.

```bash
curl -X POST   -H "Content-Type:text/plain"   --data-binary @/opt/wdqs-updater/RWStore.properties   "http://localhost:9999/bigdata/namespace"
````

* Beklenen yanıt.

CREATED.


Arayüzden 

```
http://localhost:9999/bigdata/#namespaces
```


* Use / Apply:

Namespace oluşturduktan sonra Use butonuna basmayı unutma, yoksa aktif olmayacaktır.


namespace ler içinde wdq gözükmeli. Use seçeneğine tıklayarak "wdq" namespace ini aktif et.`

<img title="In Use" alt="Acticating In Use" src="/images/blazegraph-in-use.png">

---

### 4. Blazegraph’a İlk Veriyi Yükle


Eğer Q1 ekli değilse bir tane örnek "query 1" yaratalım.

```bash
http://mediawiki.local/index.php/Special:NewItem
```

Label kısmına Q1 yazın ve kaydedin. Açıklamaya test yazabilirsiniz.

MediaWiki’den dump al:

```bash
cd /var/www/wiki/

php ./extensions/Wikibase/repo/maintenance/dumpRdf.php --format=ttl > dump.ttl
```

Blazegraph’a yükle:

```bash
curl -X POST \
  -H 'Content-Type:text/turtle' \
  --data-binary @dump.ttl \
  http://localhost:9999/bigdata/namespace/wdq/sparql
```

Beklenen çıltı:

```xml
<?xml version="1.0"?><data modified="5245" milliseconds="744"/>
```

Test edelim:


```sql
PREFIX wd: <http://mediawiki.local/entity/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT ?item ?label
WHERE {
  ?item a <http://wikiba.se/ontology#Item> ;
        rdfs:label ?label .
}
LIMIT 10
```


Beklenen Çıktı:

<table>
  <tr>
    <th>item</th>
    <th>label</th>
  </tr>

  <tr>
    <td><http://mediawiki.local/entity/Q1></td>
    <td>test</td>
  </tr>

  <tr>
    <td><http://mediawiki.local/entity/Q2></td>
    <td>Q1</td>
  </tr>
</table>


---

### 5. Updater Servisini Çalıştır (HTTP Polling)


Update path for direct executtion.


```bash
vim /opt/wdqs-updater/tools/runUpdate.sh
```

Change path


```bash
java -cp target/wikidata-query-tools-*-SNAPSHOT-jar-with-dependencies.jar ...
```

to

```bash
java -cp /opt/wdqs-updater/tools/target/wikidata-query-tools-0.3.160-SNAPSHOT-jar-with-dependencies.jar
```

Save and exit.


Create dump folder and give write permissions:

```bash
mkdir -p /opt/wdqs-updater/dumps
```

`runUpdate.sh` ile çalışır:


```bash
sh /opt/wdqs-updater/tools/runUpdate.sh \
  --wikibaseHost wikibase.olobase.dev \
  --wikibaseScheme https \
  --apiPath /api.php \
  --dumpDir /opt/wdqs-updater/dumps \
  --threadCount 10 \
  --pollDelay 10 \
  --init
```

```bash
sh /opt/wdqs-updater/tools/runUpdate.sh \
    --sparqlUrl "http://localhost:9999/bigdata/namespace/wdq/sparql" \
    --wikibaseHost wikibase.olobase.dev \
    --wikibaseScheme https \
    --apiPath /api.php \
    --dumpDir /opt/wdqs-updater/dumps \
    --threadCount 10 \
    --pollDelay 10 \
    --init
```


The options available are:
  [--apiPath value] : Path to mediawiki api.php
  [--batchSize -b value] : Number of recent changes fetched at a time.
  [--clusters -c value...] : Kafka cluster prefixes (e.g. eqiad, codfw), comma or space separated
  [--commonsUri value] : Commons concept URI for RDF entities
  [--conceptUri -U value] : Wikibase concept URI for RDF entities
  [--constraints] : Load Wikibase constraints data
  [--consumer -C value] : Set consumer ID for Kafka poller
  [--dumpDir value] : Set RDF dumping in this directory
  [--entityDataPath value] : Path to Special:EntityData
  [--entityNamespaces value] : If specified must be numerical indexes of Item and Property namespaces that defined in Wikibase repository, comma separated.
  [--help] : Show this message
  [--idrange value] : If specified must be <start>-<end>. Ids are iterated instead of recent changes. Start and end are inclusive.
  [--ids value...] : If specified must be <id> or list of <id>, comma or space separated.
  [--import-async] : Import batch asynchronously
  [--init -I] : Initialize last update time to start time
  [--kafka -K value] : If set, use Kafka polling with the argument as the broker server
  [--keepTypes] : Preserve all types
  [--labelLanguage value...] : Only import labels, aliases, and descriptions in these languages.
  [--metricDomain value] : JMX metrics domain
  [--oldRevision value] : How old (hours) should revision be to start using latest revision fetch
  [--pollDelay -d value] : Poll delay when no updates found
  [--resetKafka] : Reset Kafka offsets
  [--singleLabel value...] : Only import a single label and description using the languages specified as a fallback list. If there isn't a label in any of the specified languages then no label is imported.  Ditto for description.
  [--skipSiteLinks] : Skip site links
  [--skolemize] : Skolemization of blank nodes, expects the blank nodes to have uniquely identifiable labels
  --sparqlUrl -u value : URL to post updates and queries.
  [--start -s value] : Start time in 2015-02-11T17:11:08Z or 20150211170100 format.
  [--tailPoller -T value] : Use secondary poller with given gap (seconds) to catch up missed updates. Applies only to RecentChanges poller.
  [--threadCount -t value] : Thread count (for wikibase entity fetch)
  [--verbose -v] : Verbose mode
  [--verify -V] : Verify updates (may have performance impact)
  [--wikibaseHost -w value] : Wikibase host
  [--wikibaseScheme -S value] : Wikidata url scheme
  [--wikibaseUrl -W value] : Wikibase instance base URL



-h → Blazegraph SPARQL endpoint
-s → MediaWiki API endpoint
-d → RDF dump dosyası (başlangıç yüklemesi için)
-P → kaç thread çalışacak (10 iyi başlangıç)

Bunu bir systemd servisi yaparsan sürekli çalışır ve yeni değişiklikleri alır.


### 6. Systemd Servisi Olarak Kur

`/etc/systemd/system/wdqs-updater.service` oluştur:

```ini
[Unit]
Description=WDQS Updater Service
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/opt/wdqs-updater/tools
ExecStart=/bin/bash /opt/wdqs-updater/tools/runUpdate.sh \
  --sparqlUrl http://localhost:9999/bigdata/namespace/wdq/sparql \
  --wikibaseHost mediawiki.local \
  --dumpDir /opt/wdqs-updater/ \
  --threadCount 10 \
  --pollDelay 10
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Servisi yükle ve başlat:

```bash
systemctl daemon-reload
systemctl enable wdqs-updater
systemctl start wdqs-updater
```

---

### 7. Doğrulama

* Loglara bak:

  ```bash
  journalctl -u wdqs-updater -f
  ```
* MediaWiki’de yeni bir item oluştur.
* SPARQL’da kontrol et:

  ```
  http://localhost:9999/bigdata/#query
  ```

---

👉 Böylece WDQS Updater, MediaWiki’deki tüm değişiklikleri otomatik olarak Blazegraph’a aktarır.

---

