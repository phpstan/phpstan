<?php declare(strict_types = 1);

namespace PHPStan\Rules;

class Registry
{

	/**
	 * @var \PHPStan\Rules\Rule[][]
	 */
	private $rules = [];

	/**
	 * @var \PHPStan\Rules\Rule[][]
	 */
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
			$nodeTypes = [$nodeType] + class_parents($nodeType) + class_implements($nodeType);

			$rules = [];
			foreach ($nodeTypes as $nodeType) {
				foreach ($this->rules[$nodeType] ?? [] as $rule) {
					$rules[get_class($rule)] = $rule;
				}
			}

			$this->cache[$nodeType] = array_values($rules);
		}

		return $this->cache[$nodeType];
	}

}
