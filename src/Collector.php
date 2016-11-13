<?php


namespace Odango\Atama;


class Collector
{
    /**
     * @var TorrentProvider
     */
    private $provider;

    /**
     * @return TorrentProvider
     */
    public function getProvider(): TorrentProvider
    {
        return $this->provider;
    }

    /**
     * @param TorrentProvider $provider
     */
    public function setProvider(TorrentProvider $provider)
    {
        $this->provider = $provider;
    }

    public function find($name) {
        $torrents = $this->getProvider()->getTorrents($name);

        /** @var TorrentSet[] $sets */
        $sets = [];

        foreach ($torrents as $torrent) {
            $seriesHash = $torrent->getSeriesHash();

            if (!isset($sets[$seriesHash])) {
                $sets[$seriesHash] = new TorrentSet();
            }

            $sets[$seriesHash]->addTorrent($torrent);
        }

        return array_values($sets);
    }
}