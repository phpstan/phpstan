<?php

trait SayGoodBye
{
    public function sayGoodbye() : string
    {
        return null;
    }
    public function sayGoodbyeAgain() : string
    {
        return null;
    }
}
class HelloWorld
{
    use SayGoodBye;
    public function sayHello() : string
    {
        return null;
    }
    public function sayHelloAgain() : string
    {
    }
}