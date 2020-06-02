# 頭 (Atama)

[![Build Status](https://travis-ci.org/odangomoe/Atama.svg?branch=master)](https://travis-ci.org/odangomoe/Atama) [![Coverage Status](https://coveralls.io/repos/github/odangomoe/Atama/badge.svg?branch=master)](https://coveralls.io/github/odangomoe/Atama?branch=master)

Anime torrent title parsing and archiving

This package's only use is to parse anime torrent titles and archive them into TorrentSets

### Install

```
composer require odango/atama
```

## Metadata

The actual parsing of titles done with black magic, battle tested on the whole nyaa site. of course there may be some cases where it fails to parse correctly, please create an issue for those.

```php
$md = Metadata::createFromTitle("[HorribleSubs] Show By Rock!! S2 - 07 [720p].mkv");

echo $md["name"]; // Show By Rock!! S2
echo $md["group"]; // HorribleSubs
echo $md["type"]; // ep
echo $md["ep"]; // [7.0]
echo $md["resolution"]; // 720p
```

See [metadate-title.json](test/data/metadata-title.json) for more info.

EP's are represented by an array with floats and/or letters (thank Re:Zero for that):

```php
$md = Metadata::createFromTitle("[HorribleSubs] Re Zero kara Hajimeru Isekai Seikatsu - 01A [1080p].mkv");
$md["ep"]; // [1.0, "A"]
$md = Metadata::createFromTitle("[m.3.3.w] Hanasakeru Seishounen 12.5 (XviD) [89BACFB9].avi");
$md["ep"]; // [12.5]
```

The `Metadata` object is an `ArrayObject` and doesn't define any functions except it's constructors

### `Metadata::createFromTitle($title): Metadata`

Create an `Metadata` object from a title by parsing all the info from the title

### `Metadata::createFromArray($array): Metadata`

Create an `Metadata` object with the given array as properties

## Archiver

Archives the torrents into the correct `TorrentSet`'s can be done stateless (by using `Archiver::archive`) or with state, by creating the `Archiver` object

### `Archiver::archive(Torrent[] $torrents): TorrentSet[]`

This function will archive given torrents in separate `TorrentSet`'s and return those, as you would expect it.

All HorribleSubs, Show By Rock!! S2, 720p will be in it's own set, while the 1080p torrents of those will be in a different set.

### `Archiver->addTorrent(Torrent $torrent): bool`

This will archive this torrent in the `TorrentSet`'s currently in the archive.

This will return `true` if it's added to a TorrentSet, `false` if a newer version or the same version already exists

### `Archiver->getSets(): TorrentSet[]`

Get the `TorrentSet`'s currently in the archive

## TorrentSet

### `TorrentSet->addTorrent(Torrent $torrent): bool`

This will try to add this torrent to this `TorrentSet`

This will return `true` if it's added to this `TorrentSet`, `false` if a newer version or the same version already exists

### `TorrentSet->getTorrents(): Torrent[]`

Returns all the torrents in the `TorrentSet`

### `TorrentSet->getMetadata(): Metadata`

Gets a `Metadata` object with the `Metadata` all torrents in the set have in common

## Torrent

The `Torrent` object is pretty much made only to be extended and add more info about it, the current implementation just provides enough information to be used

### `Torrent->setTitle(string $title): void`

Set the title of this torrent and update metadata accordingly

### `Torrent->getTitle(): string`

Gets the title of this torrent

### `Torrent->getId(): string|int`

Gets the id of this torrent, used as unique identifier in the hash for series

### `Torrent->getMetadata(): Metadata`

Gets the metadata of this torrent parsed from the title

### `Torrent->getSeriesIdentifier(): string`

Gets a unique string for in this series, e.g. for an series of EP's this would be the ep number. The id or a random number is used for specials

### `Torrent->getHash(): string`

Gets a unique string for this torrent e.g. `[HorribleSubs] Show By Rock!! S2 - 07 [720p].mkv` would produce the hash `Show By Rock!! S2/HorribleSubs/720p#7` be aware that different versions of torrents still produce the same hash e.g. `[HorribleSubs] Show By Rock!! S2 - 07v2 [720p].mkv` will also produce the hash `Show By Rock!! S2/HorribleSubs/720p#7`

### `Torrent->getSeriesHash(): string`

Gets a unique string this torrent series e.g. `[HorribleSubs] Show By Rock!! S2 - 07 [720p].mkv` would produce the hash `Show By Rock!! S2/HorribleSubs/720p`
