
# Installation

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
service restaert apache2
```

Add mediawiki.local as localhost.

```sh
vim /etc/hosts

127.0.0.1 localhost
127.0.1.1 ersin
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
wget https://releases.wikimedia.org/mediawiki/1.41/mediawiki-1.41.5.tar.gz
tar -xvzf mediawiki-1.41.5.tar.gz
mv mediawiki-1.41.5 /var/www/mediawiki
cd mediawiki
sudo composer install --no-dev
```

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