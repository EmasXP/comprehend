# Comprehend

Comprehend is a library in the spirit of [Laravel's Collections](https://laravel.com/docs/5.8/collections) and [Python's List Comprehensions](https://python-3-patterns-idioms-test.readthedocs.io/en/latest/Comprehensions.html). This repository is just a proof of concept (as of now at least).

## Usage

```php
$array = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

$data = (new \Comprehend\Comprehend($array))
	->filter(new \Comprehend\Filter\IsEven)
	->all();
	
var_dump($data);
```

This example keeps all items in the array that are even. In other words it _filters out_ items in the array that are _not even_.

You can also apply actions:

```php
$array = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

$data = (new \Comprehend\Comprehend($array))
	->filter(new \Comprehend\Filter\IsEven)
	->do(new \Comprehend\Action\Multiply(2))
	->all();
	
var_dump($data);
```

This example keeps the _even_ numbers and multiplies them by _2_ (specified by `new \Comprehend\Filter\Miltiply(2)`).

Comprehend is chronological, so if you add a filter _after_ an action, the action will be applied _before_ the filter.

You can add as many filters and actions as you feel fit.

And now we are going to find the average of the result:

```php
$array = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

$average = (new \Comprehend\Comprehend($array))
	->filter(new \Comprehend\Filter\IsEven)
	->do(new \Comprehend\Action\Multiply(2))
	->all(new \Comprehend\All\Average);
	
var_dump($average);
```

## Using anonymous functions

This is an example that multiplies by two:

```php
$data = (new \Comprehend\Comprehend($array))
	->do(function($key, $val){
		return $val * 2;
	})
	->all();
```

## Naming

The namespace `\Comprehend\All` is not perfect. A better name needs to be found.