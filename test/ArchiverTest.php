<?php


namespace Odango\Atama\Test;

use Odango\Atama\Archiver;
use Odango\Atama\Torrent;

class ArchiverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string[] $names
     * @return Torrent[]
     */
    public function getTorrentArray(array $names): array {
        $torrents = [];

        foreach ($names as $title) {
            $torrent = new Torrent();
            $torrent->setTitle($title);
            $torrents[] = $torrent;
        }

        return $torrents;
    }

    public function testCollection()
    {
        $titles = [
            "[HorribleSubs] Tokyo Ghoul - 01 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 02 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 03 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 04 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 05 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 06 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 07 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 08 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 09 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 10 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 11 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 12 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 12v2 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 12v1 [720p].mkv",
        ];

        $torrents = $this->getTorrentArray($titles);
        $collections = Archiver::archive($torrents);

        $this->assertCount(1, $collections);
        $collection = reset($collections);

        $this->assertCount(12, $collection->getTorrents());
    }

    public function testDifferingTypeCollection() {
        $titles = [
            "[HorribleSubs] Tokyo Ghoul - 01 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 02 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 03 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 04 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 05 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 06 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 07 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 08 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 09 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 10 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 11 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 12 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 12v2 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 12v1 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - Vol. 1 [1080p].mkv",
            "[HorribleSubs] Tokyo Ghoul - Vol. 1 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - Vol. 3 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul [special][720p].mkv",
            "[HorribleSubs] Tokyo Ghoul - 5-10 [720p].mkv",
            "[HorribleSubs] Tokyo Ghoul [ona][720p].mkv",
            "[HorribleSubs] Tokyo Ghoul [ova][720p].mkv",
            "Tokyo Ghoul"
        ];

        $torrents = $this->getTorrentArray($titles);
        $collections = Archiver::archive($torrents);

        $this->assertCount(8, $collections);
        $collection = reset($collections);

        $this->assertCount(12, $collection->getTorrents());
    }
}
