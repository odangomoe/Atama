<?php


namespace Odango\Atama\Test;

use Odango\Atama\Collector;
use Odango\Atama\Torrent;

class CollectorTest extends \PHPUnit_Framework_TestCase
{
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

        $torrents = [];

        foreach ($titles as $title) {
            $torrent = new Torrent();
            $torrent->title = $title;
            $torrents[] = $torrent;
        }


        $provider = \Mockery::mock('Odango\Atama\TorrentProvider');
        $provider
            ->shouldReceive('getTorrents')
            ->withArgs(['Tokyo Ghoul'])
            ->andReturn($torrents);

        $collector = new Collector();
        $collector->setProvider($provider);

        $collections = $collector->find('Tokyo Ghoul');

        $this->assertCount(1, $collections);
        $collection = $collections[0];

        $this->assertCount(12, $collection->getTorrents());
    }
}
