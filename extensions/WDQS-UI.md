
## 7. WDQS UI (Optional, Web Interface)

1. Download the code:

```bash
cd /opt/wdqs
git clone https://gerrit.wikimedia.org/r/wikidata/query/gui wdqs-ui
```

2. Serve the `wdqs-ui` directory using a simple nginx/apache configuration.
3. In the `config.json` file inside `wdqs-ui`, set the following:

```json
{
  "endpoint": "http://localhost:9999/bigdata/sparql"
}
```

You can now access [http://localhost/wdqs-ui](http://localhost/wdqs-ui) → run SPARQL queries through a graphical interface.
