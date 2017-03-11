<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;

class FooParent
{

    /**
     * @return static
     */
    public function doLorem()
    {
    }

    /**
     * @return static
     */
    public function doIpsum(): self
    {
    }

    /**
     * @return $this
     */
    public function doThis()
    {
        return $this;
    }

    /**
     * @return $this|null
     */
    public function doThisNullable()
    {
        return $this;
    }

    /**
     * @return $this|Bar|null
     */
    public function doThisUnion()
    {
    }
}
