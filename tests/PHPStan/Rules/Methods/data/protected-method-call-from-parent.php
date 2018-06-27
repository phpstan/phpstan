<?php

namespace ProtectedMethodCallFromParent;

class ParentClass
{
    public function test()
    {
        $a = new ChildClass();
        $a->onChild();
    }
}


class ChildClass extends ParentClass
{
    protected function onChild()
    {
    }
}
