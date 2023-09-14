<?php


/**
 * Hydrate input array struct into an object
 *
 * <examples>
 * $obj = phore_hydrator($arrayInput, TargetClass::class);
 * </examples>
 *
 * @template T
 * @param $input
 * @param class-string<T> $targetClassName
 * @param bool $strict
 * @return T
 * @throws \Phore\Hydrator\Ex\HydratorInputDataException
 * @throws \Phore\Hydrator\Ex\InvalidStructureException
 */
function phore_hydrate ($input, string $targetClassName, bool $strict = true) {
    $hydrator = new \Phore\Hydrator\PhoreHydrator($targetClassName);
    return $hydrator->hydrate($input, $strict);
}

/**
 * Dehydrate based on the file extension (yaml, yml or json)
 * 
 * @param $inputFile
 * @param string $targetClassName
 * @param bool $strict
 * @return array|bool|float|int|object|string|null
 */
function phore_hydrate_file ($inputFile, string $targetClassName, bool $strict = true) {
    $data = file_get_contents($inputFile);
    if ($data === false)
        throw new \InvalidArgumentException("Cannot read file '$inputFile'");
    
    // Check file extension: json or yaml|yml
    $ext = strtolower(pathinfo($inputFile, PATHINFO_EXTENSION));
    if ($ext === "json") {
        $input = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE)
            throw new \InvalidArgumentException("Cannot decode json file '$inputFile': " . json_last_error_msg());
    } elseif ($ext === "yaml" || $ext === "yml") {
        $input = yaml_parse($data);
        if ($input === false)
            throw new \InvalidArgumentException("Cannot decode yaml file '$inputFile': " . yaml_last_error_msg());
    } else {
        throw new \InvalidArgumentException("Cannot decode file '$inputFile': Unknown file extension '$ext'");
    }
    
    
    $hydrator = new \Phore\Hydrator\PhoreHydrator($targetClassName);
    try {
        return $hydrator->hydrate($input, $strict);
    } catch (\Phore\Hydrator\Ex\HydratorInputDataException $e) {
        throw new \InvalidArgumentException("Cannot hydrate file '$inputFile': " . $e->getMessage(), 0, $e);
    } catch (\Phore\Hydrator\Ex\InvalidStructureException $e) {
        throw new \InvalidArgumentException("Cannot hydrate file '$inputFile': " . $e->getMessage(), 0, $e);
    }
    
}