
# Faceted Api Search

## LocalSettings.php

```php
<?php
// ************************************************************************
// Wikibase Suite Deploy Extension.php
// ************************************************************************
//
// File to load MediaWiki extension.
//
// This file will be loaded after all other extensions have been loaded,
// just like as if this code would be at the end of LocalSettings.php.
//
// Make sure to prefix the extensions name with "extensions/" when loading.
// e.g. when extension installation instructions state you need to put
//   wfLoadExtension( 'WikibaseLexeme' );
// here in Wikibase Suite Deploy you need to put
//   wfLoadExtension( 'extensions/WikibaseLexeme' );

wfLoadExtension( 'WikibaseFacetedSearch' );
wfLoadExtension( 'FacetedApiSearch' );

# Development error & debug settings
$wgDevelopmentWarnings = true;
$wgShowExceptionDetails = true;
$wgShowDBErrorBacktrace = true;

# Elasticsearch configuration for FacetedApiSearch and WikibaseSearch
$wgWikibaseRootPath = "/var/www/html";
$wgElasticsearchIndexName = "wikibase_content_first";
$wgElasticsearchBaseUrl = "http://wbs-deploy-elasticsearch-1:9200";
$wgFacetedSuggestProperties = [
    'P1',
];
```

Rebuild index

```bash
php extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php
php extensions/CirrusSearch/maintenance/ForceSearchIndex.php
```

Populate Suggestions for P Fields

```bash
php extensions/FacetedApiSearch/maintenance/UpdateSuggestIndex.php --index=wikibase_content_first
php maintenance/run.php extensions/FacetedApiSearch/maintenance/UpdateSuggestIndex.php --index=wikibase_content_first
```

```bash
php extensions/FacetedApiSearch/maintenance/RebuildSuggestIndex.php --index=wikibase_content_first
php maintenance/run.php extensions/FacetedApiSearch/maintenance/RebuildSuggestIndex.php --index=wikibase_content_first
```

Search API:

```bash
https://wikibase.everydayjazz.com/w/api.php?action=facetedsearch&query=Test1&terms={"P5":"Q2"}&dates={"P3":{"gte":"2025-11-01","lte":"2025-11-30"}}&facets=["P1","P2", "P3","P4","P5"]&textFields=["wbfs_P1","wbfs_P2","wbfs_Label"]&page=1&limit=20
```

Suggestion API:

```bash
https://wikibase.everydayjazz.com/w/api.php?action=facetedsearch&query=Te&suggest=1
```

