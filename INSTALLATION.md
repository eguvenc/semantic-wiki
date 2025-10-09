
# Installation

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y apache2 php libapache2-mod-php php-mysql \
  php-intl php-mbstring php-xml php-apcu php-curl php-zip unzip git composer curl \
  openjdk-17-jdk
```

Create a new database for your new Wiki project.

```sql
CREATE DATABASE wikidb;
FLUSH PRIVILEGES;
EXIT;
```

Create a vhost for the project.


```sh
cd /etc/apache2/sites-available/
ll
cp 000-default.conf mediawiki.conf
vim mediawiki.conf 
```

<VirtualHost *:80>
    ServerName mediawiki.local
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/mediawiki

    # apache mod headers must be enabled with this command
    # sudo a2enmod headers
  	
    <Directory /var/www/mediawiki/images>

        # .htaccess dosyalarını göz ardı et
        AllowOverride None

        # PHP motorunu kapat
        php_admin_flag engine off

        # HTML / PHP gibi dosyaları düz metin olarak sun
        AddType text/plain .php .phtml .php3 .php4 .php5 .php7 .html .htm .shtml

        # PHP dosyalar için handler’ı kaldır
        <FilesMatch "\.ph(p[3-7]?|tml)$">
           SetHandler None
        </FilesMatch>

        # Tarayıcıların dosya tipini tahmin etmesini engelle
        Header set X-Content-Type-Options "nosniff"
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>


Enable mediawiki website.

```sh
a2ensite mediawiki.conf 
sudo a2enmod headers
sudo a2enmod rewrite
service apache2 restart
```

Add mediawiki.local as localhost.

```sh
vim /etc/hosts

127.0.0.1 localhost
127.0.0.1 mediawiki.local
```

Learn latest media wiki core version from here and copy it. (REL1_39 is latest stabil version)


https://gerrit.wikimedia.org/g/mediawiki/core


First Increase Git's http.postBuffer setting.

```sh
git config --global http.postBuffer 524288000
git config --global core.compression 0
````

then Clone the latest project repo. 


```sh
cd /var/www/

wget https://releases.wikimedia.org/mediawiki/1.44/mediawiki-1.44.0.tar.gz
tar -xvzf mediawiki-1.44.0.tar.gz
mv mediawiki-1.44.0 mediawiki

chown -R www-data:www-data mediawiki

cd mediawiki
sudo composer install --no-dev
```

## Visit http://mediawiki.local/

Click to complete installation link then download LocaleSettings.php paste it to your /var/www/mediawiki/ root folder.


## LocalSettings.php

You will need to download it and put LocalSettings.php file in the base of your wiki installation (the same directory as index.php). 

## Displaying loaded extensions and plugins:

```
👉 http://mediawiki.local/index.php/Special:Version
```

## Fixing permission errors:

1. Install ACL for Ubuntu

```bash
sudo apt install acl
```

2. Add write permissions to MediaWiki components for your user e.g. `ersin`:

```bash
sudo setfacl -R -m u:ersin:rwx /var/www/mediawiki
```

3. Write permission for the web server (`www-data`):

```bash
sudo setfacl -R -m u:www-data:rwx /var/www/mediawiki
```

4. Ensure that newly created files and folders have the same permissions as the ACL:

```bash
sudo setfacl -R -d -m u:ersin:rwx /var/www/mediawiki
sudo setfacl -R -d -m u:www-data:rwx /var/www/mediawiki
```

So, it can always write to both `ersin` and `www-data` folders.


## Installing a Default Skin

Clone a skin


```
cd /var/www/mediawiki/skins
git clone https://gerrit.wikimedia.org/r/mediawiki/skins/Vector.git
cd Vector
git checkout REL1_41  // checkout mediawiki current version 
```

Enable skin in your LocalSettings.php:

```php
wfLoadSkin( 'Vector' );
$wgDefaultSkin = 'vector';
```

Restart apache2

```sh
sudo systemctl restart apache2
```
