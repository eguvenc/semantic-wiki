#!/bin/bash
set -e

########################################
# DIRECTORY CHECK
########################################

REQUIRED_DIR="/root/wikibase-release-pipeline/deploy"
CURRENT_DIR="$(pwd)"

if [ "$CURRENT_DIR" != "$REQUIRED_DIR" ]; then
  echo "❌ ERROR: Wrong working directory!"
  echo "This script must be executed from:"
  echo "  $REQUIRED_DIR"
  echo ""
  echo "Current directory:"
  echo "  $CURRENT_DIR"
  echo ""
  echo "Please run:"
  echo "  cd $REQUIRED_DIR"
  echo "  sh docker.sh start|down"
  exit 1
fi

ACTION=$1

########################################
# SETTINGS
########################################

WIKIBASE_CONTAINER="wbs-deploy-wikibase-1"
JOBRUNNER_SERVICE="wikibase-jobrunner"
PROJECT_ROOT="/var/www/html"
LOG_PATH="/var/lib/docker/containers"
THRESHOLD=90
DATE=$(date '+%Y-%m-%d %H:%M:%S')

echo "▶ docker.sh started | action: $ACTION"
echo "----------------------------------------"

########################################
# START
########################################

if [ "$ACTION" = "start" ]; then
  echo "▶ Starting Docker containers (docker compose up)..."
  docker compose up --build -d
  echo "✔ Docker containers are running"

  ########################################
  # COMPOSER
  ########################################

  echo "▶ Checking Composer installation..."
  docker exec "$WIKIBASE_CONTAINER" bash -c '
    set -e
    if ! command -v composer >/dev/null 2>&1; then
      echo "▶ Composer not found, installing..."
      php -r "copy(\"https://getcomposer.org/installer\", \"composer-setup.php\");"
      php composer-setup.php --install-dir=/usr/local/bin --filename=composer
      php -r "unlink(\"composer-setup.php\");"
      echo "✔ Composer installed successfully"
    else
      echo "✔ Composer is already installed"
    fi
  '

  echo "▶ Setting permissions for Composer files..."
  docker exec "$WIKIBASE_CONTAINER" bash -c "
    chown root:root $PROJECT_ROOT/composer.local.json 2>/dev/null || true
    chown root:root $PROJECT_ROOT/composer.lock 2>/dev/null || true
  "

  echo "▶ Running composer update (no-dev)..."
  docker exec "$WIKIBASE_CONTAINER" bash -c "
    cd $PROJECT_ROOT
    composer update --no-dev
  "
  echo "✔ Composer update completed"

  ########################################
  # JOBRUNNER
  ########################################

  echo "▶ Making jobrunner entrypoint executable..."
  docker compose exec "$JOBRUNNER_SERVICE" chmod +x /jobrunner-entrypoint.sh
  echo "✔ Jobrunner entrypoint permissions updated"

  ########################################
  # MEDIAWIKI UPDATE
  ########################################

  echo "▶ Running MediaWiki maintenance update..."
  docker exec "$WIKIBASE_CONTAINER" bash -c "
    cd $PROJECT_ROOT
    php maintenance/run.php update --conf /config/LocalSettings.php
  "
  echo "✔ MediaWiki maintenance update completed"

  ########################################
  # CIRRUSSEARCH INDEX UPDATE
  ########################################

  echo "▶ Updating CirrusSearch index configuration..."
  docker exec "$WIKIBASE_CONTAINER" bash -c "
    cd $PROJECT_ROOT
    php maintenance/run.php CirrusSearch:UpdateSearchIndexConfig
  "
  echo "✔ CirrusSearch index configuration updated"

  echo "▶ Force updating search index (skip parsing)..."
  docker exec "$WIKIBASE_CONTAINER" bash -c "
    cd $PROJECT_ROOT
    php maintenance/run.php CirrusSearch:ForceSearchIndex --skipParse
  "
  echo "✔ Search index force update completed"

  echo "✔ START process finished successfully"
  exit 0
fi

########################################
# DOWN
########################################

if [ "$ACTION" = "down" ]; then
  echo "▶ Stopping Docker containers (docker compose down)..."
  docker compose down
  echo "✔ Docker containers stopped"

  echo "▶ Checking disk usage..."
  USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//g')

  if [ "$USAGE" -ge "$THRESHOLD" ]; then
    echo "$DATE - Disk usage is ${USAGE}%, cleaning Docker logs..."

    find "$LOG_PATH" -name "*.log" -type f -exec sh -c 'echo "" > "$1"' _ {} \;

    echo "$DATE - All Docker container logs have been cleaned"
  else
    echo "$DATE - Disk usage is ${USAGE}%, no log cleanup required"
  fi

  echo "✔ DOWN process finished successfully"
  exit 0
fi

########################################
# INVALID USAGE
########################################

echo "❌ Invalid usage!"
echo "Usage:"
echo "  sh docker.sh start"
echo "  sh docker.sh down"
exit 1
