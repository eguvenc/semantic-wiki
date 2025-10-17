
Wikimedia 1.45.0 ile Wikibase üzerinde bir event formu oluşturmak isitiyorum. Aşağıdaki alanlar olacak. Event modülü için daha sonra arama ve filtreleme yapılabilecek ve Event lar listelenebilecek.

Bu yapı için Wikimedia 1.45.0-alpha ve wikibase sürümünü zaten kurdum. 

İşte form detayları:

----------------------
CREATE EVENT:

Acronym:  TEXT FIELD
(If commonly used, enter the acronym of the event. Usually the event's year is used in combination with the acronym.)

Title:  TEXT FIELD
(Enter the complete office title of the event)

Ordinal:  TEXT FIELD
(E.g. 1 for a new conference, i. e. the first conference in a series. Use only numbers without dots or 'st', 'nd', 'rd' etc.)

Event Series:  AUTO COMPLETE AREA
(Enter the name of the event series this event belongs to and select the correct one from the suggestions. If you receive no suggestions after entering the event series name, it was probably not yet captured in ConfIDent. In this case please enter the event series here first. For a more detailed definition of the term event series see https://tibhannover.github.io/ConfIDent_schema/EventSeries/

An academic event series describes the set of academic events which take place on a regular basis and thus have an established common identity. This identity is constituted, for example, through institutional continuity in the hosting of a series (e.g. by a specialised society), thematic focuses and/or a common label under which a series is defined (particularly name and acronym). Nevertheless, it is possible that each of these criteria may change over time.


Single Day Event: RADIO BUTTON  yes -  no
Choose 'yes' if the event only lasts for one day (or less). If the event spans multiple days choose 'no'.

Start Date:  DATE (DD.MM.YYYY) 
You can either enter the exact date or only select a year if for example the exact date is not yet known.


End Date:  (DD.MM.YYYY)
You can either enter the exact date or only select a year if for example the exact date is not yet known.


Daha önce bana böyle cevap vermiştin. Aşağdaıki bilgiler ile birlikte tekrar bir kurulum talimatı oluşturumusun. Neleri kurmalıyım ve event modülü ve formunu oluşturmak için
adım adım neler yapmalıyım. Hangi extension ları kullanmalıyım ?

----------------------


## Example EVENT

Wikibase üzerinde böyle bir yapı oluşturmak, aslında tam olarak **Wikibase'in amaçlandığı veri modelleme ve yapısal veri girişi** sürecidir. PageForms kullanmadan form oluşturma isteğiniz de dikkate alındığında, en doğru yaklaşım, bahsettiğiniz gibi **`Special:CreateClass`** özelliğini temel alan, yani **"EntitySchema"** (Varlık Şeması) tabanlı bir yaklaşım olacaktır.

Wikibase'de PageForms'a bir alternatif olarak, **Wikimedia'nın kullandığı** standart, yerel veri giriş formlarını kullanmanızı sağlayacak olan yöntem budur.

-----

## Kurulum ve Yapılandırma Talimatları (Wikimedia 1.45.0-alpha ve Wikibase)

### Adım 1: Gerekli Uzantıları Kontrol Etme

Event verilerini daha sonra listelemek ve aramak için en iyi pratik, **Wikibase Query Service** kullanmaktır. Bu hizmet, karmaşık SPARQL sorgularını çalıştırmanıza olanak tanır.

| Uzantı | Amaç | Durum (Wikibase Kurulumunda) |
| :--- | :--- | :--- |
| **Wikibase Repository** | Temel veri depolama (Öğeler ve Özellikler). | **Kurulu (Zaten var)** |
| **EntitySchema** (`Special:NewEntitySchema`) | Veri giriş formunu yöneten şemaları tanımlamak. | **Büyük olasılıkla kurulu (Core'da)** |
| **Wikibase Query Service** | Gelişmiş arama/filtreleme ve listeleyen sorgular için. | **Harici bir hizmet/kurulum gerektirir (önerilir)** |

**Öneri:** Eğer sadece basit başlık araması yeterliyse, **CirrusSearch** yeterli olabilir. Gelişmiş yapısal arama için **Wikibase Query Service** veya bir üçüncü taraf sorgulama uzantısı kurmalısınız.

### Adım 2: Özellikleri (Properties) Tanımlama

Bu, formunuzun temelini oluşturur. Her bir alan için uygun veri tipinde bir Wikibase özelliği oluşturulmalıdır.

| Form Alanı | Önerilen Özellik Etiketi | Veri Türü | Notlar |
| :--- | :--- | :--- | :--- |
| **Acronym** | Etkinlik Kısaltması | `Monolingual text` | Kısa metinler için. |
| **Title** | Resmi Etkinlik Başlığı | `Monolingual text` | Tam başlıklar için. |
| **Ordinal** | Sıra Numarası | `Number` | Sadece sayısal değer. |
| **Event Series** | Etkinlik Serisinin Parçası | `Item` | Otomatik tamamlama için başka bir Öğe'ye (Item) bağlantı. |
| **Single Day Event** | Tek Günlük Etkinlik | `Boolean` | Evet/Hayır (true/false) için. |
| **Start Date** | Başlangıç Tarihi | `Time` | Tarih verisi için (yıl, ay, gün saklayabilir). |
| **End Date** | Bitiş Tarihi | `Time` | Tarih verisi için. |


**Yapılacaklar:**

1.  Wikinizde `Special:CreateProperty` sayfasına gidin.
2.  Yukarıdaki listedeki 7 özelliği uygun **Veri Türü** ile oluşturun.
      * *Örn: "Etkinlik Serisinin Parçası" özelliğini oluştururken veri tipini **Item** olarak seçin.*
3.  Oluşturulan özelliklerin ID'lerini (örneğin, P1, P2, P3,...) bir yere not edin.

### Adım 3: Event Serisi (Event Series) için Bir Şema Oluşturma (Ön Koşul)

**Event Series** (Etkinlik Serisi), başka bir Öğe'ye (Item) bağlanacağı için, bu seriler için de bir şema tanımlamak faydalı olacaktır (ancak zorunlu değildir).

1.  **Etkinlik Serisi Özelliği:** Eğer yoksa, bir "Etkinlik Serisinin Başlığı" (`Monolingual text`) ve "Resmi Web Sitesi" (`Url`) gibi özellikler oluşturun.
2.  **Etkinlik Serisi Şeması:** `Special:NewEntitySchema` sayfasına giderek bu özelliklerin zorunlu olduğu bir **Event Series Şeması** oluşturun ve kaydedin (Örn: E1).


### Aşama 4: Şema Eklentisini Etkinleştirme (`Special:NewEntitySchema`)

1.  **PHP Bağımlılıklarını Kurun (Sadece Git'ten indirdiyseniz):**

	EntitySchema eklentisini kurun. (https://www.mediawiki.org/wiki/Extension:EntitySchema) . Uzantıyı Git ile klonladığınız için, uzantı dizininde PHP bağımlılıklarını kurmak için `composer` kullanmanız gerekir.

      * EntitySchema dizinine gidin:
        ```bash
        cd extensions/
		git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/EntitySchema
        git checkout REL1_38
        ```
      * Composer'ı çalıştırın:
        ```bash
        composer install --no-dev
        ```
        (Eğer sunucunuzda `composer` kurulu değilse, kurmanız veya farklı bir ortamda bağımlılıkları indirip buraya taşımanız gerekebilir.)

2.  **`LocalSettings.php`'yi Düzenleyin:**

    MediaWiki kurulumunuzun ana dizininde bulunan `LocalSettings.php` dosyasının en altına aşağıdaki satırı ekleyerek uzantıyı etkinleştirin:

    ```php
    wfLoadExtension( 'EntitySchema' );
    ```

    Update settings.

	```bash
	php maintenance/update.php
	``

3.  **Gerekli Yapılandırmayı Yapın (İsteğe Bağlı):**

    EntitySchema uzantısı, Şema doğrulama için harici bir araç kullanabilir. Bu özelliği kullanmak istiyorsanız, `LocalSettings.php` dosyanıza aşağıdaki gibi bir ayar eklemeniz gerekebilir. Bu, genellikle bir [ShEx2 - Simple Online Validator](https://www.google.com/search?q=https://www.mediawiki.org/wiki/Extension:EntitySchema%23Configuration) bağlantısıdır:

    ```php
    $wgEntitySchemaShExSimpleUrl = 'HARİCİ_DOĞRULAYICI_URLSİ'; // Örn: https://tools.wmflabs.org/shex-simple/wikidata/doc/shex-simple.html
    ```

4.  **Kurulumu Kontrol Edin:**

      * Wiki'nizde `Special:Version` sayfasına giderek **Installed extensions** (Kurulu uzantılar) listesinde **EntitySchema** uzantısının görünüp görünmediğini kontrol edin.

Bu adımlar EntitySchema uzantısının MediaWiki (ve dolayısıyla Wikibase) kurulumunuzda etkinleştirilmesini sağlayacaktır.


### Adım 5: Event (Etkinlik) Şemasını Oluşturma (`Special:NewEntitySchema`)

Harika bir karar. MediaWiki 1.39.9 (LTS sürümü) ve Wikibase, daha kararlı ve iyi bilinen bir ortam sunar. Sizin için hem form oluşturmayı hem de daha sonraki arama/listeleme işlevlerini kapsayan, **MediaWiki 1.39.9** sürümüne özel güncellenmiş ve adım adım kurulum talimatlarını hazırladım.

MediaWiki 1.39.9 sürümünde **EntitySchema** (Varlık Şeması) hala ayrı bir uzantı olarak kullanılıyor olabilir (bazı kurulumlarda), ancak Wikibase'in temelini oluşturur.

-----

## MediaWiki 1.39.9 & Wikibase ile Event Formu Kurulumu

Bu süreç, önceki planınızla aynı mantığı izleyecektir: Özellikler oluşturma, şema tanımlama ve şemayı kullanarak veri girişi yapma.

### Aşama 1: Zorunlu Uzantıları Kontrol Etme/Kurma

MediaWiki 1.39 sürümünde, Wikibase ile birlikte formlama için kullanacağımız temel uzantıların doğru çalıştığından emin olmalıyız.

1.  **Wikibase Repository / Client:** Zaten kurulu olmalı.
2.  **EntitySchema:** Bu uzantı, `Special:NewEntitySchema` sayfalarını sağlar ve veri formunuzun yapısını tanımlar.
      * **Kontrol:** Wiki'nizde `Special:Version` sayfasına giderek bu uzantının listelenip listelenmediğini kontrol edin.
      * **Kurulum (Gerekliyse):** Eğer EntitySchema kurulu değilse, ilgili versiyonunu klonlayıp `LocalSettings.php` dosyasına `wfLoadExtension( 'EntitySchema' );` eklemeniz ve composer bağımlılıklarını çalıştırmanız gerekebilir:
        ```bash
        # EntitySchema dizininde
        composer install --no-dev
        ```

### Aşama 2: Tüm Özellikleri (Properties) Tanımlama

Formdaki her alan için gerekli olan Wikibase Özelliklerini oluşturun.

| Form Alanı | Önerilen Etiket | Veri Türü | Notlar |
| :--- | :--- | :--- | :--- |
| **Acronym** | Etkinlik Kısaltması | `Monolingual text` | **P1** olarak varsayalım. |
| **Title** | Resmi Etkinlik Başlığı | `Monolingual text` | **P2** olarak varsayalım. |
| **Ordinal** | Sıra Numarası | `Number` | **P3** olarak varsayalım. |
| **Event Series** | Etkinlik Serisinin Parçası | `Item` | Otomatik tamamlama sağlar. **P4** olarak varsayalım. |
| **Single Day Event** | Tek Günlük Etkinlik | `Boolean` | Evet/Hayır için. **P5** olarak varsayalım. |
| **Start Date** | Başlangıç Tarihi | `Time` | **P6** olarak varsayalım. |
| **End Date** | Bitiş Tarihi | `Time` | **P7** olarak varsayalım. |

**Yapılacaklar:**

1.  `Special:CreateProperty` sayfasına gidin.
2.  Yukarıdaki listedeki kalan tüm özellikleri (P3, P4, P5, P6, P7) uygun **Veri Türü** ile oluşturun.
3.  Oluşturulan tüm P-ID'leri (P1'den P7'ye kadar) bir yere not edin.

### Aşama 3: EntitySchema (EventForm) Oluşturma

Bu adım, veri giriş formunuzun kurallarını tanımlar ve önceki denemede yaptığınız şemayı genişletiriz.

1.  `Special:NewEntitySchema` sayfasına gidin.
2.  Şema Etiketi olarak "Event Formu" veya "Etkinlik" gibi bir isim girin.
3.  Aşağıdaki **Genişletilmiş ShEx Kodunu** kopyalayıp yapıştırın.

**Genişletilmiş ShEx Kodu (Lütfen P-ID'leri Kendi Gerçek ID'lerinizle Değiştirin):**

```shex
PREFIX : <http://www.wikidata.org/entity/>
PREFIX p: <http://www.wikidata.org/prop/>
PREFIX ps: <http://www.wikidata.org/prop/statement/>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

# Etkinlik (Event) Şekli
:EventShape CLOSED {
    
    # Acronym: TEXT FIELD (P1) - Zorunlu
    p:P1 [ xsd:string ] @tr ;
    
    # Title: TEXT FIELD (P2) - Zorunlu
    p:P2 [ xsd:string ] @tr ;
    
    # Ordinal: TEXT FIELD (P3) - Zorunlu (Sayıl olması beklenir, burada sayısal veri tipiyle uyumlu olur)
    p:P3 . ;
    
    # Event Series: AUTO COMPLETE AREA (P4) - Zorunlu (Bir Item'a bağlanır)
    p:P4 . ; 
    
    # Single Day Event: RADIO BUTTON (P5) - Zorunlu (Boolean)
    p:P5 . ;
    
    # Start Date: DATE (P6) - Zorunlu (Time)
    p:P6 . ;
    
    # End Date: DATE (P7) - İsteğe Bağlı (Time)
    p:P7 .? # Soru işareti (?) bu alanın isteğe bağlı olduğunu belirtir.

}
```

4.  Şemayı kaydedin ve atanan ID'yi not edin (Örn: **E1** veya **E2**).

### Aşama 4: Veri Girişini Başlatma (Formun Kullanımı)

Artık bu şemayı, yeni bir etkinlik Ögesi (Item) oluştururken form olarak kullanabilirsiniz.

1.  `Special:NewItem` sayfasından yeni bir **Etkinlik Ögesi** oluşturun (Örn: Q5).
2.  Yeni Öğe sayfasına gidin.
3.  Sayfanın altındaki (veya arayüze göre yan menüdeki) **"Kullanılan Varlık Şemaları"** (Used Entity Schemas) alanına gidin.
4.  Buraya oluşturduğunuz Şema ID'sini (**E1**) girin ve kaydedin.
5.  Öğe sayfasını tekrar açtığınızda, artık tüm beyanlar (statements) bölümü, **E1** şemasındaki kurallara göre şekillendirilmiş bir **veri giriş formu** gibi görünecektir. Zorunlu alanları doldurmanız beklenecek ve alanlar doğru veri tipine göre (tarih, sayı, otomatik tamamlama) biçimlendirilecektir.

-----

## Aşama 5: Arama ve Filtreleme Modülünü Oluşturma (Listeleme)

Arama ve listeleme, doğrudan bir "modül" kurmak yerine, **Wikibase Query Service** (WBQS) kullanılarak yapılır.

### Çözüm: Wikibase Query Service Kurulumu (Önerilir)

En iyi arama ve listeleme için, ayrı bir sunucuda veya kapsayıcıda [Wikibase Query Service](https://www.google.com/search?q=https://www.mediawiki.org/wiki/Wikidata/Wikibase_Query_Service) kurmanız gerekir. Bu hizmet, karmaşık SPARQL sorgularını çalıştırmanıza olanak tanır.

Kurulumdan sonra:

1.  **SPARQL Sorgusu Hazırlama:** Kullanıcıların istedikleri filtrelemeyi (örneğin yıla göre, seriye göre) yapabileceği SPARQL sorgularını hazırlayın. (Bkz. önceki cevaplardaki örnek sorgu.)
2.  **Özel Arama Sayfası:** Bu sorguları çalıştıran ve sonuçları tablo halinde gösteren bir MediaWiki uzantısı (Örn: `Special:EventList`) yazmanız, 1.39 sürümü için en gelişmiş çözümdür.
      * Bu, özel bir PHP uzantısı yazarak WBQS API'sinden veri çeken ve bunu Wiki sayfasında bir tabloda listeleyen bir `SpecialPage` oluşturmayı içerir.

### Basit Alternatif: CirrusSearch

Eğer CirrusSearch (Elasticsearch) kuruluysa:

1.  **Indexleme:** Wikibase verileriniz (P1, P2 gibi) varsayılan olarak indekslenecektir.
2.  **Arama:** Kullanıcılar, standart arama kutusuna "2024 Konferansı" gibi başlık veya kısaltma parçaları yazarak ilgili etkinlikleri arayabilirler.
3.  **Filtreleme:** `has:P4` (Etkinlik Serisine sahip olanlar) veya `P6:2024` (Başlangıç tarihi 2024 olanlar) gibi gelişmiş CirrusSearch filtrelerini standart arama çubuğunda kullanarak basit yapısal filtreleme yapabilirsiniz.


-----

## 2\. Aşama: Event Search Özelliğini Ekleme

Formunuz hazır olup verileriniz girilmeye başlandıktan sonra arama özelliğine geçebilirsiniz:

### Arama için Çözüm 1: Wikibase Query Service

Bu, Wikibase'deki yapılandırılmış verileri aramak için en güçlü ve standart yöntemdir.

1.  **SPARQL ile Sorgulama:** Kullanıcılarınız için hazır **SPARQL** sorguları oluşturarak (örneğin, "Başlangıç tarihi 2024 olan tüm etkinlikleri getir") bu sorgu sonuçlarını bir tabloda listeleyebilirsiniz.
2.  **Özel Sayfa:** Bu sorguları çalıştıran ve sonuçları gösteren bir `Special:EventSearch` sayfası oluşturmak için, bir MediaWiki uzantısı yazmanız (veya mevcut Query Service istemcisini kullanmanız) gerekebilir.

### Arama için Çözüm 2: CirrusSearch'ü Yapılandırma

Eğer kurulumunuzda **CirrusSearch** aktifse, Wikibase verileri varsayılan olarak indekslenir.

1.  **Gelişmiş Arama:** Kullanıcılar, standart arama kutusunu kullanarak etkinlik başlıklarını (Title) ve kısaltmalarını (Acronym) arayabilirler.
2.  **Özellik Araması:** CirrusSearch'ün Wikibase için sunduğu filtreleri kullanarak (örneğin, `has:P1` gibi - burada `P1` sizin "Etkinlik Serisinin Parçası" özelliğinizin ID'si olurdu) kullanıcıların sadece yapılandırılmış alanlarda arama yapmasını sağlayabilirsiniz.

**Özetle, PageForms kullanmadan bu projeyi hayata geçirmenin en sağlam ve standart yolu, EntitySchema (`Special:CreateClass`) üzerine inşa etmektir.**