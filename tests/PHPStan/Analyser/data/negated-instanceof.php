<?php

namespace NegatedInstanceOf;

class Foo
{
    public function someMethod()
    {
        if (!$foo instanceof Foo) {
            return;
        }

        if (!$bar instanceof Bar || get_class($bar) !== get_class($otherBar)) {
            return;
        }

        if (!($lorem instanceof Lorem || get_class($lorem) === get_class($otherLorem))) { // still mixed after if
            return;
        }

        if ($dolor instanceof Dolor) { // still mixed after if
            return;
        }

        if (!(!$sit instanceof Sit)) { // still mixed after if
            return;
        }

        if ($mixedFoo instanceof Foo && doFoo()) {
            return;
        }

        if (!($mixedBar instanceof Bar) && doFoo()) {
            return;
        }

        if (!$self instanceof self) {
            return;
        }

        if (!$static instanceof static) {
            return;
        }

        die;
    }
}
