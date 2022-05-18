<?php


namespace test;

use Phore\Hydrator\Ex\HydratorInputDataException;
use Phore\Hydrator\Ex\InvalidStructureException;
use Phore\Hydrator\PhoreHydrator;
use Phore\Hydrator\Mock\TestClassA;
use Phore\Hydrator\Mock\TestClassB;
use PHPUnit\Framework\TestCase;

/**
 * Class Com1TestClass1
 * @package test
 * @internal
 */
class Com1TestClass1 {

    /**
     * @var Com1TestClass2
     */
    public $p1;

    /**
     * @var Com1TestClass2[]
     */
    public $p2;

    /**
     * @var Com1TestClass1|null
     */
    public $p3;


    /**
     * @var array<string, Com1TestClass2>
     */
    public $map1;
}

/**
 * Class Com1TestClass2
 * @package test
 * @internal
 */
class Com1TestClass2 {

    /**
     * @var string
     */
    public $val;
}


/**
 * Class DehydrateTest
 * @package test
 * @internal
 */
class HydrateComplexTest extends TestCase
{


    public function testObjectReturnType()
    {
        $input = [
            "p1" => [
                "val" => "v1"
            ],
            "p2" => [
                [
                    "val" => "v2"
                ]
            ]
        ];


        $res = phore_hydrate($input, Com1TestClass1::class);

        self::assertInstanceOf(Com1TestClass1::class, $res);
        self::assertInstanceOf(Com1TestClass2::class, $res->p1);
        self::assertCount(1, $res->p2);
        self::assertInstanceOf(Com1TestClass2::class, $res->p2[0]);

        self::assertEquals(null, $res->p3);
        self::assertEquals([], $res->p4);
    }


    public function testThrowsExceptionOnObjectDataOnArray()
    {
        $input = [
        "p1" => [
            "val" => "v1"
        ],
        "p2" => [
            [
                "val" => "v2"
            ]
        ],
        "p3" => "string"
        ];
        self::expectException(HydratorInputDataException::class);
        $ret = phore_hydrate($input, Com1TestClass1::class);
        print_r($ret);
    }




}
