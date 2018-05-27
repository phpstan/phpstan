<?php

function (?string $nullableString, ?int $nullableInt) {
	$nullableString();
	$nullableInt();

	$null = null;
	$null();
};
