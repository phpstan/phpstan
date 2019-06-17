<?php

namespace TemplateTypeBound;

/**
 * @template T of int|float
 * @param T $a
 */
function a($a): void {
}

/**
 * @template T of \DateTime
 * @template U of \DateTime|\DateTimeImmutable
 * @param T $a
 * @param U $b
 */
function b($a, $b): void {
}

