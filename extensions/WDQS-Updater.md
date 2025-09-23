
# 🔧 WDQS Updater Kurulumu (Wikibase 1.44 için) (Değişiklikleri İşleyen Servis)

### 1. Gereksinimler

* MediaWiki + Wikibase kurulmuş olmalı.
* Blazegraph (`bigdata.jar`) çalışıyor olmalı.
* PHP CLI ve Java yüklü olmalı.

---

### 2. RDF İhracatını Test Et

```
http://mediawiki.local/index.php/Special:EntityData/Q1.ttl
```

Eğer `ttl` dosyası açılıyorsa RDF export aktif demektir.

---

### 3. WDQS Updater Betiğini Çek

Wikidata Query Service reposunu indir:

```bash
cd /opt/wdqs-updater
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
sudo nano /etc/profile.d/maven.sh
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
Apache Maven 3.9.9
Java version: 17.0.x
```

---

### 2. Kaynak Kodunu İndir

```bash
cd /opt/
git clone https://gerrit.wikimedia.org/r/wikidata/query/rdf wdqs-updater
cd wdqs
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

MediaWiki’den gelen RDF değişikliklerini alır → Blazegraph’a yazar.

Normalde runUpdate.sh ile çalışır.

Kaynağı ya:

MediaWiki job queue (HTTP polling),

ya da Kafka stream (büyük kurulumlarda).

Senin local kurulum için HTTP polling yeterli.


### 3. Derleme

```bash
cd /opt/wdqs-updater
mvn clean package -DskipTests -pl tools -am
```

Bittiğinde şunlar oluşmalı:

```
wdqs-updater/target/query-service-*-SNAPSHOT.jar
wdqs-updater/runUpdate.sh
```

---

### 4. Scripti Çalıştırılabilir Yap

```bash
chmod +x runUpdate.sh
```

---

### 5. Çalıştırma (HTTP Polling)

```bash
./runUpdate.sh \
  -h http://localhost:9999/bigdata/namespace/wdq/sparql \
  -s http://mediawiki.local/api.php \
  -d /var/lib/wdqs/dump.ttl.gz \
  -P 10
```

-h → Blazegraph SPARQL endpoint
-s → MediaWiki API endpoint
-d → RDF dump dosyası (başlangıç yüklemesi için)
-P → kaç thread çalışacak (10 iyi başlangıç)

Bunu bir systemd servisi yaparsan sürekli çalışır ve yeni değişiklikleri alır.


---

### 4. Blazegraph’a İlk Veriyi Yükle

MediaWiki’den dump al:

```bash
php /var/www/mediawiki/extensions/Wikibase/repo/maintenance/dumpRdf.php --format=ttl > dump.ttl
```

Blazegraph’a yükle:

```bash
curl -X POST \
  -H 'Content-Type:text/turtle' \
  --data-binary @dump.ttl \
  http://localhost:9999/bigdata/namespace/kb/sparql
```

---

### 5. Updater Servisini Çalıştır

`runUpdate.sh` ile çalışır:

```bash
./runUpdate.sh \
  -h http://localhost:9999/bigdata/namespace/wdq/sparql \
  -s http://mediawiki.local/api.php \
  -d /var/lib/wdqs/dump.ttl.gz \
  -P 10
```

### 6. Systemd Servisi Olarak Kur

`/etc/systemd/system/wdqs-updater.service` oluştur:

```ini
[Unit]
Description=Wikidata Query Service Updater
After=network.target blazegraph.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/opt/wdqs
ExecStart=/opt/wdqs/runUpdate.sh -h http://localhost:9999/bigdata/namespace/wdq/sparql \
          -s http://mediawiki.local/api.php \
          -d /var/lib/wdqs/dump.ttl.gz \
          -P 10
Restart=always

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

