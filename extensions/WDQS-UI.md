

## 7. WDQS UI (Opsiyonel, Web Arayüzü)

1. Kodları indir:

```bash
cd /opt/wdqs
git clone https://gerrit.wikimedia.org/r/wikidata/query/gui wdqs-ui
```

2. Basit bir nginx/apache ayarıyla `wdqs-ui` dizinini servis et.
3. `wdqs-ui` içindeki `config.json` dosyasında şu ayarı yap:

```json
{
  "endpoint": "http://localhost:9999/bigdata/sparql"
}
```

Artık [http://localhost/wdqs-ui](http://localhost/wdqs-ui) → SPARQL sorgularını görsel arayüzden çalıştırabilirsin.
