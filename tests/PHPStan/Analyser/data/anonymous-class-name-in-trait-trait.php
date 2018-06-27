<?php

namespace AnonymousClassNameInTrait;

trait FooTrait
{

    public function doFoo()
    {
        new class () {

            public function doFoo()
            {
                die;
            }
        };
    }
}
