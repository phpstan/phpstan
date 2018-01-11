<?php

namespace ExtendingKnownClassWithCheck;

if (class_exists(Bar::class)) {
    class Foo extends Bar
    {}
} else {
    class Foo extends \Exception
    {}
}
