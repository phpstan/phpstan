<?php

namespace InstanceOfNamespace;

if ($foo instanceof Foo) {
} elseif ($foo instanceof Bar) {
} elseif ($foo instanceof self) {
} elseif ($foo instanceof $bar) {
} elseif ($foo instanceof FOO) {
} elseif ($foo instanceof parent) {
} elseif ($foo instanceof self) {
}
