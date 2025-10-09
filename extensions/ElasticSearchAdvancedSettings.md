
Advanced Settings (Optional)

```bash
# Elasticsearch cluster name — tek node için basit bir ad yeterli
cluster.name: mediawiki

# Node kimliği (tek bir Elasticsearch çalışıyorsa node-1 yeterli)
node.name: node-1

# Yalnızca yerel bağlantılara izin ver
network.host: 0.0.0.0  # or just localhost
http.port: 9300 # desired port, e.g., 9300

# Tek node çalıştığı için otomatik discovery devre dışı
discovery.type: single-node

# JVM heap hatalarını önlemek için memlock aktif
bootstrap.memory_lock: true

# Index ayarları — MediaWiki için optimize
index.number_of_shards: 1
index.number_of_replicas: 0

# Daha verimli segment birleştirme
indices.store.throttle.max_bytes_per_sec: 200mb

# Disk alanı koruma eşiği (eğer disk dolarsa indeksleme durur)
cluster.routing.allocation.disk.threshold_enabled: true
cluster.routing.allocation.disk.watermark.low: 85%
cluster.routing.allocation.disk.watermark.high: 90%
cluster.routing.allocation.disk.watermark.flood_stage: 95%

# Log ayarları
path.data: /var/lib/elasticsearch
path.logs: /var/log/elasticsearch

# X-Pack özellikleri kapalı (Wikibase için gerekmez)
xpack.security.enabled: false
xpack.monitoring.enabled: false
xpack.ml.enabled: false
xpack.watcher.enabled: false

# -------------------- Performans Ayarları --------------------

# Segment cache sınırı (MediaWiki için idealdir)
indices.fielddata.cache.size: 30%
indices.breaker.fielddata.limit: 40%
indices.breaker.request.limit: 40%
indices.breaker.total.limit: 70%
```
