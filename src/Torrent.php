<?php


namespace Odango\Atama;


class Torrent
{
    protected $id;
    protected $title;
    protected $metadata;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return Metadata
     */
    public function getMetadata(): Metadata
    {
        if ($this->metadata === null) {
            $this->metadata = Metadata::createFromTitle($this->getTitle());
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
                return implode(',', $this->getMetadata()['ep']);
            case 'special':
            case 'ova':
            case 'ona':
                return $this->getId() ?? rand(0, 9999);
            default:
                return 0;
        }
    }

    public function getHash(): string {
        return $this->getSeriesHash() . '#' . $this->getSeriesIdentifier();
    }

    public function getSeriesHash(): string {
        $md = $this->getMetadata();

        $parts = [$md['name'], $md['group'], $md['type'], $md['resolution'] ?? 'unknown'];
        return implode("/", $parts);
    }
}