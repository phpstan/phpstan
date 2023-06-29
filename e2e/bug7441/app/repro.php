<?php

// FooOriginal class is defined in eval() in preload.php
class Foo extends FooOriginal
{
    public function save($saveMode = false): void
    {
    }
}

$foo = new Foo();
