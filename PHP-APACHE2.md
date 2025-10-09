
## PHP

```sh
sudo apt-get install -y php-cli php-common php-mysql php-zip php-gd php-mbstring php-curl php-xml php-bcmath php-intl
```

To see all enabled extensions

```
php -m
```

## APACHE2

Enable Apache2 Mode Rewrite (.htaccess) and Setting Your ServerName

```php
sudo a2enmod rewrite
```

Go

```php
vim /etc/apache2/apache2.conf
```

Replace AllowOverride None values with <b>AllowOverride All</b>.

Also we set serverName to prevent service restart errors.

```php
<Directory />
        Options FollowSymLinks
        AllowOverride All
        Require all denied
</Directory>

<Directory /usr/share>
        AllowOverride All
        Require all granted
</Directory>

<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>
```

Restart Apache2

```php
sudo service apache2 restart
```