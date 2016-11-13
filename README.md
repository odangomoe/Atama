# 頭 (Atama)
Anime Torrent title parsing and archiving

## Metadata

The actual parsing of titles done with black magic, battle tested on the whole nyaa site.
Some cases fail those you can find in `test/data/metadata-title.json` with the attribute `should_fail` in the comment is explained why

```php
$md = Metadata::createFromTitle("[HorribleSubs] Show By Rock!! S2 - 07 [720p].mkv");

echo $md["name"]; // Show By Rock!! S2
echo $md["group"]; // HorribleSubs
echo $md["type"]; // ep
echo $md["ep"]; // 7
echo $md["resolution"]; // 720p
```

## Collector

### `find($name): TorrentSet[]`
 
This function will find all torrents through the torrent provider and put all found torrents in TorrentSets archived by resolution, group, type and name 

```
$collector = new Collector();
$sets = $collector->find('Show By Rock!! S2');

echo $sets[0]->getMetadata()['group'] . ': ' . $sets[0]->getMetadata()['resolution']; // HorribleSubs: 720p
echo $sets[1]->getMetadata()['group'] . ': ' . $sets[0]->getMetadata()['resolution']; // HorribleSubs: 1080p
```