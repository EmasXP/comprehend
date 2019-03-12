<?php

namespace Comprehend\Filter;

class IsEven {

	public function __invoke($key, $val)
	{
		return ($val % 2) == 0;
	}

}
