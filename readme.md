# WP_Model

### A simple class for creating active record, eloquent-esque models of WordPress Posts.

```php

Class Product extends WP_Model
{
    public $name = 'product';
    public $attributes = [
        'color',
        'weight'
    ];
}

Product::register();

$book = new Product;
$book->color = 'Green';
$book->weight = 100;
$book->save();

```

***

### Installation

Require WP_Model with composer

```
$ composer require anthonybudd/WP_Model
```

#### Or (not recommend)

Download the WP_Model class and require it at the top of your functions.php file.

```php
    require 'src/WP_Model.php';
```

***

### Setup
You will then need to make a class that extends WP_Model. This class will need the public property $name (this will be the post type) and $attributes, an array of strings.

If you would like to have any taxonomies loaded into the model, add the optional parameter $taxonomies (array of taxonomy slugs) to the class.

If you need to prefix the model's data in your post_meta table add a public property $prefix. This will be added to the post meta so the attribute 'color' will be saved in the database using the meta_key 'wp_model_color'
```php
Class Product extends WP_Model
{
    public $name = 'product';
    public $prefix = 'wp_model_';
    public $attributes = [
        'color',
        'weight'
    ];
    public $taxonomies = [
        'category',
    ];
}
```

***

### Register
Before you can create a post you will need to register the post type. You can do this by calling the inherited static method register() in your functions.php file.
Optionally, you can also provide this method with an array of arguments, this array will be sent directly to the second argument of Wordpress's [register_post_type()](https://codex.wordpress.org/Function_Reference/register_post_type) function.
```php
Product::register();

Product::register([
    'singular_name' => 'Product'
]);
```

***

### Creating and saving models
You can create a model using the following methods.
```php
$product = new Product();
$product->color = 'white';
$product->weight = 300;
$product->title = 'the post title';
$product->content = 'the post content';
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

***

### Find()

find() will return an instanciated model. If a post exists in the database with the ID of $id it's data will be loaded into the object.

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

all() will return all posts. Use with caution.
```php
$allProducts = Product::all();
```

### In()

To find multiple posts by ID you can us the in() method.

```php
$firstProducts = Product::in(1, 2, 3, 4);
$firstProducts = Product::in([1, 2, 3, 4]);
```

### Where()

where() is a simple interface into WP_Query, the method can accept two string arguments (meta|_value and meta_key). For complex queries supply the method with a single array as the argument. The array will be automatically broken down into tax queries and meta queries, WP_Query will then be executed and will return an array of models.

```php
$greenProducts = Product::where('color', 'green');

$otherProducts = Product::where([
    [
        'key' => 'color',
        'value' => 'green',
        'compare' => '!='
    ],[
        'taxonomy' => 'category',
        'terms' => ['home', 'garden']
    ]
]);
```

### Custom Finders

The finder() method allows you to create a custom finder method.
To create a custom finder first make a method in your model named your finders name and suffixed with 'Finder' this method must return an array. The array will be given directly to the constructer of a WP_Query. The results of the WP_Query will be returned by the finder() method.

If you would like to post-process the results of your custom finder you can add a 'PostFinder' method. This method must accept one argument which will be the array of found posts.
```php

Class Product extends WP_Model
{
    ...

    public function heavyFinder()
    {  
        return [
            'meta_query' => [
                [
                    'key' => 'weight',
                    'compare' => '>',
                    'type' => 'NUMERIC',
                    'value' => '1000'
                ]
            ]
        ];
    }

    // Optional
    public function heavyPostFinder($results)
    {  
        return array_map(function($model){
            if($model->color == 'green'){
                return $model->color;
            }
        }, $results);
    }
}

$heavyProducts = Product::finder('heavy');
```

***

### Delete()
delete() will trash the post.
```php
$product = Product::find(15);
$product->delete();
```

### hardDelete()
hardDelete() will delete the post's r the post and set all of it's meta (in the database and in the object) to NULL.
```php
$product->hardDelete();
```

restore() will unTrash the post and restore the model. You cannot restore hardDeleted models.
```php
$product = Product::restore(15);
```

***

### Virtual Properties
If you would like to add virtual properties to your models, you can do this by adding a method named the virtual property's name prefixed with '_get'

```php

Class Product extends WP_Model
{
    ...

    public function _getHumanWeight()
    {  
        return $this->weight . 'Kg';
    }
}

$product = Product::find(15);
echo $product->humanWeight;
```

***

### Serialization

If you want to JSON encode a model and keep virtual properties you can do this by adding the property $serialize to the model.

Conversely, if you would like to hide a property you can do this by adding $protected to the model

```php

Class Product extends WP_Model
{
    ...

    public $serialize = [
        'humanWeight',
    ];

    public $protected = [
        'weight',
    ];

    public function _getHumanWeight()
    {  
        return $this->weight . 'Kg';
    }
}

$product = Product::find(15);
echo json_encode($product);
```

Result:
```json
{
    "ID":           15,
    "title":        "The post title",
    "content":      "The post content",
    "color":        "blue",
    "HumanWeight":  "250Kg"
}
```

***


### Events
WP_Model has an events system, this is the best way to hook into WP_Model's core functions. All events with the suffix -ing fire as soon as the method has been called. All events with the suffix -ed will be fired at the very end of the method. Below is a list of available events. All events will be supplied with the model that triggered the event

You can also trigger the save, insert and delete events from the admin section of wordpress.

- booting
- booted
- saving
- inserting
- inserted
- saved
- deleting
- deleted
- hardDeleting
- hardDeleted
- patching
- patched

When saving a new model the saving, inserting, inserted and saved events are all fired (in that order).
```php
Class Product extends WP_Model
{
    ...
    
    public function saving(){
        echo "The save method has been called, but nothing has been written to the database yet.";
    }
    
    public function saved($model){
        echo "The save method has completed and the post and it's meta data have been updated in the database.";
        echo "The Model's ID is". $model->ID;
    }
}
```

***

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

    <input type="text" name="color" value="red">
    <input type="submit" value="Submit" name="submit">
</form>
```

***

### Helper Properties

The $new property will return true if the model has not been saved in the Database yet.

The $dirty property will return true if the data in the model is different from what's currently stored in the database.


```php
$product = new Product;
$product->new; // Returns (bool) true

$product = Product::find(15);
$product->new; // Returns (bool) false

$product->color = 'red';
$product->dirty; // Returns (bool) true
$product->save();
$product->dirty; // Returns (bool) false

```

### Helper Methods

```php
Product::single(); // Returns the current model if on a single page or in the loop

Product::exists(15); // Returns (bool) true or false

$product->post() // Returns WP_Post object

$product->featuredImage($defaultURL) // Returns featured image URL

$product->toArray() // Returns an array representaion of the model

Product::asList() // Returns array of posts keyed by the post's ID
[
    15 => Product,
    16 => Product,
    17 => Product
]

// You can also specify the value of each element in the array to be meta from the model.
Product::asList('post_title')
[
    15 => "Product 1",
    16 => "Product 2",
    17 => "Product 3"
]
```


***

### Todos

 - Support data types: Array, Integer, Date
 - Update taxonomy support
 - Test admin event triggering