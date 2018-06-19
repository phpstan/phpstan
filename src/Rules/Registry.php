<?php declare(strict_types = 1);

namespace PHPStan\Rules;

class Registry
{

	/** @var \PHPStan\Rules\Rule[][] */
	private $rules = [];

	/** @var \PHPStan\Rules\Rule[][] */
	private $cache = [];

	/**
	 * @param \PHPStan\Rules\Rule[] $rules
	 */
	public function __construct(array $rules)
	{
		foreach ($rules as $rule) {
			$this->rules[$rule->getNodeType()][] = $rule;
		}
	}

	/**
	 * @param string $nodeType
	 * @return \PHPStan\Rules\Rule[]
	 */
	public function getRules(string $nodeType): array
	{
		if (!isset($this->cache[$nodeType])) {
			$parentNodeTypes = [$nodeType] + class_parents($nodeType) + class_implements($nodeType);

			$rules = [];
			foreach ($parentNodeTypes as $parentNodeType) {
				foreach ($this->rules[$parentNodeType] ?? [] as $rule) {
					$rules[] = $rule;
				}
			}

			$this->cache[$nodeType] = $rules;
		}

		return $this->cache[$nodeType];
	}

}
