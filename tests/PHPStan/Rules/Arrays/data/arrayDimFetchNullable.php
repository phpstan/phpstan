<?php

function (array $array, ?array $nullableArray) {
	$array['test'];
	$nullableArray['test'];
	$nullableArray[] = 'test';
};
