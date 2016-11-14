<?php


namespace Odango\Atama;


class TorrentSet
{
    /**
     * @var Torrent[]
     */
    private $torrentsMappedByHash = [];

    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @var bool
     */
    private $cleanMetadata = true;

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
            $this->metadata = new Metadata();
        }

        return $this->metadata;
    }

    public function addTorrent(Torrent $torrent): bool {
        $hash = $torrent->getHash();

        if (
            isset($this->torrentsMappedByHash[$hash]) &&
            ($torrent->getMetadata()['version'] ?? 0) <= ($this->torrentsMappedByHash[$hash]->getMetadata()['version'] ?? 0)
        ) {
            return false;
        }

        $this->torrentsMappedByHash[$hash] = $torrent;
        $this->updateMetadata($torrent->getMetadata());
        return true;
    }

    private function updateMetadata(Metadata $metadata) {
        if ($this->cleanMetadata) {
            $this->cleanMetadata = false;

            $this->metadata = clone $metadata;
            return;
        }

        foreach ($this->metadata as $key => $value) {
            $newValue = $metadata[$key] ?? null;
            if ($newValue !== $value) {
                unset($this->metadata[$key]);
            }
        }
    }
}