<?php

namespace ConstantsForNodeScopeResolverTest;

$foo = FOO_CONSTANT;

define('BAR_CONSTANT', 'bar');

if (defined('BAZ_CONSTANT')) {
	die;
}
