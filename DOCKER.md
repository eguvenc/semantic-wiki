
## STARTING DOCKER SERVICE

```bash
service start docker
```

## STOPPING DOCKER SERVICE

```bash
sudo systemctl status docker
sudo systemctl status docker.socket
```

## IMPORTANT DOCKER TIPS: CREATING USER GROUPS

Create the docker group.

```bash
sudo groupadd docker
```

Add your user to the docker group.

```bash
sudo usermod -aG docker $USER
```

Log out and log back in so that your group membership is re-evaluated.

If you're running Linux in a virtual machine, it may be necessary to restart the virtual machine for changes to take effect.

You can also run the following command to activate the changes to groups:

```bash
newgrp docker
```

https://docs.docker.com/engine/install/linux-postinstall/


### Listing docker containers:

```bash
docker ps
```

CONTAINER ID   IMAGE                                                                                            COMMAND                   CREATED       STATUS                 PORTS                                         NAMES
c6deadccd878   wikiprojects_testwiki-wdqs-frontend                                                              "/docker-entrypoint.…"    2 hours ago   Up 2 hours (healthy)   0.0.0.0:8900->80/tcp, [::]:8900->80/tcp       wikiprojects_testwiki-wdqs-frontend-1
f0accd5738ba   wikibase/wdqs-proxy:1                                                                            "/bin/sh -c \"/entryp…"   2 hours ago   Up 2 hours             80/tcp                                        wikiprojects_testwiki-wdqs-proxy-1
90f5d8cb285d   wikibase/wdqs:1                                                                                  "/entrypoint.sh /wdq…"    2 hours ago   Up 1 second                                                          wikiprojects_testwiki-wdqs-updater-1
b5da09ae8143   wikiprojects_testwiki-wikibase_jobrunner                                                         "docker-php-entrypoi…"    2 hours ago   Up 2 hours             80/tcp                                        wikiprojects_testwiki-wikibase_jobrunner-1
c669aee283dc   wikiprojects_testwiki-control                                                                    "/app/entrypoint_con…"    2 hours ago   Up 2 hours                                                           wikiprojects_testwiki-control-1
3d3b579ee969   wikibase/wdqs:1                                                                                  "/entrypoint.sh /run…"    2 hours ago   Up 2 hours (healthy)                                                 wikiprojects_testwiki-wdqs-1
5f265bca7642   wikiprojects_testwiki-wikibase                                                                   "/usr/local/bin/entr…"    2 hours ago   Up 2 hours (healthy)   80/tcp                                        wikiprojects_testwiki-wikibase-1
0ced43e32584   registry.gitlab.com/nfdi4culture/openrefine-reconciliation-services/openrefine-wikibase:latest   "python app.py"           2 hours ago   Up 2 hours             0.0.0.0:8000->8000/tcp, [::]:8000->8000/tcp   wikiprojects_testwiki-openrefine-1
5d39af7807b3   wikibase/elasticsearch:1                                                                         "/tini -- /usr/local…"    2 hours ago   Up 2 hours (healthy)   9200/tcp, 9300/tcp                            

wikiprojects_testwiki-elasticsearch-1

41056217fe9d   mariadb:10.9                                                                                     "docker-entrypoint.s…"    2 hours ago   Up 2 hours (healthy)   3306/tcp                                      wikiprojects_testwiki-database-1
38cce0e61b48   traefik:v3.0                                                                                     "/entrypoint.sh --lo…"    2 hours ago   Up 2 hours             0.0.0.0:80->80/tcp, [::]:80->80/tcp           wikiprojects_testwiki-reverse-proxy-1
db74ab6d33cc   redis:alpine                                                                                     "docker-entrypoint.s…"    2 hours ago   Up 2 hours             6379/tcp   


### Deleting All Containers

```bash
docker stop $(docker ps -aq)
docker rm $(docker ps -aq)
docker volume prune
docker network prune
```

* docker ps -aq → lists all container IDs.
* docker volume prune -> Only deletes unused volumes
* docker network prune -> Only deletes unused volume networks


### Deleting Specific Container

If you want to remove ***wikidemo*** project.

```bash
docker ps -a | grep wikidemo | awk '{print $1}' | xargs docker rm -f
```

## Cleaning containers, images, volumes and networks in the entire system

```bash
docker system prune -a --volumes
```

### Listing files in Docker Containers

```bash
docker exec -it wikiprojects_testwiki-wikibase-1 bash

root@5f265bca7642:/var/www/html# ls

CODE_OF_CONDUCT.md     LocalSettings.php.tmp  composer.local.json   img_auth.php     mw-config      tests
COPYING          README.md        composer.local.json-sample  includes     opensearch_desc.php    thumb.php
CREDITS          RELEASE-NOTES-1.39     composer.lock     index.php    pages-to-delete.txt    thumb_handler.php
FAQ          SECURITY         docker-compose.yml    initLocalSettings.sh   patchSMWNamespaceManager.sh  vendor
HISTORY          UPGRADE          docs        install-extensions.sh  rebuildWikibaseIdCounters.sql  w
INSTALL          api.php          dump.xml        jsduck.json    resources
LocalSettings.d        autoload.php       extensionManagement.json    languages    rest.php
LocalSettings.php      cache          extensions      load.php     scripts
LocalSettings.php.bak  composer.json        images        maintenance    skins
```

### Reaching to ElasticSearch 

```bash
docker exec -it wikiprojects_testwiki-elasticsearch-1 bash
```

```bash
curl -X GET http://localhost:9200

{
  "name" : "5d39af7807b3",
  "cluster_name" : "docker-cluster",
  "cluster_uuid" : "DZw6YkvCRXqeiO4Y5GPgTA",
  "version" : {
    "number" : "7.10.2",
    "build_flavor" : "default",
    "build_type" : "docker",
    "build_hash" : "747e1cc71def077253878a59143c1f785afa92b9",
    "build_date" : "2021-01-13T00:42:12.435326Z",
    "build_snapshot" : false,
    "lucene_version" : "8.7.0",
    "minimum_wire_compatibility_version" : "6.8.0",
    "minimum_index_compatibility_version" : "6.0.0-beta1"
  },
  "tagline" : "You Know, for Search"
}
```

