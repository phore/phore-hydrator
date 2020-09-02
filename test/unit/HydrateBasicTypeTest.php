<?php


namespace test;

use Phore\Hydrator\Helper\PhoreTypeParser;
use PHPUnit\Framework\TestCase;

/**
 * Class DehydrateTest
 * @package test
 * @internal
 */
class HydrateBasicTypeTest extends TestCase
{


    public function testBasicTypeString()
    {
        $h = new PhoreTypeParser("string");
        self::assertEquals("a", $h->convert("a"));
    }

    public function testBasicTypeStringArray()
    {
        $h = new PhoreTypeParser("string[]");
        self::assertEquals(["a"], $h->convert(["a"]));
    }

}
