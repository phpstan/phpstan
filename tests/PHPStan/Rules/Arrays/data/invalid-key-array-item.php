<?php

namespace InvalidKeyArrayItem;

$a = [
	'foo',
	1 => 'aaa',
	'1' => 'aaa',
	null => 'aaa',
	new \DateTimeImmutable() => 'aaa',
	[] => 'bbb',
];
