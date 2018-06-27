<?php

namespace TraitUseCase;

trait FooTrait
{

}

class Foo
{

    use FOOTrait;
}

class Bar
{

    use FooTrait;
}
