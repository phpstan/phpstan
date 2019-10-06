<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PhpParser\Node\Name;
use PHPStan\Broker\Broker;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\VerbosityLevel;

class GenericAncestorsCheck
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\Generics\GenericObjectTypeCheck */
	private $genericObjectTypeCheck;

	public function __construct(
		Broker $broker,
		GenericObjectTypeCheck $genericObjectTypeCheck
	)
	{
		$this->broker = $broker;
		$this->genericObjectTypeCheck = $genericObjectTypeCheck;
	}

	/**
	 * @param array<\PhpParser\Node\Name> $nameNodes
	 * @param array<\PHPStan\Type\Type> $ancestorTypes
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function check(
		array $nameNodes,
		array $ancestorTypes,
		string $incompatibleTypeMessage,
		string $noNamesMessage,
		string $noRelatedNameMessage,
		string $classNotGenericMessage,
		string $notEnoughTypesMessage,
		string $extraTypesMessage,
		string $typeIsNotSubtypeMessage,
		string $invalidTypeMessage
	): array
	{
		if (count($ancestorTypes) === 0) {
			return [];
		}

		$names = array_fill_keys(array_map(static function (Name $nameNode): string {
			return $nameNode->toString();
		}, $nameNodes), true);

		$messages = [];
		foreach ($ancestorTypes as $ancestorType) {
			if (!$ancestorType instanceof GenericObjectType) {
				$messages[] = RuleErrorBuilder::message(sprintf($incompatibleTypeMessage, $ancestorType->describe(VerbosityLevel::typeOnly())))->build();
				continue;
			}

			$ancestorTypeClassName = $ancestorType->getClassName();
			if (!isset($names[$ancestorTypeClassName])) {
				if (count($names) === 0) {
					$messages[] = RuleErrorBuilder::message($noNamesMessage)->build();
				} else {
					$messages[] = RuleErrorBuilder::message(sprintf($noRelatedNameMessage, $ancestorTypeClassName, implode(',', array_keys($names))))->build();
				}

				continue;
			}

			$genericObjectTypeCheckMessages = $this->genericObjectTypeCheck->check(
				$ancestorType,
				$classNotGenericMessage,
				$notEnoughTypesMessage,
				$extraTypesMessage,
				$typeIsNotSubtypeMessage
			);
			$messages = array_merge($messages, $genericObjectTypeCheckMessages);

			foreach ($ancestorType->getReferencedClasses() as $referencedClass) {
				if (
					$this->broker->hasClass($referencedClass)
					&& !$this->broker->getClass($referencedClass)->isTrait()
				) {
					continue;
				}

				$messages[] = RuleErrorBuilder::message(sprintf($invalidTypeMessage, $referencedClass))->build();
			}
		}

		return $messages;
	}

}
