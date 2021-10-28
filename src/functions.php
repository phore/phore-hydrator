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
