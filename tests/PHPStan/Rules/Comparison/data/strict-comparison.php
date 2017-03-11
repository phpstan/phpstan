<?php

namespace StrictComparison;

class Foo
{
    public function doFoo()
    {
        1 === 1;
        1 === '1'; // wrong
        1 !== '1'; // wrong
        doFoo() === doBar();
        1 === null;
        (new Bar()) === 1; // wrong

        /** @var Foo[]|Collection|bool $unionIterableType */
        $unionIterableType = doFoo();
        1 === $unionIterableType;
        false === $unionIterableType;
        $unionIterableType === [new Foo()];
        $unionIterableType === new Collection();

        /** @var bool $boolean */
        $boolean = doFoo();
        true === $boolean;
        false === $boolean;
        $boolean === true;
        $boolean === false;
        true === false;
        false === true;

        $foo = new self();
        $this === $foo;

        $trueOrFalseInSwitch = false;
        switch ('foo') {
            case 'foo':
                $trueOrFalseInSwitch = true;
                break;
        }
        if ($trueOrFalseInSwitch === true) {
        }
    }
}
