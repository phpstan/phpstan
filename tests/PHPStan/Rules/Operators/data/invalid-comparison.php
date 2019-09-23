<?php

function (stdClass $ob, $a)
{
    $ob == $a;
    $ob != $a;
    $ob < $a;
    $ob > $a;
    $ob <= $a;
    $ob >= $a;
    $ob <=> $a;
};

function (stdClass $ob, int $n) {
    $ob == $n;
    $ob != $n;
    $ob < $n;
    $ob > $n;
    $ob <= $n;
    $ob >= $n;
    $ob <=> $n;
};

function (stdClass $ob, ?float $n) {
    $ob == $n;
    $ob < $n;
};

function (stdClass $ob, string $str) {
    $ob == $str;
    $ob < $str;
};

function (string $str, int $n) {
    $str == $n;
    $str < $n;
};

function (stdClass $ob, callable $fn) {
    /** @var int|float|null $n */
    $n = $fn();

    $ob == $n;
    $ob < $n;
};

function (stdClass $ob) {
    $ob == 1;
    $ob < 1;
};

function (stdClass $ob, callable $fn) {
    /** @var int|stdClass $a */
    $a = $fn();

    $ob == $a;
    $ob < $a;
};

function (array $ob, int $n) {
	$ob == $n;
	$ob != $n;
	$ob < $n;
	$ob > $n;
	$ob <= $n;
	$ob >= $n;
	$ob <=> $n;
};

function (array $ob, ?float $n) {
	$ob == $n;
	$ob < $n;
};

function (array $ob, string $str) {
	$ob == $str;
	$ob < $str;
};

function (array $ob, callable $fn) {
	/** @var int|float|null $n */
	$n = $fn();

	$ob == $n;
	$ob < $n;
};

function (array $ob) {
	$ob == 1;
	$ob < 1;
};

function (array $ob, callable $fn) {
	/** @var int|array $a */
	$a = $fn();

	$ob == $a;
	$ob < $a;
};

/**
 * @param int[] $a
 * @param string[] $b
 */
function (array $a, array $b) {
	$a == $b;
	$a < $b;
};
