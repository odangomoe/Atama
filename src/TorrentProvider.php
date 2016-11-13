<?php


namespace Odango\Atama;


interface TorrentProvider
{
    /**
     * @param $name string
     * @return Torrent[]
     */
    public function getTorrents(string $name): array;
}