# Vestige

A sophisticated word full of meaning.
**vestige**. n. A visible trace, evidence, or sign of something that once existed but exists or appears no more.
Vestige is a basic PHP experiment, based in a single class Memory. Although this class works as a 'quasi-DI', this is actually NOT its main purpose. Vestige emerges from an attempt of declaring methods from a class which hasn't yet been instantiated, and then execute those methods, once the class instance is finally created.

Even though this class has been thought as a proof of concept, a handful of possible applications come to mind:
* Asynchronous class methods and tasks
* Retaining results from various class methods, even after a class is destroyed
* Easy to use hooks and triggers

# How it works

Vestige uses the class Memory to hold class objects. Having that said, it could be a design structure just by then, but we decided to move it further. So, for understanding the process and all features, let's start by adding an array of objects to the class Memory:

```php
use Vestige\Memory;
use Any\ClassA;
use Any\ClassB;

require __DIR__ . '/vendor/autoload.php';

$objects = [
	'class_a' => new ClassA // Each class receives an instance tag
];

$vestige = new Memory($objects);
/*
So $vestige->objects['class_a'] contains an instance of ClassA, for instance
*/
```
Now, let's say ClassB has three different methods with no arguments: prepare(), toJson() and send(). Vestige makes possible to you to position those methods, even if ClassB hasn't been added to Memory or instantiated in any other way. Normally, that would throw an exception or fatal, as you can't call non-static method from a class that has no existent instances.

However, with Vestige you can leave a 'record' for each of those methods, and they will be executed in the moment a ClassB instance is created through Memory. So, let's see the code.

```php
// ...

$vestige = new Memory($objects);

$vestige->record('class_b', 'prepare');
$vestige->record('class_b', 'toJson');
$vestige->record('class_b', 'send');
```
Done. The three methods are positioned and waiting for a class instance tagged as 'class_b'. If no instances like that are ever created, nothing is going to happen - not even exceptions, of course. However, if a new instance of ClassB named 'class_b' is added to Memory any time, the methods will be immediately executed.

For such, Memory is also equipped with a static method for pushing new instances to the objects array:

```php
// ...
$vestige->record('class_b', 'prepare');
$vestige->record('class_b', 'toJson');
$vestige->record('class_b', 'send', ['mail@domain.com', 'Contact Name']);

$memory->push(['class_b' => new ClassB()]); // As an array - which means you can push several objects altogether

/*
Once the object is pushed, all queued methods are executed in sequence.
For methods with parameters, these can be passed in order, in an array,
as a third argument of the method record() - like send() example
*/
```

## Leaving a 'vestige'

Now the process that led to this library name. So as adding new instances is possible, using push(), you can remove existent instance anytime, by using drop(). When that happens, hooked methods won't be executed anymore. Nonetheless, an interesting thing occurs. Taking the previous example, let's see what happens if we drop ClassB.

```php
$memory->drop('class_b'); // Removes instance from $objects in Memory

// ClassB 'promised' methods are not called anymore.
// But they leave a vestige...
```

Well, some methods return variables or any other elements and data. If we just drop the instance, that would imply we can't get those returns anymore. Of course we could save results in a database, or even cache the serialized class and its results. But we don't wwant to cache anything, nor using a database or making any kind of query for now. But, as I promised, that first queued execution produced a special by-product: a vestige.

Even with no instances of ClassB, you can still retrieve the results, if any, for the three recorded methods. You can either get an array with the full 'vestige', or retrieving the results from one of those methods only - like this:

```php
$one = $memory->return('class_b', 'toJson'); // Gets toJson results
$all = $memory->returnAll(); // Gets an array with all vestige data, including method results
```