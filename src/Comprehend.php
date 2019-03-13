<?php

namespace Comprehend;

class Comprehend {

	const IF = 1;
	const IF_NOT = 2;
	const DO = 3;

	protected $array;
	protected $actions = array();
	protected $assoc = false;


	public function __construct(array $array, $assoc = false)
	{
		$this->array = $array;
		$this->assoc = $assoc;
	}


	public function filter(callable $if)
	{
		$this->actions[] = array(self::IF, $if);
		return $this;
	}


	public function filterNot(callable $if_not)
	{
		$this->actions[] = array(self::IF_NOT, $if_not);
		return $this;
	}


	public function do(callable $do)
	{
		$this->actions[] = array(self::DO, $do);
		return $this;
	}


	protected function execute_legacy()
	{
		if (count($this->actions) == 0)
		{
			return;
		}

		$out = array();

		foreach ($this->array as $key => $val)
		{
			foreach ($this->actions as $action)
			{
				list($type, $callback) = $action;

				if ($type == self::IF)
				{
					if ( ! $callback($key, $val))
					{
						continue 2;
					}
				}
				elseif ($type == self::IF_NOT)
				{
					if ($callback($key, $val))
					{
						continue 2;
					}
				}
				elseif ($type == self::DO)
				{
					$val = $callback($key, $val);
				}
				else
				{
					print "TODO: THROW EXCEPTION";
				}
			}

			if ($this->assoc)
			{
				$out[$key] = $val;
			}
			else
			{
				$out[] = $val;
			}
		}

		$this->array = $out;
		$this->actions = array();
	}


	protected function execute_array_walk()
	{
		if (count($this->actions) == 0)
		{
			return;
		}

		$remove = array();

		array_walk(
			$this->array,
			function(&$val, $key) use (&$remove) {
				foreach ($this->actions as $action)
				{
					list($type, $callback) = $action;

					if ($type == self::IF)
					{
						if ( ! $callback($key, $val))
						{
							$val = null;
							$remove[] = $key;
							break;
						}
					}
					elseif ($type == self::IF_NOT)
					{
						if ($callback($key, $val))
						{
							$val = null;
							$remove[] = $key;
							break;
						}
					}
					elseif ($type == self::DO)
					{
						$val = $callback($key, $val);
					}
					else
					{
						print "TODO: THROW EXCEPTION";
					}
				}
			}
		);

		for ($i = count($remove)-1; $i >= 0; $i--)
		{
			if ($this->assoc)
			{
				unset($this->array[$remove[$i]]);
			}
			else
			{
				array_splice($this->array, $remove[$i], 1);
			}
		}

		$this->actions = array();
	}


	protected function execute_array_filter()
	{
		// NOTE: THIS ONE HAS NO WAY OF APPLYING ACTIONS TO THE ARRAY

		if (count($this->actions) == 0)
		{
			return;
		}

		$this->array = array_filter(
			$this->array,
			function($val, $key) {
				foreach ($this->actions as $action)
				{
					list($type, $callback) = $action;

					if ($type == self::IF)
					{
						if ( ! $callback($key, $val))
						{
							return false;
						}
					}
					elseif ($type == self::IF_NOT)
					{
						if ($callback($key, $val))
						{
							return false;
						}
					}
					elseif ($type == self::DO)
					{
						// NOTE: This is not going to be reflected in the array
						$val = $callback($key, $val);
					}
					else
					{
						print "TODO: THROW EXCEPTION";
					}
				}

				return true;
			},
			ARRAY_FILTER_USE_BOTH
		);

		$this->actions = array();
	}


	protected function execute_array_walk_array_filter_combination()
	{
		/**
		 * NOTE:
		 * The grouping here works like this: Let's say that we add tasks in this order:
		 * filter
		 * filter
		 * action
		 * filter
		 * action
		 * action
		 *
		 * Then it will be grouped like this:
		 * filter, filter
		 * action
		 * filter
		 * action, action
		 *
		 * The grouping implementation here is confusing and I know it. It needs to be refactored
		 * if we choose to stick with this method.
		 */

		if (count($this->actions) == 0)
		{
			return;
		}

		$grouped = array();
		$last_type = null;

		foreach ($this->actions as $action)
		{
			$type = $action[0];

			if ($type === self::DO)
			{
				if ($last_type === self::DO)
				{
					$grouped[count($grouped)-1][] = $action;
				}
				else
				{
					$grouped[] = array($action);
				}
			}

			else
			{
				if (
					$last_type === self::IF
					|| $last_type === self::IF_NOT
				)
				{
					$grouped[count($grouped)-1][] = $action;
				}
				else
				{
					$grouped[] = array($action);
				}
			}
		}


		foreach ($grouped as $actions)
		{
			$first_action = $actions[0];
			$type = $first_action[0];

			if ($type === self::DO)
			{
				array_walk(
					$this->array,
					function(&$val, $key) use ($actions) {
						foreach ($actions as $action)
						{
							list($type, $callback) = $action;

							if ($type == self::DO)
							{
								$val = $callback($key, $val);
							}
							else
							{
								print "TODO: THROW EXCEPTION";
							}
						}
					}
				);
			}

			else
			{
				$this->array = array_filter(
					$this->array,
					function($val, $key) use ($actions) {
						foreach ($actions as $action)
						{
							list($type, $callback) = $action;

							if ($type == self::IF)
							{
								if ( ! $callback($key, $val))
								{
									return false;
								}
							}
							elseif ($type == self::IF_NOT)
							{
								if ($callback($key, $val))
								{
									return false;
								}
							}
							else
							{
								print "TODO: THROW EXCEPTION";
							}
						}

						return true;
					},
					ARRAY_FILTER_USE_BOTH
				);
			}
		}
	}


	public function all(callable $callback = null)
	{
		$this->execute_array_walk_array_filter_combination();

		if ($callback === null)
		{
			return $this->array;
		}

		return $callback($this->array);
	}

}
