<?php // @codingStandardsIgnoreFile (phpcs runs out of memory)

/**
 * Copied over from https://github.com/phan/phan/blob/da49cd287e3b315f5dfdb440522da797d980fe63/src/Phan/Language/Internal/FunctionSignatureMap_php74_delta.php
 * with more functions added from PHP 7.4 changelog
 * Copyright (c) 2015 Rasmus Lerdorf
 * Copyright (c) 2015 Andrew Morrison
 */

/**
 * This contains the information needed to convert the function signatures for php 7.4 to php 7.3 (and vice versa)
 *
 * This has two sections.
 * The 'new' section contains function/method names from FunctionSignatureMap (And alternates, if applicable) that do not exist in php7.3 or have different signatures in php 7.4.
 *   If they were just updated, the function/method will be present in the 'added' signatures.
 * The 'old' signatures contains the signatures that are different in php 7.3.
 *   Functions are expected to be removed only in major releases of php.
 *
 * @see FunctionSignatureMap.php
 *
 * @phan-file-suppress PhanPluginMixedKeyNoKey (read by Phan when analyzing this file)
 */
return [
	'new' => [
		'FFI::addr' => ['FFI\CData', '&ptr'=>'FFI\CData'],
		'FFI::alignof' => ['int', '&ptr'=>'mixed'],
		'FFI::arrayType' => ['FFI\CType', 'type'=>'string|FFI\CType', 'dims'=>'array<int,int>'],
		'FFI::cast' => ['FFI\CData', 'type'=>'string|FFI\CType', '&ptr'=>''],
		'FFI::cdef' => ['FFI', 'code='=>'string', 'lib='=>'?string'],
		'FFI::free' => ['void', '&ptr'=>'FFI\CData'],
		'FFI::load' => ['FFI', 'filename'=>'string'],
		'FFI::memcmp' => ['int', '&ptr1'=>'FFI\CData|string', '&ptr2'=>'FFI\CData|string', 'size'=>'int'],
		'FFI::memcpy' => ['void', '&dst'=>'FFI\CData', '&src'=>'string|FFI\CData', 'size'=>'int'],
		'FFI::memset' => ['void', '&ptr'=>'FFI\CData', 'ch'=>'int', 'size'=>'int'],
		'FFI::new' => ['FFI\CData', 'type'=>'string|FFI\CType', 'owned='=>'bool', 'persistent='=>'bool'],
		'FFI::scope' => ['FFI', 'scope_name'=>'string'],
		'FFI::sizeof' => ['int', '&ptr'=>'FFI\CData|FFI\CType'],
		'FFI::string' => ['string', '&ptr'=>'FFI\CData', 'size='=>'int'],
		'FFI::typeof' => ['FFI\CType', '&ptr'=>'FFI\CData'],
		'FFI::type' => ['FFI\CType', 'type'=>'string'],
		'get_mangled_object_vars' => ['array', 'obj'=>'object'],
		'mb_str_split' => ['array<int,string>|false', 'str'=>'string', 'split_length='=>'int', 'encoding='=>'string'],
		'password_algos' => ['array<int, string>'],
		'password_hash' => ['string|false', 'password'=>'string', 'algo'=>'string|null', 'options='=>'array'],
		'password_needs_rehash' => ['bool', 'hash'=>'string', 'algo'=>'string|null', 'options='=>'array'],
		'ReflectionReference::fromArrayElement' => ['?ReflectionReference', 'array'=>'array', 'key'=>'int|string'],
		'ReflectionReference::getId' => ['string'],
		'SQLite3Stmt::getSQL' => ['string', 'expanded='=>'bool'],
		'WeakReference::create' => ['WeakReference', 'referent'=>'object'],
		'WeakReference::get' => ['?object'],
	],
	'old' => [],
];
