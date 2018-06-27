<?php

namespace ConstantCondition;

class ElseIfCondition
{

    /**
     * @param int $i
     * @param \stdClass $std
     * @param Foo|Bar $union
     * @param Lorem&Ipsum $intersection
     */
    public function doFoo(int $i, \stdClass $std, $union, $intersection)
    {
        if ($i) {
        } elseif ($std) {
        }

        if ($i) {
        } elseif (!$std) {
        }

        if ($union instanceof Foo || $union instanceof Bar) {
        } elseif ($union instanceof Foo && true) {
        }

        if ($intersection instanceof Lorem && $intersection instanceof Ipsum) {
        } elseif ($intersection instanceof Lorem && true) {
        }
    }
}
