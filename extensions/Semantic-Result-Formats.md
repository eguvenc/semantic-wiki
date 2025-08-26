
## Semantic Result Formats

Semantic Result Formats, SMW allows you to display query results with visuals such as tables, graphs, maps, timelines, etc.

| Format                             | Açıklama                                                                   |
| ---------------------------------- | -------------------------------------------------------------------------- |
| `table`                            | HTML tablo                                                                 |
| `list`                             | Liste                                                                      |
| `timeline`                         | Etkinlikleri zamana göre gösterir (Google Timeline veya Simile Timeline)   |
| `graph`                            | RDF ilişkileri görselleştirir (force-directed graph)                       |
| `calendar`                         | Tarih verilerini takvim olarak gösterir                                    |
| `sparkline`                        | Küçük grafikler                                                            |
| `gallery`                          | Görsel galerisi                                                            |
| `map`                              | Coğrafi koordinatları haritada gösterir (OpenLayers, Leaflet, Google Maps) |
| `tagcloud`                         | Etiket bulutu oluşturur                                                    |
| `treemap`, `piechart`, `bar chart` | Görsel istatistikler                                                       |
| `eventline`, `eventcalendar`       | Zaman serileri ve etkinlik odaklı görseller                                |


## Installation

Ubuntu altında MediaWiki çalıştırıyorsan SRF’yi şu şekilde yükleyebilirsin:

MediaWiki'nin extensions dizinine git:


```sh
cd /var/www/mediawiki/
composer require mediawiki/semantic-result-formats "^5.0"
```

LocalSettings.php dosyana ekle:

```php
wfLoadExtension( 'SemanticResultFormats' );
```

Eğer özel formatlar gerekiyorsa ek modülleri yükle (örneğin Maps, Graphviz gibi).


## Example 

Sorguyu tablo değil de grafik olarak görmek istersen:

```
{{#ask:
 [[Category:Kişi]]
 | ?doğumTarihi
 | format=timeline
}}
```

Ya da:

```
{{#ask:
 [[katıldığıOlaylar::Kurtuluş Savaşı]]
 | ?doğumYeri
 | format=map
}}
```

## Notlar:

SRF, MediaWiki + SMW sürüm uyumluluğu açısından hassastır. Kullandığın MediaWiki ve SMW sürümüne uygun SRF sürümünü çektiğinden emin ol.

Google Maps API anahtarı gerektiren bazı harita özellikleri vardır. Ama OpenStreetMap ve Leaflet gibi açık kaynak çözümlerle API gerekmez.

İstersen birlikte örnek bir timeline, map, veya graph çıktısı oluşturabiliriz. Hangi formatı denemek istersin?