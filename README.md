# phore-hydrator
serialize / unserialize plain into object structures. Hydrator parses
the DocComments of public properties and instanciates the classes
according to the definiton.

Examples:
- [hydrator-usage-example.php](doc/hydrator-usage-example.php)

Installation:

```
composer install phore/hydrator
```

## Basic Example

```php
class UserData {
    /**
     * @var string
     */
    public $name;
    
    /**
    * Assoc Array 
    * @var array<string, ClassType2> 
    */
    public $map;
    
    /**
     * @var int
     */
    public $age;
}

$input = ["name"=>"bob", "age"=>37];

$userData = phore_hydrate($input, UserData::class);

assert( $userData instanceof UserData);
```
`$userData` is a `UserData` Object and all properties casted correctly
to desired types specified in DocComments.

## Recognized Annotations

- Simple types like `string`, `int`, `bool`, `float`, `array`
- Array types like `string[]`, `int[]`...
- Object types `OtherClass`
- Arrays of Objects `OtherClass[]`
- Nullable properties `type|null`

## Guide

### Getters / Setters

On objects, hydrator will try to set property values in the following
order:

1) If object has a `set<PropertyName>($value)`-Method it will use it first
2) If the property is `public` it will be set directly
3) If there is a `__set($name, $value)` method it will be used

### Default Values

Default values will be applied if no data was found for the specific
key

```
public $prop1 = []
```
### Optional Properties

You can define a property as optional by adding `|null` to the
DocBlock.

```
/**
 * @var SomeEntity1|null
 */
public $entity1;
```

If the input data was not found, the value will be `null`.

### Filter input data before hydration

To ease backwards compatibility issues, the magick `__hydrate()` method
is called to prefilter the input data before it is hydrated.

```php
class Entity1 {
    public $p1;

    public function __hydrate(array $input) : array
    {
        // .. modify input to match the object ..
        return $input;
    }
}
```

### Dealing with additional / undefined input data

By default, on undefined input keys, hydrator will throw
an exception. You can toggle this behaviour 
