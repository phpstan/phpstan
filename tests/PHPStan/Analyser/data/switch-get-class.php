<?php

namespace SwitchGetClass;

class Foo
{

    public function doFoo()
    {
        $lorem = doFoo();

        switch (get_class($lorem)) {
            case Ipsum::class:
                break;
            case Lorem::class:
                'normalName';
                break;
            case self::class:
                'selfReferentialName';
                break;
        }
    }
}
