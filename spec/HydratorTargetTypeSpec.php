<?php

namespace spec\Phore\Hydrator;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\Process;

class HydratorTargetTypeSpec extends ObjectBehavior
{
    function it_loads_without_php_deprecations()
    {
        $process = new Process([
            PHP_BINARY,
            "-n",
            "-d", "error_reporting=" . E_ALL,
            "-d", "display_errors=stderr",
            "-l", __DIR__ . "/../src/HydratorTargetType.php"
        ]);
        $process->run();

        if ( ! $process->isSuccessful() || $process->getErrorOutput() !== "") {
            throw new FailureException($process->getOutput() . $process->getErrorOutput());
        }
    }

    function it_defaults_to_string_when_the_definition_is_omitted()
    {
        $this->FromString()->type->shouldBe("string");
    }

    function it_accepts_explicit_null_for_both_optional_parameters()
    {
        $this->FromString(null, null)->type->shouldBe("string");
        $this->FromString("int", null)->type->shouldBe("int");
    }

    function it_resolves_relative_class_names_in_the_reflected_namespace()
    {
        $this->FromString("Example", new \ReflectionClass(self::class))
            ->type->shouldBe(__NAMESPACE__ . "\\Example");
    }

    function it_parses_nullable_types()
    {
        $type = $this->FromString("string|null");
        $type->type->shouldBe("string");
        $type->isNullable->shouldBe(true);
    }
}
