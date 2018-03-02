<?php declare(strict_types = 1);

namespace DuplicateMethod;

class Lesson
{

	use LessonTrait;

	public function test()
	{
		$this->doFoo();
	}

}
