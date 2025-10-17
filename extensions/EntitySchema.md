

## EntitySchema

---

1.  **PHP Bağımlılıklarını Kurun (Sadece Git'ten indirdiyseniz):**

	EntitySchema eklentisini kurun. (https://www.mediawiki.org/wiki/Extension:EntitySchema) . Uzantıyı Git ile klonladığınız için, uzantı dizininde PHP bağımlılıklarını kurmak için `composer` kullanmanız gerekir.

      * EntitySchema dizinine gidin:
        ```bash
        cd extensions/
		    git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/EntitySchema
        git checkout REL1_39
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
