<?php


namespace Phore\Hydrator;


use Phore\Hydrator\Ex\HydratorInputDataException;
use Phore\Hydrator\Ex\InvalidStructureException;
use Phore\Hydrator\Ex\UnrecognizedKeyException;


class PhoreHydrator
{



    public $options = [
        "strict_arrays" => true,
        "ignore_invalid_input_keys" => false
    ];

    /**
     * @var HydratorTargetType
     */
    private $type;

    /**
     * PhoreTypeParser constructor.
     *
     * Parameter 1 specifies the desired types
     * - Internal types like `string`, `int`
     * - Array types like `string[]` or `ìnt[]`
     * - Objects like `\ns1\class1`
     * - Object Array like `\ns1\class1[]`
     *
     * @param HydratorTargetType|string $type
     */
    public function __construct($targetType=null)
    {

        if ( ! $targetType instanceof HydratorTargetType)
            $targetType = HydratorTargetType::FromString($targetType);
        $this->type = $targetType;
    }


    private function getInternalVal($input)
    {
        if ($this->type->isNullable && $input === null)
            return null;
        switch ($this->type->type) {
            case "string":
                return (string)$input;
            case "int":
                return (int)$input;
            case "bool":
                return (bool)$input;
            case "float":
                return (float)$input;
            case "array":
                return (array)$input;
            case "mixed":
                return $input;
            default:
                throw new \InvalidArgumentException("Unrecognized internal type '{$this->type->type}'");
        }
    }

    private function getTypeFromDocComment(string $docComment, \ReflectionClass $currentClassReflection) : ?HydratorTargetType
    {
        $type = phore_parse_annotation($docComment, "@var");
        return HydratorTargetType::FromString($type, $currentClassReflection);
    }

    private function setObjectPropertyValue(string $propName, $value, $targetObject)
    {
        $reflectionObject = new \ReflectionObject($targetObject);

        // If there is a setter Method (setPropName) - use this
        $setterName = "set" . ucfirst($propName);
        if ($reflectionObject->hasMethod($setterName) && $reflectionObject->getMethod($setterName)->isPublic()) {
            $targetObject->$setterName($value);
            return;
        }

        // If the property is public: set it directly
        if ($reflectionObject->hasProperty($propName) && $reflectionObject->getProperty($propName)->isPublic()) {
            $reflectionObject->getProperty($propName)->setValue($targetObject, $value);
            return;
        }

        // If there is a magic __set() - use this
        if ($reflectionObject->hasMethod("__set")) {
            $targetObject->__set($propName, $value);
            return;
        }
        throw new \InvalidArgumentException("No setter available for '$propName' on object " . get_class($targetObject));
    }


    /**
     * This method tries to cast the Input array in parameter 2
     * into a instance of parameter 1 specified class.
     *
     * It will parse the doc-comment of each property to determine
     * the type.
     *
     * @param string $className
     * @param $input
     * @param array $path
     * @return object
     * @throws \ReflectionException
     */
    private function getObjectCast (string $className, $input, array $path, array &$errors)
    {
        // Create a new instance of the object without the constructor
        $refClass = new \ReflectionClass($className);
        $obj = $refClass->newInstanceWithoutConstructor();

        $ref = new \ReflectionObject($obj);
        $defaultProperties = $ref->getDefaultProperties();

        if ( ! is_array($input)) {
            // Return null if input data is not a valid array

            throw new InvalidStructureException($path, $ref->getName() , $input);
        }

        if ($ref->hasMethod("__hydrate") && $ref->getMethod("__hydrate")->isPublic()) {
            if ((string)$ref->getMethod("__hydrate")->getReturnType() !== "array")
                throw new \InvalidArgumentException("Return type of __hydrate method must be array in " . $ref->getName());
            // Filter Data with object's __hydrate() method
            $input = $obj->__hydrate($input);
        }

        $propertiesParsed = [];
        foreach ($ref->getProperties() as $prop) {
            $curPropName = $prop->getName();
            $curPath = $path;
            $curPath[] = $curPropName;

            $propertiesParsed[] = $curPropName;

            $targetType = $this->getTypeFromDocComment($prop->getDocComment(), $refClass);

            if ( ! array_key_exists($curPropName, $input)) {
                // Check default Properties. If they exist: Ignore the missing property
                if (isset ($defaultProperties[$prop->getName()]))
                    continue;

                $typeString = $targetType->type;
                if ($targetType->isMap)
                    $typeString = "array<string, {$targetType->type}>";
                if ($targetType->isArray)
                    $typeString = "{$targetType->type}[]";
                if ( ! $targetType->isNullable)
                    throw new InvalidStructureException($curPath, $typeString, null);
                continue;
            }
            $subPropTypeParser = new self($targetType);
            $subPropTypeParser->options = $this->options;
            try {
                if ($input[$curPropName] === null && $targetType->isNullable)
                    continue;
                $value = $subPropTypeParser->convert($input[$curPropName], $curPath, $errors);
                if ($value === null && ! $targetType->isNullable) {
                    $errors[] = new InvalidStructureException($curPath, $this->type, $value);
                }
                $this->setObjectPropertyValue($prop->getName(), $value, $obj);
            } catch (InvalidStructureException $e) {
                $errors[] = $e;
                continue;
            }
        }
        if ( ! $this->options["ignore_invalid_input_keys"]) {
            // Check for keys, that are not part of the object structure
            foreach (array_keys($input) as $key) {
                if (!in_array($key, $propertiesParsed)) {
                    $errors[] = new UnrecognizedKeyException($path, $key);
                }
            }
        }
        return $obj;
    }


    private function getPathStr(array $path)
    {
        return implode(".", $path);
    }

    public function convert($input, array $path, array &$errors)
    {
        if ( ! $this->type->isObject && ! $this->type->isArray && ! $this->type->isMap) {
            return $this->getInternalVal($input);
        }

        if ($this->type->isArray) {
            $ret = [];
            if ( ! is_array($input))
                throw new InvalidStructureException($path, "array", $input);
            $subtype = new self(HydratorTargetType::FromString($this->type->type));
            foreach ($input as $key => $row) {
                $curPath = $path;
                $curPath[] = $key;
                if ($this->options["strict_arrays"] && ! is_int($key))
                    throw new InvalidStructureException($path, "array", null,"map/invalid key: '$key' (strict_arrays: true)");
                $ret[] = $subtype->convert($row, $curPath, $errors);
            }
            return $ret;
        }

        if ($this->type->isMap) {

            $ret = [];
            if ( ! is_array($input))
                throw new InvalidStructureException($path, "array<string, T>", $input);
            $subtype = new self(HydratorTargetType::FromString($this->type->type));
            foreach ($input as $key => $row) {
                $curPath = $path;
                $curPath[] = $key;
                if ($this->options["strict_arrays"] && ! is_string($key) && ! is_int($key))
                    throw new InvalidStructureException($path, "array<string, {$this->type->type}>", null,"invalid key: '$key' (strict_arrays: true)");
                $ret[$key] = $subtype->convert($row, $curPath, $errors);
            }
            return $ret;
        }

        if ( ! class_exists($this->type->type))
            throw new \InvalidArgumentException("Class '{$this->type->type}' does not exist. (Path: " . $this->getPathStr($path) . "')");

        return $this->getObjectCast($this->type->type, $input, $path, $errors);
    }


    private function buildException (array $errors) : ?HydratorInputDataException
    {
        if (count ($errors) === 0)
            return null;

        $msg = "Hydration failed with " . count ($errors) . " errors:";
        foreach ($errors as $error)
            $msg .= "\n- " . $error->getMessage();
        $ex = new HydratorInputDataException($msg);
        foreach ($errors as $error)
            $ex->addException($error);
        return $ex;
    }

    /**
     * @param $input
     * @return array|bool|float|int|object|string|null
     * @throws InvalidStructureException
     * @throws HydratorInputDataException
     */
    public function hydrate($input, bool $strict = true)
    {
        $errors = [];
        $data = $this->convert($input, ["@"], $errors);

        $e = $this->buildException($errors);
        if ($e !== null)
            throw $e;
        return $data;
    }



}
