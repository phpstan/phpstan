<?php // lint >= 7.1

namespace VoidNamespace;

class Foo
{
    public function doFoo(): void
    {
        die;
    }

    /**
     * @return void
     */
    public function doBar(): void
    {
    }

    /**
     * @return int
     */
    public function doConflictingVoid(): void
    {
    }
}
