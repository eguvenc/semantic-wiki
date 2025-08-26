
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


Thats It !


## Adding Localhost Starter for your .local domains

```phtml
<?php
// Ayarlar
$documentRootBase = '/var/www'; // Projelerin kök dizini

// /etc/hosts dosyasını oku
$hostsFile = file('/etc/hosts');
$domains = [];

foreach ($hostsFile as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;

    $parts = preg_split('/\s+/', $line);
    if (count($parts) < 2) continue;

    $ip = $parts[0];
    // Sadece 127.0.0.1 IP'sine bak
    if ($ip === '127.0.0.1') {
        for ($i = 1; $i < count($parts); $i++) {
            $host = $parts[$i];
            // Basit kontrol: sadece .local uzantılıları al
            if (str_ends_with($host, '.local')) {
                // Örn: mediawiki.local → mediawiki
                $folderName = explode('.', $host)[0];
                $fullPath = "$documentRootBase/$folderName";

                $domains[] = [
                    'host' => $host,
                    'folder' => $folderName,
                    'path' => $fullPath,
                    'exists' => is_dir($fullPath)
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yerel Domain Yöneticisi</title>
    <style>
        body { font-family: sans-serif; padding: 2em; background: #f7f7f7; }
        h1 { color: #333; }
        ul { list-style: none; padding: 0; }
        li { background: #fff; margin: 0.5em 0; padding: 1em; border-radius: 8px; }
        a { font-size: 1.2em; color: #007BFF; text-decoration: none; }
        .missing { color: red; font-style: italic; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>🔗 Yerel Domain Projeleri</h1>
    <ul>
        <?php foreach ($domains as $d): ?>
            <li>
                <a href="http://<?= htmlspecialchars($d['host']) ?>" target="_blank">
                    <?= htmlspecialchars($d['host']) ?>
                </a>
                <?php if (!$d['exists']): ?>
                    <span class="missing">❌ <?= htmlspecialchars($d['path']) ?> bulunamadı</span>
                <?php else: ?>
                    <span>✅ <?= htmlspecialchars($d['path']) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</bsody>
</html>
```