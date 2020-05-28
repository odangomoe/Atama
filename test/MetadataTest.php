<?php


namespace Odango\Atama\Test;


use Odango\Atama\Metadata;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{

    /**
     * @dataProvider metadataTitleProvider
     *
     * @param $title    string
     * @param $metadata array
     */
    public function testTitleParsing($title, $metadata)
    {
        $md = Metadata::createFromTitle($title);
        foreach ($metadata as $key => $value) {
            $this->assertArrayHasKey($key, $md, "{$title} => {$key} ".$this->getFlatRepresentationOfArray($md));
            if (isset($md[$key])) {
                $this->assertRecursive($value, $md[$key], [$key], $title, $md);
            }
        }
    }

    private function assertRecursive($expected, $actual, $path, $message, $src)
    {
        $pathStr = implode('/', $path);
        $msg     = "{$message} =>  {$pathStr} ".$this->getFlatRepresentationOfArray($src);
        if (is_array($actual) && is_array($expected)) {
            $this->assertSameSize($expected, $actual, $msg);
            $expectedKeys = array_keys($expected);
            $actualKeys   = array_keys($actual);
            sort($expectedKeys);
            sort($actualKeys);

            for ($i = 0; $i < count($actualKeys); $i++) {
                $expectedKey = $expectedKeys[$i];
                $actualKey   = $actualKeys[$i];

                $this->assertSame($expectedKey, $actualKey, $msg);
                $this->assertRecursive(
                    $expected[$expectedKey],
                    $actual[$actualKey],
                    array_merge($path, [$actualKey]),
                    $message,
                    $src
                );
            }
        } else {
            $this->assertSame($expected, $actual, $msg);
        }
    }

    public function getFlatRepresentationOfArray($arr)
    {
        return json_encode($arr);
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
        $data = json_decode(file_get_contents(__DIR__.'/data/metadata-title.json'), true);
        $set  = [];

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
