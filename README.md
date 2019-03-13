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

This example keeps the _even_ numbers and multiplies them by _2_ (specified by `new \Comprehend\Filter\Multiply(2)`).

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

* The namespace `\Comprehend\All` is not perfect. A better name needs to be found. Maybe `\Comprehend\Result`?
* `do()` -> `apply()`?
* `Comprehend::$actions` => `Comprehend::$tasks`

## Thoughts

### Sub-filters and Sub-actions

Let's say we have a list of objects, or a list of arrays, and we want to filter on a parameter of the objects (or nested array):

```php
[
    ["id" => 1, "name" => "Donald"],
    ["id" => 2, "name" => "Daisy"]
]
```

#### Adding the field as the second parameter to filter()

```php
->filter(new IsEven, "id");
```

* Easy to use and remember.
* Which key should be sent to `__invoke()`?  The id of the row, or simply "id"? Or maybe we don't need to pass the key, _ever_. I cannot think of a use case for it.

#### Passing the field to the filter object

```php
->filter(new IsEven("id"));
```

* I think this is actually a bad idea. If we take `Multiply` action for example, we want the first parameter to be the factor, which means that the position of the key is different between different filters/actions.
* We are placing this logic in the filter/action instead of in the Comprehend class, which I believe is the _wrong_ separation of concern.

#### Passing the filter object by method

```php
->filter((new IsEven)->field("id"))
```

* Better than using the constructor, but still  I believe is the wrong separation of concern.

#### A different method

```php
->subFilter("id", new IsEven);
```

* Are there any advantages of using a different method instead of an extra parameter to `filter()`?