<?php

namespace InvalidPhpDoc;

function noDoc() : void
{
}

/**
 * No tag here.
 */
function noThrowsTag()
{
}

/**
 * @throws \Exception
 */
function singleClassThrows()
{
}

/**
 * @throws \RuntimeException Some comment.
 */
function commentedThrows()
{
}

/**
 * @throws \RuntimeException|\LogicException
 */
function unionThrows()
{
}

/**
 * @throws \Throwable&\DateTimeInterface
 */
function intersectThrows()
{
}

/**
 * @throws (\RuntimeException&\Throwable)|\TypeError
 */
function unionAndIntersectThrows()
{
}

/**
 * @throws \Undefined
 */
function undefinedThrows()
{
}

/**
 * @throws bool
 */
function scalarThrows()
{
}

/**
 * @throws \DateTimeImmutable
 */
function notThrowableThrows()
{
}

/**
 * @throws \DateTimeImmutable|\Throwable
 */
function notThrowableInUnionThrows()
{
}

/**
 * @throws \DateTimeImmutable&\IteratorAggregate
 */
function notThrowableInIntersectThrows()
{
}
