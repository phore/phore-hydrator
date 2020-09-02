<?php


namespace Phore\Hydrator;


class HydratorTargetType
{
    const INTERNAL_TYPES = ["string", "int", "bool", "float", "array", "mixed"];

    public $type;
    public $isNullable;
    public $isArray;
    public $isObject;


    public static function FromString(string $typeDef=null, \ReflectionClass $reflectionClass=null) : self
    {
        $t = new self();
        if (empty($typeDef))
            $typeDef = "string"; // Default is string

        $parts = explode("|", $typeDef);
        if (count($parts) > 1) {
            if ($parts[1] === "null") {
                $t->isNullable = true;
            } else {
                throw new \InvalidArgumentException("Invalid type definition: '$typeDef'");
            }
            $typeDef = $parts[0];
        }

        $t->type = $typeDef;

        if (endsWith($typeDef, "[]")) {
            $t->isArray = true;
            $typeDef = $t->type = substr($typeDef, 0, -2);
        }

        if ( ! in_array($typeDef, self::INTERNAL_TYPES)) {
            $t->isObject = true;
            if ( ! startsWith($typeDef, "\\") && $reflectionClass !== null) {
                // Extend called class namespace
                if ($reflectionClass->inNamespace())
                    $typeDef = $reflectionClass->getNamespaceName() . "\\" . $typeDef;
            }
            $t->type = $typeDef;
        }
        return $t;
    }

}
