<?php

namespace SwitchInstanceOfNot;

$foo = doFoo();

switch (false) {
    case $foo instanceof Foo:
        die;
}
