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


	protected function execute()
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


	public function all(callable $callback = null)
	{
		$this->execute();

		if ($callback === null)
		{
			return $this->array;
		}

		return $callback($this->array);
	}

}
