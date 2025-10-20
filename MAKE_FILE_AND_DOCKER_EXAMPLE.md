
## DOCKER AND MAKEFILE EXAMPLE PROJECT

Bu şekilde hem **Docker**’ı hem de **Makefile**’ı gerçekten anlayarak öğrenebilirsin — çünkü ikisi de *otomasyon aracı* ve birlikte kullanıldığında harika işler çıkarır.
Hadi adım adım ilerleyelim 👇

---

## 🎯 Hedef

Küçük ama öğretici bir proje yapacağız:

* `docker-compose.yml` ile bir **web uygulaması** (örneğin `nginx` + `php`) çalıştıracağız,
* `Makefile` ile şu işleri yöneteceğiz:

  * `make up` → container’ları başlat
  * `make down` → container’ları durdur
  * `make logs` → logları göster
  * `make rebuild` → her şeyi sıfırdan kur

---

## 📁 Proje yapısı

```
my-docker-project/
├── docker-compose.yml
├── Makefile
└── src/
    └── index.php
```

---

## 🐳 `docker-compose.yml`

```yaml
version: "3.8"

services:
  web:
    image: nginx:latest
    container_name: my_nginx
    ports:
      - "8080:80"
    volumes:
      - ./src:/usr/share/nginx/html:ro

  php:
    image: php:8.2-fpm
    container_name: my_php
    volumes:
      - ./src:/var/www/html
```

🧩 Bu ne yapıyor:

* `web` → nginx çalıştırıyor, `src/` klasöründeki dosyaları sunuyor.
* `php` → PHP-FPM çalıştırıyor (ileride backend yazarsak hazır).
* Her iki container aynı klasörü (`src/`) paylaşıyor.

---

## 📜 `Makefile`

```makefile
# Varsayılan hedef
.DEFAULT_GOAL := help

# Docker Compose komutunu kısaltalım
DC = docker compose

help:
	@echo ""
	@echo "Kullanılabilir komutlar:"
	@echo "  make up       → Container'ları başlat"
	@echo "  make down     → Container'ları durdur"
	@echo "  make logs     → Logları göster"
	@echo "  make rebuild  → Her şeyi yeniden kur"
	@echo ""

up:
	$(DC) up -d

down:
	$(DC) down

logs:
	$(DC) logs -f

rebuild: down
	$(DC) build --no-cache
	$(DC) up -d
```

🔍 Açıklama:

* `make up` → Docker Compose’ı arka planda (`-d`) başlatır
* `make down` → Tüm container’ları kapatır
* `make logs` → Logları canlı izler
* `make rebuild` → Her şeyi silip sıfırdan kurar

---

## 🧪 `src/index.php`

```php
<?php
phpinfo();
```

---

## 🚀 Kullanım

Terminalde proje klasörüne girip sırayla çalıştır:

```bash
make up
```

➡️ `nginx` ve `php` container’ları ayağa kalkar.
Tarayıcıda şu adresi aç:
👉 [http://localhost:8080](http://localhost:8080)

Sonra:

```bash
make logs
```

➡️ Nginx loglarını canlı olarak görebilirsin.

Bitirmek için:

```bash
make down
```

---

## 💡 Ne öğrendin burada?

| Öğrendiğin            | Açıklama                                         |
| --------------------- | ------------------------------------------------ |
| `docker-compose.yml`  | Servislerin nasıl tanımlandığını                 |
| `Makefile`            | Tek satırlık komutlarla Docker yönetimini        |
| `make`                | Tekrarlayan işlemleri otomatikleştirmenin gücünü |
| `DC = docker compose` | Değişken tanımlamayı ve tekrar kullanmayı        |

---

İstersen buradan bir adım öteye geçip bu projeye **veritabanı (MySQL veya PostgreSQL)** ekleyebiliriz.
O zaman `Makefile`’a `make db-shell`, `make seed`, `make migrate` gibi komutlar da ekleriz — profesyonel Docker workflow’unu tamamen öğrenmiş olursun.

👉 Devam etmek ister misin o adımla (örneğin MySQL ekleyerek)?
