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

$product = Product::insert([
    'color' => 'blue',
    'weight' => '250'
]);
```

### Find
find() will return an instanciated model. If a post with id $id exists in the databse it's data will be loaded into the object.
```php
$product = Product::find(15);
```
findorFail() will throw an exception if a post of the correct type cannot be found in the database.
```php
try {
    $product = Product::findorFail(15);
} catch (Exception $e) {
    echo "Product not found.";
}
```

### Delete
Delete will trash the post.
```php
$product = Product::find(15);
$product->delete();
```
Hard delete will trash the post and set all of it's meta (in that database and object) to NULL.
```php
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
- deleting
- deleted
- hardDeleting
- hardDeleted
- patching
- patched

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

### Patching
By calling the static methods patchable whenever a form is submitted with the field _model. It will automatically create a new model or update an existing model if the field _id is also present.

```php
Product::patchable();
```

```html
<form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="_model" value="product">
    
    <!-- Omitting this will create a new model --> 
    <input type="hidden" name="_id" value="15">

    <input type="text" name="location" value="China">
    <input type="submit" value="Submit" name="submit">
</form>
```
### Todos

 - Improve Relationship system
 - Support data types: Array, Integer, Date

License
----

Does this section even matter? If you wanted to steal this code there is pretty much nothing I could do to stop you...

