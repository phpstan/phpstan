<?php

class FooWithoutNamespace
{

    public function misleadingBoolReturnType(): boolean
    {
        return true;
        return 1;
        return new boolean();
    }

    public function misleadingIntReturnType(): integer
    {
        return 1;
        return true;
        return new integer();
    }

    public function misleadingMixedReturnType(): mixed
    {
        return 1;
        return true;
        return new mixed();
    }
}
