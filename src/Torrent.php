<?php


namespace Odango\Atama;


class Torrent
{
    public $id;
    public $title;
    public $metadata;

    /**
     * @return Metadata
     */
    public function getMetadata(): Metadata
    {
        if ($this->metadata === null) {
            $this->metadata = Metadata::createFromTitle($this->title);
        }

        return $this->metadata;
    }

    /**
     * Gets a unique string for in this series, e.g. for an series of EP's this would be the ep number
     * @return string
     */
    public function getSeriesIdentifier(): string
    {
        switch($this->getMetadata()['type']) {
            case 'volume':
                return $this->getMetadata()['volume'];
            case 'collection':
                return implode('-', $this->getMetadata()['collection']);
            case 'ep':
                return $this->getMetadata()['ep'];
            case 'special':
            case 'ova':
            case 'ona':
                return $this->id;
            default:
                return 0;
        }
    }

    public function getHash(): string {
        return $this->getSeriesHash() . '#' . $this->getSeriesIdentifier();
    }

    public function getSeriesHash(): string {
        $md = $this->getMetadata();

        $parts = [$md['name'], $md['group'], $md['type'], $md['resolution']];
        return implode("/", $parts);
    }
}