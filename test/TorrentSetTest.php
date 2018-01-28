<?php


namespace Odango\Atama\Test;


use Odango\Atama\Torrent;
use Odango\Atama\TorrentSet;
use PHPUnit\Framework\TestCase;

class TorrentSetTest extends TestCase
{

    public function testEmptySetMetadataRetrieval()
    {
        $set = new TorrentSet();
        $this->assertCount(0, $set->getMetadata());
    }

    public function testMetadataRetrieval() {
        $set = new TorrentSet();
        $torrent = new Torrent();
        $torrent->setTitle("[Ha!] The Test [720p]");
        $set->addTorrent($torrent);

        $md = $set->getMetadata();

        $this->assertEquals('Ha!', $md['group']);
        $this->assertEquals('The Test', $md['name']);
        $this->assertEquals('720p', $md['resolution']);
    }

    public function testMetadataUpdating() {
        $set = new TorrentSet();
        $torrent = new Torrent();
        $torrent->setTitle("[Ha!] The Test [720p]");
        $set->addTorrent($torrent);
        $torrent = new Torrent();
        $torrent->setTitle("[Ha!] The Test [1080p]");
        $set->addTorrent($torrent);

        $md = $set->getMetadata();

        $this->assertEquals('Ha!', $md['group']);
        $this->assertEquals('The Test', $md['name']);
        $this->assertArrayNotHasKey('resolution', $md);
    }
}
