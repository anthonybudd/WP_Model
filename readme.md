# WPModel

##### A simple drop-in abstract class for creating active record style, eloquent-esque models of WordPress Posts.

### Installation

Download the WPModel class and require it at the top of your functions.php file.

```php
    require 'WPModel.php';
```

### Setup
You will then need to make a class that extends WPModel. This class will need the public property $name (lowercase String) and $attributes (Array of lowercase Strings).
```php
Class Product extends
{
    public $name = 'product';
    public $attributes = [
        'color'
        'weight'
    ];
}
```

### Register
Before you can create a post you will need to register the post type. You can do this by calling the inherited static method register() in your functions.php file.
Optionally, you can also provide this method with an array of arguments, this array will be sent directly to the second argument of Wordpress's [register_post_type()](https://codex.wordpress.org/Function_Reference/register_post_type) function.
```php
Product::register();

Product::register([
    'singular_name' => 'Product'
]);
```

### Create and Save
```php
$product = new Product();
$product->color = 'white';
$product->weight = '300';
$product->title = 'post title';
$product->content = 'post content';
$product->save();

$product = new Product([
    'color' => 'blue',
    'weight' => '250'
]);
$product->save();
```

### Find
```php
$product = Product::find(15);

try {
    $product = Product::findorFail(15);
} catch (Exception $e) {
    echo "Product not found.";
}
```

### Delete
```php
$product = Product::find(15);

$product->delete();
$product->hardDelete();
```

### Events
WPModel has a rudimentary events system, this is the best way to hook into WPModel's core functions. All events with the suffix -ing fire as soon as the method has been called. All events with the suffix -ed will be fired at the very end of the method. Below is a list of available events;

- booting, before the model has initialized
- booted, the model has initialized
- saving, before saving the model
- inserting, before inserting a new post into the database
- inserted, the new post has been inserted into the database
- saved, the model has finished saving
- deleting, 
- deleted
- hardDeleting
- hardDeleted

When saving a new model the saving, inserting, inserted and saved events are all fired (in that order).
```php
Class Product extends
{
    public $name = 'product';
    public $attributes = [
        'color'
        'weight'
    ];
    
    public function saving(){
        echo "The save method has been called, but nothing has been written to the database yet."
    }
    
    public function saved(){
        echo "The save method has completed and the post and it's meta data have been updated in the database."
    }
}
```

### Todos

 - Improve Relationship system
 - Support data types: Array, Integer

License
----

Does this section even matter? If you wanted to steal this code there is pretty much nothing I could do to stop you...

