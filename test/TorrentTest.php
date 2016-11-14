<?php


namespace Odango\Atama\Test;


use Odango\Atama\Torrent;

class TorrentTest extends \PHPUnit_Framework_TestCase
{
    // PURE FOR THAT 100% CODE COVERAGE
    public function testIdSetting()
    {
        $torrent = new Torrent();
        $this->assertEquals(null, $torrent->getId());
        $torrent->setId(3);
        $this->assertEquals(3, $torrent->getId());
    }
}
