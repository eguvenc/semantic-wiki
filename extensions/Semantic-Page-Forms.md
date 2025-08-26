
## PageForms

Page Forms, MediaWiki için geliştirilmiş bir eklentidir ve amacı yapılandırılmış (structured) veri girişi ve sayfa oluşturmayı kolaylaştırmaktır.

### Temel İşlevi Nedir?

- Normalde MediaWiki’de sayfa oluşturmak için direkt wikitext yazarsın.
- Page Forms, kullanıcılara kolay ve kullanıcı dostu form tabanlı arayüzler sunar.
- Bu formlar, kullanıcıların karmaşık şablonları (templates) veya semantik veri yapılarını (Semantic MediaWiki için) elle yazmak zorunda kalmadan veri girmesine olanak tanır.

### Neden Önemlidir ?

- Semantic MediaWiki ile birlikte kullanıldığında, sayfalara girilen veriler otomatik olarak semantik özelliklere (properties) dönüşür.
- Kullanıcılar, formdaki alanları doldurarak, arka planda otomatik biçimde uygun şablon ve semantik etiketler oluşturulur.
- Bu sayede hem hatalar azalır, hem de veri tutarlılığı sağlanır.


```sh
composer require mediawiki/page-forms
```

Sonra LocalSettings.php içine şunu ekle:

```php
wfLoadExtension( 'PageForms' );
```