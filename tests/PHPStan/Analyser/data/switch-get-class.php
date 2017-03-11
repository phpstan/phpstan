<?php

namespace SwitchGetClass;

$lorem = doFoo();

switch (get_class($lorem)) {
    case Ipsum::class:
        break;
    case Lorem::class:
        die;
        break;
}
