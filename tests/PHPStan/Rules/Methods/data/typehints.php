<?php

namespace TestMethodTypehints;

class FooMethodTypehints
{
    public function foo(FooMethodTypehints $foo, $bar, array $lorem): NonexistentClass
    {
    }

    public function bar(BarMethodTypehints $bar): array
    {
    }

    public function baz(...$bar): FooMethodTypehints
    {
    }

    /**
     * @param FooMethodTypehints[] $foos
     * @param BarMethodTypehints[] $bars
     * @return BazMethodTypehints[]
     */
    public function lorem($foos, $bars)
    {
    }

    /**
     * @param FooMethodTypehints[] $foos
     * @param BarMethodTypehints[] $bars
     * @return BazMethodTypehints[]
     */
    public function ipsum(array $foos, array $bars): array
    {
    }

    /**
     * @param FooMethodTypehints[] $foos
     * @param FooMethodTypehints|BarMethodTypehints[] $bars
     * @return self|BazMethodTypehints[]
     */
    public function dolor(array $foos, array $bars): array
    {
    }

    public function parentWithoutParent(parent $parent): parent
    {
    }

    /**
     * @param parent $parent
     * @return parent
     */
    public function phpDocParentWithoutParent($parent)
    {
    }
}
