<?php

declare(strict_types=1);

namespace Userland\Library\Forms;

use Phalcon\Forms\Element\ElementInterface;

class TextElement implements ElementInterface {
	public function render(array $attributes = []): string {
		return '<input type="text">';
	}
}
