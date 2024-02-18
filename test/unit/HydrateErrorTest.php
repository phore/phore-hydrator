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
class ErrorClass1 {

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
class HydrateErrorTest extends TestCase
{


    public function testObjectReturnType()
    {


        self::expectException(new \Exception(), function () {
            $h = new PhoreHydrator(SettersTestClass1::class);
            $input = [
                "p1" => "v",
                "p2" => "v",
                "p3" => "v"
            ];
            $res = $h->hydrate($input);


        });


    }



}
