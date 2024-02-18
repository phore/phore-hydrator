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
class HydrateRecursionDetectionTestClass1 {

    /**
     * @var HydrateRecursionDetectionTestClass1|null
     */
    public $p1;

}



/**
 * Class DehydrateTest
 * @package test
 * @internal
 */
class HydrateRecursionDetectionTest extends TestCase
{


    public function testRecursionDetection()
    {
        $input = [
            "p1" => [
                "p1" => [
                    "p1" => null
                ]
            ]
        ];

        $h = new PhoreHydrator(HydrateRecursionDetectionTestClass1::class);
        $res = $h->hydrate($input);

    }



}
