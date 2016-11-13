<?php


namespace Odango\Atama;


class TorrentSet
{
    /**
     * @var Torrent[]
     */
    private $torrentsMappedByHash;

    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @return Torrent[]
     */
    public function getTorrents(): array
    {
        return array_values($this->torrentsMappedByHash);
    }

    public function getMetadata(): Metadata
    {
        if ($this->metadata === null) {
            if (count($this->getTorrents()) > 0) {
                $this->metadata = $this->getTorrents()[0]->getMetadata();
            } else {
                $this->metadata = new Metadata();
            }
        }

        return $this->metadata;
    }

    public function addTorrent(Torrent $torrent): bool {
        $hash = $torrent->getHash();

        if (!isset($this->torrentsMappedByHash[$hash])) {
            $this->torrentsMappedByHash[$hash] = $torrent;
            return true;
        }

        $oldTorrent = $this->torrentsMappedByHash[$hash];

        if (($torrent->getMetadata()['version'] ?? 0) < ($oldTorrent->getMetadata()['version'] ?? 0)) {
            return false;
        }

        $this->torrentsMappedByHash[$hash] = $torrent;
        return true;
    }
}