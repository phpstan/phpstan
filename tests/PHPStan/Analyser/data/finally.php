<?php

try {

} catch (\FooException $e) {
	$integerOrString = 1;
} catch (\BarException $e) {
	$integerOrString = 'foo';
} finally {
	die;
}
