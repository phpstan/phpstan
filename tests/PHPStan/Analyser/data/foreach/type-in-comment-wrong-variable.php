<?php

namespace TypeInCommentOnForeach;

/** @var mixed[] $values */
$values = [];

/** @var int $wrongValue */
foreach ($values as $value) {
    die;
}
