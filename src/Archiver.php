<?php


namespace Odango\Atama;

class Archiver
{
    /**
     * @var TorrentSet[]
     */
    private $sets = [];

    /**
     * @return TorrentSet[]
     */
    public function getSets(): array
    {
        return array_values($this->sets);
    }

    /**
     * @param Torrent $torrent
     * @return bool
     */
    public function addTorrent(Torrent $torrent): bool {
        $seriesHash = $torrent->getSeriesHash();

        if (!isset($this->sets[$seriesHash])) {
            $this->sets[$seriesHash] = new TorrentSet();
        }

        return $this->sets[$seriesHash]->addTorrent($torrent);
    }


    /**
     * @param $torrents Torrent[]
     * @return TorrentSet[]
     */
    public static function archive($torrents) {
        $archive = new Archiver();

        foreach ($torrents as $torrent) {
            $archive->addTorrent($torrent);
        }

        return $archive->getSets();
    }
}