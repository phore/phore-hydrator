<?php


namespace test;

use Phore\Hydrator\PhoreHydrator;
use PHPUnit\Framework\TestCase;

/**
 * Class DehydrateTest
 * @package test
 * @internal
 */
class HydrateBasicTypeTest extends TestCase
{

    public function testDefaultTypeIsString()
    {
        $h = new PhoreHydrator();
        self::assertEquals("a", $h->hydrate("a"));
    }

    public function testNullableBasicType()
    {
        $h = new PhoreHydrator("string|null");
        self::assertEquals(null, $h->hydrate(null));
    }

    public function testBasicTypeString()
    {
        $h = new PhoreHydrator("string");
        self::assertEquals("a", $h->hydrate("a"));
    }

    public function testBasicTypeStringArray()
    {
        $h = new PhoreHydrator("string[]");
        self::assertEquals(["a"], $h->hydrate(["a"]));
    }

}
