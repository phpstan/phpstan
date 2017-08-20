<?php // lint >= 7.1

namespace CatchUnion;

try {

} catch (FooException | BarException $e) {
	die;
}
