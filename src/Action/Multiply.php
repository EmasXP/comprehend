<?php

namespace Comprehend\Action;

class Multiply {

	protected $factor;


	public function __construct(int $factor)
	{
		$this->factor = $factor;
	}


	public function __invoke($key, $val)
	{
		return $val * $this->factor;
	}

}
