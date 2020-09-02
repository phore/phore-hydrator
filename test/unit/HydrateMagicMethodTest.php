<?php


namespace unit;


use Phore\Hydrator\Ex\HydratorInputDataException;
use Phore\Hydrator\PhoreHydrator;
use PHPUnit\Framework\TestCase;

/**
 * Class MagickMethodTestClass
 * @package unit
 * @internal
 */
class MagickMethodTestClass {

    public $p2;

    public function __hydrate(array $input) : array
    {
        return ["p2" => $input["p1"]];
    }
}


/**
 * Class HydrateMagicMethodTest
 * @package unit
 * @internal
 */
class HydrateMagicMethodTest extends TestCase
{

    public function testMagicMethodsWorks()
    {
        $h = new PhoreHydrator(MagickMethodTestClass::class);
        $ret = $h->hydrate(["p1" => "val"]);
        self::assertEquals("val", $ret->p2);
    }

}
