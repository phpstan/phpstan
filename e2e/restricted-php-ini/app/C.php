<?php

class C extends A
{
    use T;

    public function foo(): void
    {
    }

    public function bar(): void
    {
        var_dump($x);
    }
}
