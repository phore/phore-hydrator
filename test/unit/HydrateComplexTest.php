<?php


namespace test;

use Phore\Hydrator\Helper\PhoreTypeParser;
use Phore\Hydrator\Mock\TestClassB;
use PHPUnit\Framework\TestCase;

/**
 * Class DehydrateTest
 * @package test
 * @internal
 */
class HydrateComplexTest extends TestCase
{


    public function testObjectReturnType()
    {
        $h = new PhoreTypeParser(TestClassB::class);
        $res = $h->convert(["propA" => "val1", "propB" => "val2"]);
        print_r($res);
        self::assertEquals("a", $res);
    }



}
