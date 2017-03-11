<?php declare(strict_types = 1);

namespace AnalyseTraits;

class Foo
{
    use FooTrait;

    public function doFoo()
    {
    }

    public function conflictingMethodWithDifferentArgumentNames(string $input)
    {
    }
}
