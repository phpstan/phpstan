<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;
use SomeNamespace\Consecteur;

class FooWithTrait extends FooParent
{

    use FooTrait;

    /**
     * @return Bar
     */
    public static function doSomethingStatic()
    {
    }

    /**
     * @return self[]
     */
    public function doBar(): array
    {
    }

    public function returnParent(): parent
    {
    }

    /**
     * @return parent
     */
    public function returnPhpDocParent()
    {
    }

    /**
     * @return NULL[]
     */
    public function returnNulls(): array
    {
    }

    public function returnObject(): object
    {
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Foo
     */
    public function returnPhpunitMock(): Foo
    {
    }
}
