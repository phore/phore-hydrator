<?php

namespace spec\Phore\Hydrator;

use Phore\Hydrator\Ex\HydratorInputDataException;
use PhpSpec\ObjectBehavior;

class PhoreHydratorSpec extends ObjectBehavior
{
    function it_defaults_to_string()
    {
        $this->hydrate("a")->shouldBe("a");
    }

    function it_hydrates_nullable_basic_types()
    {
        $this->beConstructedWith("string|null");
        $this->hydrate(null)->shouldBe(null);
    }

    function it_hydrates_strings()
    {
        $this->beConstructedWith("string");
        $this->hydrate("a")->shouldBe("a");
    }

    function it_hydrates_string_arrays()
    {
        $this->beConstructedWith("string[]");
        $this->hydrate(["a"])->shouldBe(["a"]);
    }

    function it_hydrates_nested_objects_arrays_and_maps()
    {
        $this->beConstructedWith(ComplexData::class);
        $result = $this->hydrate([
            "p1" => ["val" => "v1"],
            "p2" => [["val" => "v2"]],
            "map2" => ["1" => "val1", "2" => "val2"]
        ]);

        $result->shouldHaveType(ComplexData::class);
        $result->p1->shouldHaveType(NestedData::class);
        $result->p1->val->shouldBe("v1");
        $result->p2->shouldHaveCount(1);
        $result->p2[0]->shouldHaveType(NestedData::class);
        $result->p2[0]->val->shouldBe("v2");
        $result->p3->shouldBe(null);
        $result->map2->shouldBe([1 => "val1", 2 => "val2"]);
    }

    function it_rejects_scalar_input_for_an_object_property()
    {
        $this->beConstructedWith(ComplexData::class);
        $this->shouldThrow(HydratorInputDataException::class)->duringHydrate([
            [
                "p1" => ["val" => "v1"],
                "p2" => [["val" => "v2"]],
                "p3" => "string"
            ]
        ]);
    }

    function it_hydrates_object_maps()
    {
        $this->beConstructedWith(MapData::class);
        $result = $this->hydrate(["map" => ["a" => ["val" => "abc"]]]);

        $result->map["a"]->shouldHaveType(NestedData::class);
        $result->map["a"]->val->shouldBe("abc");
    }

    function it_filters_input_using_the_magic_hydrate_method()
    {
        $this->beConstructedWith(MagicData::class);
        $this->hydrate(["p1" => "val"])->p2->shouldBe("val");
    }

    function it_hydrates_recursive_nullable_properties()
    {
        $this->beConstructedWith(RecursiveData::class);
        $result = $this->hydrate(["p1" => ["p1" => ["p1" => null]]]);

        $result->p1->shouldHaveType(RecursiveData::class);
        $result->p1->p1->shouldHaveType(RecursiveData::class);
        $result->p1->p1->p1->shouldBe(null);
    }

    function it_uses_public_properties_setters_and_magic_setters()
    {
        $this->beConstructedWith(SetterData::class);
        $result = $this->hydrate(["p1" => "v1", "p2" => "v2", "p3" => "v3"]);

        $result->shouldHaveType(SetterData::class);
        $result->p1->shouldBe("v1");
        $result->getP2()->shouldBe("v2");
        $result->getP3()->shouldBe("v3");
        $result->opts->shouldBe([]);
    }

    function it_rejects_properties_without_an_accessible_setter()
    {
        $this->beConstructedWith(InaccessibleData::class);
        $this->shouldThrow(\InvalidArgumentException::class)
            ->duringHydrate([["p1" => "v"]]);
    }
}

class NestedData
{
    /**
     * @var string
     */
    public $val;
}

class ComplexData
{
    /**
     * @var NestedData
     */
    public $p1;

    /**
     * @var NestedData[]
     */
    public $p2;

    /**
     * @var ComplexData|null
     */
    public $p3;

    /**
     * @var array<string, string>
     */
    public $map2;
}

class MapData
{
    /**
     * @var array<string, NestedData>
     */
    public $map;
}

class MagicData
{
    public $p2;

    public function __hydrate(array $input) : array
    {
        return ["p2" => $input["p1"]];
    }
}

class RecursiveData
{
    /**
     * @var RecursiveData|null
     */
    public $p1;
}

class SetterData
{
    /**
     * @var string
     */
    public $p1;

    public $opts = [];

    private $p2;
    private $p3;

    public function setP2($value)
    {
        $this->p2 = $value;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function getP2()
    {
        return $this->p2;
    }

    public function getP3()
    {
        return $this->p3;
    }
}

class InaccessibleData
{
    /**
     * @var string
     */
    private $p1;
}
