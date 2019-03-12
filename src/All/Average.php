<?php

namespace Comprehend\All;

class Average {

	public function __invoke($items)
	{
		if (count($items) == 0)
		{
			return 0;
		}

		$total = 0;

		foreach ($items as $item)
		{
			$total += $item;
		}

		return $total / count($items);
	}

}
