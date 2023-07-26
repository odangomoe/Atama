<?php


namespace Odango\Atama;


class Metadata extends \ArrayObject
{
    const INDICATORS
        = [
            "resolution"  => ['/^[0-9]+p$/i', '/^[0-9]+x[0-9]+$/i', '720', '1080', '420'],
            "source"      => ['/^dvd(?:-?(?:rip|iso))?$/i', '/^bd(?:-?(?:rip|iso))?$/i', '/^blu-?ray$/i', '/^web(:?-?(?:rip)?)?$/i'],
            "audio"       => ['/^aac(-ac3)?$/i', 'mp3', '/^flac(-ac3)?$/i'],
            "video"       => [
                'x264',
                'x265',
                'avc',
                'hevc',
                '/^h\.?264/i',
                '/^h\.?265/i',
                'xvid',
                'divx',
                'vp8',
		'vp9',
		'av1',
            ],
            "video-depth" => ['/^10-?b(it)?$/i', '/^8-?b(it)?$/i', 'hi10p'],
            "container"   => ['mp4', 'mkv', 'avi', 'webm', 'ogv'],
            "crc32"       => ['/^[a-f0-9]{8}$/i'],
            "type"        => ['batch', 'ova', 'special', 'ona'],
        ];

    // Matches group and spacer (' ' or '_')
    const GROUP_AND_SPACER_MATCHER = '~^(?:\[([^\]]+)\]|\(([^\)]+)\)|(.+) >> )([_ ])?~';
    // Matches the name of a title
//    const NAME_MATCHER = '~(?:(?:\[([^\]]+)\]|\(([^\)]+)\))(?:(?:[_ ]\[(?:[^\]]+)\]|\((?:[^\)]+)\)))*|(.+) >>)?((?:(?!\[[^\]+]\]|(\s*WEB)?\s*[0-9]+-[0-9]+|ep(isode )?[0-9]|\s[-\~]\s(?:[0-9]|season|vol|batch|special|o[nv]a)|( (Vol(ume)?\.? ?[0-9])?(\s*-\s*[0-9]+|[0-9]{2,})?(v[0-9]+)? ?)?(\(|\[|\.[a-z0-9]+$)).)+)~i';
    const NAME_MATCHER = '~(?:(?:\[([^\]]+)\]|\(([^\)]+)\))(?:(?:[_ ]\[(?:[^\]]+)\]|\((?:[^\)]+)\)))*|(.+) >>)?((?:(?!\[[^\]+]\]|(\s*WEB)?\s*[0-9]+-[0-9]+|ep(isode )?[0-9]|\sS\d+E\d+|\s[-\~]\s(?:S\d+(E\d+)?|[0-9]|season|vol|batch|special|o[nv]a)|( (Vol(ume)?\.? ?[0-9])?(\s*-\s*[0-9]+|[0-9]{2,})?(v[0-9]+)? ?)?(\(|\[|\.[a-z0-9]+$)).)+)~i';
    // Matches tags in the title e.g. [MP3] or (MP4)
    const TAG_MATCHER = '~(?:\[([^\]]+)\]|\(([^\)]+)\))~';
    // Matches the extension of a torrent e.g. .mkv or .mp4
    const EXTENSION_MATCHER = '~\.([a-z0-9]+)$~i';
    // Matches info like which EP, batch or Volume this is
//    const TYPE_INFO_MATCHER = '~(?:WEB ?)?(?: (?:(Vol(?:ume)?\.? ?([0-9]+) (?:End)?)|(?:ep)?([0-9]+(?:\.[0-9]+|[A-Z]+)?)|(batch(?: ([0-9]+(?:\.[0-9]+|[A-Z]+)?)-([0-9]+(?:\.[0-9]+|[A-Z]+)?))?|o[vn]a|special)|(([0-9]+(?:\.[0-9]+|[A-Z]+)?)-([0-9]+(?:\.[0-9]+|[A-Z]+)?))(?: complete)?|((s|season )([0-9]+)))|( ?v([0-9]+))|((?:\s+[0-9]+(?:\.[0-9]+|[A-Z]+)?)(?:\s+[-\~]\s+(?:[0-9]+(?:\.[0-9]+|[A-Z]+)?)+))(?:\s+-\s+(batch))?)+ ?(?:END ?)?(?:\[|\()~i';
    const TYPE_INFO_MATCHER = '~(?:WEB ?)?(?: (?:(Vol(?:ume)?\.? ?([0-9]+) (?:End)?)|(?:ep)?([0-9]+(?:\.[0-9]+|[A-Z]+)?)|(batch(?: ([0-9]+(?:\.[0-9]+|[A-Z]+)?)-([0-9]+(?:\.[0-9]+|[A-Z]+)?))?|o[vn]a|special)|(([0-9]+(?:\.[0-9]+|[A-Z]+)?)-([0-9]+(?:\.[0-9]+|[A-Z]+)?))(?: complete)?|((s|season )([0-9]+)(?:e(\d+))?))|( ?v([0-9]+))|((?:\s+[0-9]+(?:\.[0-9]+|[A-Z]+)?)(?:\s+[-\~]\s+(?:[0-9]+(?:\.[0-9]+|[A-Z]+)?)+))(?:\s+-\s+(batch))?)+ ?(?:END ?)?(?:\[|\()~i';
    // Matches a range for a collection e.g. 10 - 23
    const COLLECTION_RANGE_MATCHER = '~([0-9]+(?:\.[0-9]+)?) ?[-\~] ?([0-9]+(?:\.[0-9]+)?)~';
    // Matches the EP with part
    const EP_MATCHER = '~^([0-9]+(?:\.[0-9]+)?)([a-z]+)?$~i';

    static public function createFromArray($metadata)
    {
        $md = new static();
        $md->exchangeArray($metadata);

        return $md;
    }

    static public function createFromTitle($title)
    {
        $md = static::createFromArray(
            [
                'unparsed' => [],
                'type'     => 'unknown',
            ]
        );

        $normalizedTitle = $md->normalizeTitle($title);
        $md['group']     = $md->parseGroupFromTitle($normalizedTitle);
	$name = $md->parseNameFromTitle($normalizedTitle);
	$names = preg_split("/\\s+[\\|\\/]\\s+/", $name);
	$md['name'] = array_shift($names);
	$md['alternative-names'] = $names;

        $md->mergeIntoSelf($md->parseTagsFromTitle($normalizedTitle));
        $md->mergeIntoSelf($md->parseTypeInfoFromTitle($normalizedTitle));
        $md['container'] = $md->parseContainerFromTitle($normalizedTitle);

        // Mostly when there is a source and we don't know the type
        // It's a batch from that source
        if (isset($md['source']) && $md['type'] === 'unknown') {
            $md['type'] = 'batch';
        }

        $md->removeGroupFromUnparsed();

        return $md;
    }

    private function removeGroupFromUnparsed()
    {
        if ( ! isset($this['group']) || ! isset($this['unparsed'])) {
            return;
        }

        $group    = $this['group'];
        $unparsed = $this['unparsed'];

        $pos = array_search($group, $unparsed);

        if ($pos === false) {
            return;
        }

        array_splice($unparsed, $pos, 1);
        $this['unparsed'] = $unparsed;
    }

    private function mergeIntoSelf($arr)
    {
        foreach ($arr as $key => $value) {
            $this[$key] = $value;
        }
    }

    private function normalizeTitle($title)
    {
        preg_match(static::GROUP_AND_SPACER_MATCHER, $title, $match);

        if ((isset($match[4]) && $match[4] === '_') || substr_count($title, ' ') === 0) {
            $title = str_replace('_', ' ', $title);
	}

	$title = preg_replace('/\s+/', ' ', $title);

        return $title;
    }

    private function parseGroupFromTitle($title)
    {
        if (preg_match(static::GROUP_AND_SPACER_MATCHER, $title, $match)) {
            return $match[1] ?: $match[2] ?: $match[3];
        }

        return null;
    }

    private function parseNameFromTitle($title)
    {
        if ( ! preg_match(static::NAME_MATCHER, $title, $match)) {
            return null;
        }

        return trim($match[4] ?? "");
    }

    private function parseTagsFromTitle($title)
    {
        $amountMatches = preg_match_all(static::TAG_MATCHER, $title, $matches);
        $data          = [];
        $unparsed      = [];

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

        if ( ! empty($unparsed)) {
            $data['unparsed'] = $unparsed;
        }

        return $data;
    }

    private function parseTag($tag)
    {
        $try = $this->tryParseLoneTag($tag);

        if ($try !== false) {
            return $try;
        }

        $splitters = [',', '.', ' ', '-'];

        foreach ($splitters as $splitter) {
            $tags = $this->parseTagsFromSplitterTag($tag, $splitter);

            if ($tags !== false) {
                return $tags;
            }
        }

        return false;
    }

    private function tryParseLoneTag($tag)
    {
        foreach (static::INDICATORS as $name => $_) {
            if ($this->matchIndicator($name, $tag)) {
                return [$name => strtolower($tag)];
            }
        }

        if (preg_match(static::COLLECTION_RANGE_MATCHER, $tag, $match)) {
            return ["collection" => [$this->parseEpInfo($match[1]), $this->parseEpInfo($match[2])]];
        }

        return false;
    }

    private function matchIndicator($name, $value)
    {
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

    private function parseTagsFromSplitterTag($tag, $splitter)
    {
        if (strpos($tag, $splitter) === false) {
            return false;
        }

        $result = [];

        $tagBits   = explode($splitter, $tag);
        $failCount = 0;

        foreach ($tagBits as $tagBit) {
            $try = $this->tryParseLoneTag($tagBit);

            if ($try === false) {
                $failCount++;
            } else {
                $result += $try;
            }

            if ($failCount > 3) {
                return false;
            }
        }

        if (empty($result)) {
            return false;
        }

        return $result;
    }

    private function parseContainerFromTitle($title)
    {
        if (preg_match(static::EXTENSION_MATCHER, $title, $match)) {
            return $match[1];
        }

        return null;
    }

    private function parseEpInfo($ep)
    {
        if ( ! preg_match(static::EP_MATCHER, $ep, $match)) {
            // @codeCoverageIgnoreStart
            return [floatval($ep)];
            // @codeCoverageIgnoreEnd
        }

        $epParts = [floatval($match[1])];

        if ( ! empty($match[2])) {
            $epParts[] = $match[2];
        }

        return $epParts;
    }

    private function parseTypeInfoFromTitle($title)
    {
        $info = [];

        if ( ! preg_match(static::TYPE_INFO_MATCHER, $title, $match)) {
            return $info;
        }

        if ( ! empty($match[1])) {
            // If volume
            $info['type']   = 'volume';
            $info['volume'] = intval($match[2]);
        } elseif ( ! empty($match[3])
                   && /* in case a series ends with a number and has BATCH in the tags */ $this['type'] === 'unknown') {
            // If EP
            $info['type'] = 'ep';
            $info['ep']   = $this->parseEpInfo($match[3]);
        } elseif ( ! empty($match[4])) {
            // If batch or special
            if (strtolower(substr($match[4], 0, 5)) == 'batch') {
                $info['type'] = 'batch';
                if (isset($match[5])) {
                    $info['collection'] = [$this->parseEpInfo($match[5]), $this->parseEpInfo($match[6])];
                }
            } else {
                $info['type']    = 'special';
                $info['special'] = strtolower($match[4]);
            }
        } elseif ( ! empty($match[7])) {
            // If collection
            $info['type']       = 'collection';
            $info['collection'] = [$this->parseEpInfo($match[8]), $this->parseEpInfo($match[9])];
        } elseif ( ! empty($match[10])) {
            $info['type']   = 'season';
	    $info['season'] = intval($match[12]);

	    if (!empty($match[13])) {
              $info['type'] = 'ep';
	      $info['ep'] = $this->parseEpInfo($match[13]);
	    }
        } elseif ( ! empty($match[16])) {
            $parts      = preg_split('~\s*(\s+|[-\~])\s*~', trim($match[16], ' -'));
            $namesParts = explode(' ', $this['name']);
            $item       = end($namesParts);

            if ($item == $parts[0]) {
                $parts = array_slice($parts, 1);
            }

            if (count($parts) > 2) {
                $parts = array_slice($parts, -2);
            }

            if (count($parts) === 2) {
                $info['type'] = empty($match[17]) ? 'collection' : 'batch';

                $info['collection'] = [$this->parseEpInfo($parts[0]), $this->parseEpInfo($parts[1])];
            } else {
                $info['type'] = 'ep';
                $info['ep']   = $this->parseEpInfo($parts[0]);
            }
        }

        if ( ! empty($match[14])) {
            $info['version'] = intval($match[15]);
	}

	if (!isset($info['source']) && strtolower(substr($match[0], 0, 3)) === 'web') {
            $info['source'] = 'web';
	}

        return $info;
    }
}
