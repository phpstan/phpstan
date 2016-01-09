<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\FunctionLike;
use PHPStan\Broker\Broker;

class FunctionDefinitionCheck
{

	const VALID_TYPEHINTS = [
		'self',
		'static',
		'array',
		'callable',
		'string',
		'int',
		'bool',
		'float',
	];

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	/**
	 * @param \PhpParser\Node\FunctionLike $function
	 * @param string $parameterMessage
	 * @param string $returnMessage
	 * @return string[]
	 */
	public function checkFunction(
		FunctionLike $function,
		string $parameterMessage,
		string $returnMessage
	): array
	{
		$errors = [];
		foreach ($function->getParams() as $param) {
			$class = (string) $param->type;
			if (
				$class
				&& !in_array($class, self::VALID_TYPEHINTS, true)
				&& !$this->broker->hasClass($class)
			) {
				$errors[] = sprintf($parameterMessage, (string) $param->name, $class);
			}
		}

		$returnType = (string) $function->getReturnType();
		if (
			$returnType
			&& !in_array($returnType, self::VALID_TYPEHINTS, true)
			&& !$this->broker->hasClass($returnType)
		) {
			$errors[] = sprintf($returnMessage, $returnType);
		}

		return $errors;
	}

}
