

**WARNING:** To prevent the change from being reverted every time, this setting needs to be added to ./deploy/jobrunner-entrypoint.sh:/jobrunner-entrypoint.sh in addition to other volumes under wikibase-jobrunner.

```yml
wikibase-jobrunner:
  volumes:
    - ./config:/config:z
    - ./config/extensions:/var/www/html/extensions/extensions:z
    - ./config/Extensions.php:/var/www/html/LocalSettings.d/90_UserDefinedExtensions.php:z
    - wikibase-image-data:/var/www/html/images
    - quickstatements-data:/quickstatements/data
    - ./jobrunner-entrypoint.sh:/jobrunner-entrypoint.sh 
```