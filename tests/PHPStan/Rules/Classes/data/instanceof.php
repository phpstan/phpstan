<?php

namespace InstanceOfNamespace;

if ($foo instanceof Foo) {
} elseif ($foo instanceof Bar) {
} elseif ($foo instanceof self) {
} elseif ($foo instanceof $bar) {
}
