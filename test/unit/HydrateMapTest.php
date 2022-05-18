<?php

namespace test;

use PHPUnit\Framework\TestCase;

class TestC2 {
    /**
     * @var string
     */
    public $name;
}

class TestC1 {
    /**
     * @var array<string, TestC2>
     */
    public $map;
}

class HydrateMapTest extends TestCase
{

    public function testObjectMap() {
        $input = [
            "map" => [
                "a" => [
                    "name" => "abc"
                ]
            ]
        ];

        $r = phore_hydrate($input, TestC1::class);
        self::assertEquals(TestC2::class, get_class($r->map["a"]));

    }

}
