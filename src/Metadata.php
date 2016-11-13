<?php


namespace Odango\Atama;


class Metadata extends \ArrayObject
{
    const INDICATORS = [
        "resolution" => ['/^[0-9]+p$/i','/^[0-9]+x[0-9]+$/i', '720','1080','420'],
        "source" => ['/^dvd(-?rip)?$/i', '/^bd(?:-?rip)?$/i', '/^blu-?ray$/i'],
        "audio" => ['/^aac(-ac3)?$/i', 'mp3', '/^flac(-ac3)?$/i'],
        "video" => ['x264', 'x265', 'avc', 'hevc', '/^h\.?264/i', '/^h\.?265/i'],
        "video-depth" => ['/^10b(it)?$/i', '/^8b(it)?$/i', 'hi10p'],
        "container" => ['mp4','mkv', 'avi'],
        "crc32" => ['/^[a-f0-9]{8}$/i'],
        "type" => ['batch', 'ova', 'special', 'ona'],
    ];

    // Matches group and spacer (' ' or '_')
    const GROUP_AND_SPACER_MATCHER = '~^(?:\[([^\]]+)\]|\(([^\)]+)\)|(.+) >> )([_ ])?~';
    // Matches the name of a title
    const NAME_MATCHER = '~(?:\[([^\]]+)\]|\(([^\)]+)\)|(.+) >>)?((?:(?!\[[^\]+]\]| [-\~] (?:[0-9]|vol|batch|special|o[nv]a)|( (Vol\. ?)?[0-9]*(-[0-9]+)?(v[0-9]+)? ?)?(\(|\[|\.[a-z0-9]+$)).)+)~i';
    // Matches tags in the title e.g. [MP3] or (MP4)
    const TAG_MATCHER = '~(?:\[([^\]]+)\]|\(([^\)]+)\))~';
    // Matches the extension of a torrent e.g. .mkv or .mp4
    const EXTENSION_MATCHER = '~\.([a-z0-9]+)$~i';
    // Matches info like which EP, batch or Volume this is
    const TYPE_INFO_MATCHER = '~ (?:(Vol\.? ?([0-9]+) (?:End)?)|([0-9]+(?:\.[0-9]+)?)|(batch(?: (\d+)-(\d+))?|o[vn]a|special)|(([0-9]+)-([0-9]+))(?: complete)?|((s|season )([0-9]+)))?( ?v([0-9]+))? ?(\[|\()~i';
    // Matches a range for a collection e.g. 10 - 23
    const COLLECTION_RANGE_MATCHER = '~([0-9]+(?:\.[0-9]+)?) ?- ?([0-9]+(?:\.[0-9]+)?)~';

    static public function createFromArray($metadata) {
        $md = new static();
        $md->exchangeArray($metadata);
        return $md;
    }

    static public function createFromTitle($title) {
        $md = static::createFromArray([
           'unparsed' => []
        ]);

        $normalizedTitle = $md->normalizeTitle($title);
        $md['group'] = $md->parseGroupFromTitle($normalizedTitle);
        $md['name'] = $md->parseNameFromTitle($normalizedTitle);
        $md->mergeIntoSelf($md->parseTagsFromTitle($normalizedTitle));
        $md->mergeIntoSelf($md->parseTypeInfoFromTitle($normalizedTitle));
        $md['container'] = $md->parseContainerFromTitle($normalizedTitle);

        $md->removeGroupFromUnparsed();

        return $md;
    }

    private function removeGroupFromUnparsed() {
        if (!isset($this['group']) || !isset($this['unparsed'])) {
            return;
        }

        $group = $this['group'];
        $unparsed = $this['unparsed'];

        $pos = array_search($group, $unparsed);

        if ($pos === false) {
            return;
        }

        array_splice($unparsed, $pos, 1);
        $this['unparsed'] = $unparsed;
    }

    private function mergeIntoSelf($arr) {
        foreach ($arr as $key => $value) {
            $this[$key] = $value;
        }
    }

    private function normalizeTitle($title) {
        if (!preg_match(static::GROUP_AND_SPACER_MATCHER, $title, $match)) {
            return $title;
        }

        if (isset($match[4]) && $match[4] === '_') {
            return str_replace('_', ' ', $title);
        }

        return $title;
    }

    private function parseGroupFromTitle($title) {
        if (preg_match(static::GROUP_AND_SPACER_MATCHER, $title, $match)) {
            return $match[1] ?: $match[2] ?: $match[3];
        }

        return null;
    }

    private function parseNameFromTitle($title)
    {
        if (!preg_match(static::NAME_MATCHER, $title, $match)) {
            return null;
        }

        if (isset($match[4])) {
            return trim($match[4]);
        }

        return null;
    }

    private function parseTagsFromTitle($title) {
        $amountMatches = preg_match_all(static::TAG_MATCHER, $title, $matches);
        $data = [];
        $unparsed = [];

        // If no matches return quickly
        if ($amountMatches < 1) {
            return [];
        }

        for ($i = 0; $i < $amountMatches; $i++) {
            $tag = $matches[1][$i] ?: $matches[2][$i];

            $parsed = $this->parseTag($tag);

            if ($parsed === false) {
                $unparsed[] = $tag;
            } else {
                $data += $parsed;
            }
        }

        if (!empty($unparsed)) {
            $data['unparsed'] = $unparsed;
        }

        return $data;
    }

    private function parseTag($tag) {
        $splitters = [',', '.', ' ', '-'];

        foreach ($splitters as $splitter) {
            $tags = $this->parseTagsFromSplitterTag($tag, $splitter);

            if ($tags !== false) {
                return $tags;
            }
        }

        $try = $this->tryParseLoneTag($tag);

        if ($try === false) {
            return false;
        }

        return $try;
    }

    private function tryParseLoneTag($tag) {
        foreach (static::INDICATORS as $name => $_) {
            if ($this->matchIndicator($name, $tag)) {
                return [ $name => strtolower($tag) ];
            }
        }

        if (preg_match(static::COLLECTION_RANGE_MATCHER, $tag, $match)) {
            return [ "collection" => [floatval($match[1]), floatval($match[2])] ];
        }

        return false;
    }

    private function matchIndicator($name, $value) {
        $matchers = static::INDICATORS[$name];

        foreach ($matchers as $matcher) {
            if ($matcher[0] === '/') {
                $result = preg_match($matcher, strtolower($value)) > 0;
            } else {
                $result = strtolower($matcher) === strtolower($value);
            }

            if ($result) {
                return true;
            }
        }

        return false;
    }

    private function parseTagsFromSplitterTag($tag, $splitter) {
        if (strpos($tag, $splitter) === false) {
            return false;
        }

        $result = [];

        $tagBits = explode($splitter, $tag);
        $failCount = 0;

        foreach ($tagBits as $tagBit) {
            $try = $this->tryParseLoneTag($tagBit);

            if ($try === false) {
                $failCount++;
            } else {
                $result += $try;
            }

            if ($failCount > 2) {
                return false;
            }
        }

        if (empty($result)) {
            return false;
        }

        return $result;
    }

    private function parseContainerFromTitle($title) {
        if(preg_match(static::EXTENSION_MATCHER, $title, $match)) {
            return $match[1];
        }

        return null;
    }

    private function parseTypeInfoFromTitle($title) {
        $info = [];

        if (!preg_match(static::TYPE_INFO_MATCHER, $title, $match)) {
            return $info;
        }

        if (!empty($match[1])) {
            // If volume
            $info['type'] = 'volume';
            $info['volume'] = intval($match[2]);
        } else if (!empty($match[3]) && /* in case a series ends with a number and has BATCH in the tags */ !isset($this['type'])) {
            // If EP
            $info['type'] = 'ep';
            $info['ep'] = floatval($match[3]); // floatval for special ep numbering e.g. 10.5
        } else if (!empty($match[4])) {
            // If batch or special
            if (substr(strtolower($match[4]), 0, 5) == 'batch') {
                $info['type'] = 'batch';
                if (isset($match[5])) {
                    $info['collection'] = [intval($match[5]), intval($match[6])];
                }
            } else {
                $info['type'] = 'special';
                $info['special'] = strtolower($match[4]);
            }
        } else if (!empty($match[7])) {
            // If collection
            $info['type'] = 'collection';
            $info['collection'] = [intval($match[8]), intval($match[9])];
        } else if (!empty($match[9])) {
            $info['type'] = 'season';
            $info['season'] = intval($match[11]);
        }

        if (!empty($match[13])) {
            $info['version'] = intval($match[14]);
        }

        return $info;
    }
}