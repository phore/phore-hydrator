<?php


namespace test;

use Phore\Hydrator\PhoreHydrator;
use Phore\Hydrator\Mock\TestClassA;
use Phore\Hydrator\Mock\TestClassB;
use PHPUnit\Framework\TestCase;

/**
 * Class SettersTestClass1
 * @package test
 * @internal
 */
class SettersTestClass1 {

    /**
     * @var string
     */
    public $p1;

    public $opts = [];

    private $p2;

    public function setP2($val) {
        $this->p2 = $val;
    }

    private $p3;

    public function __set($name, $value)
    {

    }

}



/**
 * Class DehydrateTest
 * @package test
 * @internal
 */
class HydrateSettersTest extends TestCase
{


    public function testObjectReturnType()
    {
        $input = [
            "p1" => "v",
            "p2" => "v",
            "p3" => "v"
        ];

        $h = new PhoreHydrator(SettersTestClass1::class);
        $res = $h->hydrate($input);
        if ( ! $res instanceof SettersTestClass1)
            throw new \InvalidArgumentException();

        print_r($res);
        self::assertEquals("v", $res->p1);
    }



}
