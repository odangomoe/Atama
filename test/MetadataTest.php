<?php


namespace Odango\Atama\Test;


use Odango\Atama\Metadata;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{

    /**
     * @dataProvider metadataTitleProvider
     * @param $title string
     * @param $metadata array
     */
    public function testTitleParsing($title, $metadata)
    {
        $md = Metadata::createFromTitle($title);

        foreach ($metadata as $key => $value) {
            $this->assertArrayHasKey($key, $md, "{$title} => {$key} " . $this->getFlatRepresentationOfArray($md));
            if (isset($md[$key])) {
                $this->assertEquals($value, $md[$key], "{$title} => {$key} " . $this->getFlatRepresentationOfArray($md));
            }
        }
    }

    public function getFlatRepresentationOfArray($arr) {
        $str = "A(";

        foreach ($arr as $key => $value) {
            $str .= "({$key})->";

            if (is_array($value)) {
                $str .= $this->getFlatRepresentationOfArray($value);
            } elseif (is_string($value)) {
                $str .= "'{$value}'";
            } else {
                $str .= $value;
            }

            $str .= ' ';
        }

        return $str . ")";
    }

    public function testArrayRetrieval()
    {
        $meta = Metadata::createFromArray(["data" => "test"]);
        $this->assertArrayHasKey("data", $meta);
        $this->assertArrayNotHasKey("non-existent-key", $meta);
        $this->assertSame("test", $meta["data"]);
    }

    public function metadataTitleProvider()
    {
        $data =  json_decode(file_get_contents(__DIR__ . '/data/metadata-title.json'), true);
        $set = [];

        foreach ($data as $title => $metadata) {
            if (isset($metadata['should_fail']) && $metadata['should_fail']) {
                // These are only in the data set for commenting purposes
                continue;
            }

            unset($metadata['comment']);

            $set[$title] = [$title, $metadata];
        }

        return $set;
    }


}
