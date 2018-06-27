<?php

namespace TypeInCommentOnForeach;

/** @var mixed[] $values */
$values = [];

/** @var string $value */
foreach ($values as &$value) {
    die;
}
