<?php

namespace InstanceOfNamespace;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Stmt\Function_;

class Foo
{
    public function someMethod()
    {
        $foo = new Function_();
        $bar = $foo;
        $baz = doFoo();

        if ($baz instanceof Foo) {
            // ...
        } else {
            while ($foo instanceof ArrayDimFetch) {
                assert($lorem instanceof Lorem);
                if ($dolor instanceof Dolor && $sit instanceof Sit) {
                    if ($static instanceof static) {
                        if ($self instanceof self) {
                            die;
                        }
                    }
                }
            }
        }
    }
}
