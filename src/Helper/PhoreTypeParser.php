<?php


namespace Phore\Hydrator\Helper;


class PhoreTypeParser
{

    const INTERNAL_TYPES = ["string", "int", "bool", "float", "array", "mixed"];

    private $isArray = false;
    private $isObject = false;
    private $type = null;

    /**
     * PhoreTypeParser constructor.
     *
     * Parameter 1 specifies the desired types
     * - Internal types like `string`, `int`
     * - Array types like `string[]` or `ìnt[]`
     * - Objects like `\ns1\class1`
     * - Object Array like `\ns1\class1[]`
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;

        if (endsWith($type, "[]")) {
            $this->isArray = true;
            $type = $this->type = substr($type, 0, -2);
        }

        if ( ! in_array($type, self::INTERNAL_TYPES)) {
            $this->isObject = true;
            $this->type = $type;
        }
    }


    private function getInternalVal($input)
    {
        switch ($this->type) {
            case "string":
                return (string)$input;
            case "int":
                return (int)$input;
            case "bool":
                return (bool)$input;
            case "float":
                return (float)$input;
            case "mixed":
                return $input;
            default:
                throw new \InvalidArgumentException("Unrecognized internal type '{$this->type}'");
        }
    }


    private function getObjectCast (string $className, $value, array $path)
    {
        $ref = new \ReflectionClass($className);
        $obj = $ref->newInstanceWithoutConstructor();

        $ref = new \ReflectionObject($obj);
        $defaultProperties = $ref->getDefaultProperties();

        foreach ($ref->getProperties() as $prop) {
            $value = "";
            $type = phore_parse_annotation($prop->getDocComment(), "@var");
            if ($type === null) {
                continue;
            }
            if ( ! is_array($value) || ! array_key_exists($prop->getName(), $value)) {
                throw new \InvalidArgumentException("Property '{$prop->getName()}' missing in {$this->getPathStr($path)}");
            }
            $propType = new self($type);

            $prop->setValue($obj, $propType->convert($value[$prop->getName()], $path + [$prop->getName()]));
        }
        return $obj;
    }


    private function getPathStr(array $path)
    {
        return implode(".", $path);
    }

    public function convert($input, array $path=["@"])
    {
        if ( ! $this->isObject && ! $this->isArray) {
            return $this->getInternalVal($input);
        }

        if ($this->isArray) {
            $ret = [];
            if ( ! is_array($input))
                throw new \InvalidArgumentException("Invalid array input at {$this->getPathStr($path)}");
            $subtype = new self($this->type);
            foreach ($input as $key => $row) {
                $ret[] = $subtype->convert($row, $path + [$key]);
            }
            return $ret;
        }

        if ( ! class_exists($this->type))
            throw new \InvalidArgumentException("Class '$this->type' does not exist.");

        return $this->getObjectCast($this->type, $input, $path);
    }



}
