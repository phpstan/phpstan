<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Node;
use PHPStan\Broker\Broker;
use PHPStan\Rules\FunctionCallParametersCheck;

class InstantiationRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\FunctionCallParametersCheck
	 */
	private $check;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Rules\FunctionCallParametersCheck $check
	 */
	public function __construct(Broker $broker, FunctionCallParametersCheck $check)
	{
		$this->broker = $broker;
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return New_::class;
	}

	/**
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[]
	 */
	public function processNode(Node $node): array
	{
		$instantiation = $node->getParserNode();
		if (!($instantiation->class instanceof \PhpParser\Node\Name)) {
			return [];
		}

		$class = (string) $instantiation->class;
		if ($class === 'static') {
			return array();
		}

		if ($class === 'self') {
			$class = $node->getScope()->getClass();
			if ($class === null) {
				return [];
			}
		}

		if (!$this->broker->hasClass($class)) {
			return [
				sprintf('Instantiated class %s does not exist.', $class),
			];
		}

		$classReflection = $this->broker->getClass($class);
		if ($classReflection->isAbstract()) {
			return [
				sprintf('Instantiated class %s is abstract.', $class),
			];
		}
		if ($classReflection->isInterface()) {
			return [
				sprintf('Cannot instantiate interface %s.', $class),
			];
		}

		if (!$classReflection->hasMethod('__construct') && !$classReflection->hasMethod($class)) {
			if (count($instantiation->args) > 0) {
				return [
					sprintf(
						'Class %s does not have a constructor and must be instantiated without any parameters.',
						$class
					),
				];
			}

			return [];
		}

		return $this->check->check(
			$classReflection->hasMethod('__construct') ? $classReflection->getMethod('__construct') : $classReflection->getMethod($class),
			$instantiation,
			[
				'Class ' . $class . ' constructor invoked with %d parameter, %d required.',
				'Class ' . $class . ' constructor invoked with %d parameters, %d required.',
				'Class ' . $class . ' constructor invoked with %d parameter, at least %d required.',
				'Class ' . $class . ' constructor invoked with %d parameters, at least %d required.',
				'Class ' . $class . ' constructor invoked with %d parameter, %d-%d required.',
				'Class ' . $class . ' constructor invoked with %d parameters, %d-%d required.',
			]
		);
	}

}
