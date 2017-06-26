# WP_Model - Pseudo ORM for WordPress

<p align="center"><img src="https://c1.staticflickr.com/1/415/31850480513_6cf2b5bdde_b.jpg"></p>

### A simple class for creating active record models of WordPress Posts.
WP_Model is a pseudo ORM for WordPress designed to provide a better method for handling posts using a simple OOP style syntax. WP_Model has been specifically designed to be easy as possible for front-end developers (helper methods, taxonomies) and developers with an entry level knowledge of PHP, but powerful enough (virtual properties, relationships, events) that it could be genuinely useful to back-end devs who want to make complex WP based projects.

#### Introduction: **[Medium Post](https://medium.com/@AnthonyBudd/wp-model-6887e1a24d3c)**

#### Advanced Functionality: **[Medium Post](https://medium.com/@AnthonyBudd/wp-model-advanced-b44f117617a7)**


```php

Class Product extends WP_Model
{
    public $postType = 'product';
    public $attributes = [
        'color',
        'weight'
    ];
}

Product::register();

$book = new Product;
$book->title = 'WordPress for dummies';
$book->color = 'Yellow';
$book->weight = 100;
$book->save();

```

# Installation

Require WP_Model with composer

```
$ composer require anthonybudd/WP_Model
```

**Or**

Download the WP_Model class and require it at the top of your functions.php file. This is not recommended. 


# Setup
You will then need to make a class that extends WP_Model. This class will need the public property $postType and $attributes, an array of strings.
```php
Class Product extends WP_Model
{
    public $postType = 'product';

    public $attributes = [
        'color',
        'weight'
    ];
    
    public $prefix = 'wp_model_'; // Optional
}
```
If you need to prefix the model's data in your post_meta table add a public property $prefix. This will be added to the post meta so the attribute 'color' will be saved in the database using the meta_key 'wp_model_color'


# Register
Before you can create a post you will need to register the post type. You can do this by calling the static method register() in your functions.php file.
```php
Product::register();

Product::register([
    'singular_name' => 'Product'
]);
```
Optionally, you can also provide this method with an array of arguments, this array will be sent directly to the second argument of Wordpress's [register_post_type()](https://codex.wordpress.org/Function_Reference/register_post_type) function.


# Creating and Saving
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


# Retrieving Models
**Find()**

find() will return an instantiated model if a post exists in the database with the ID if a post cannot be found it will return NULL.

```php
$product = Product::find(15);
```

**findOrFail()**

The findOrFail() method will throw an exception if a post of the correct type cannot be found in the database.

```php
try {
    $product = Product::findorFail(15);
} catch (Exception $e) {
    echo "Product not found.";
}
```

**all()**

all() will return all posts. Use with caution.

```php
$allProducts = Product::all();
```

**in()**

To find multiple posts by ID you can us the in() method.

```php
$firstProducts = Product::in([1, 2, 3, 4]);
```

## Chainable Finders

If you prefer to find your models using a chainable OOP syntax the query() method is a  wrapper for the where() method. Each of the chainable finder methods meta() and tax can accept a varying amount of arguments. You must call the execute() method to run the query.

**Meta()**
```php
Product::query()
    ->meta('meta_key', 'meta_value')
    ->meta('meta_key', 'compare', meta_value')
    ->meta('meta_key', 'compare', meta_value', 'type')
```

**Tax()**
```php
Product::query()
    ->tax('taxonomy', 'terms')
    ->tax('taxonomy', 'field', 'terms')
    ->tax('taxonomy', 'field', 'operator', 'terms')
```

**Params()**

An array of additional arguments for WP_Query.
```php
Product::query()
    ->params(['orderby' => 'meta_value', 'order' => 'ASC])
```

#### Example:

```php
$products = Product::query()
    ->meta('color', 'blue')
    ->execute();
```
```php
$products = Product::query()
    ->meta('color', 'red')
    ->meta('weight', '>', 2000, 'NUMERIC')
    ->tax('type', 'small')
    ->tax('category', ['office', 'home'])
    ->tax('quality', 'slug', 'high')
    ->tax('county', 'term_id', 'NOT IN', [1, 5])
    ->params(['orderby' => 'meta_value menu_order title'])
    ->execute();
```

# Deleting
**delete()**

delete() will trash the post.

```php
$product = Product::find(15);
$product->delete();
```
**restore()**

restore() will unTrash the post and restore the model. You cannot restore hardDeleted models.

```php
$product = Product::restore(15);
```

**hardDelete()**

hardDelete() will delete the post and set all of it's meta (in the database and in the object) to NULL.

```php
$product->hardDelete();
```

# Helper Properties

The $new property will return true if the model has not been saved in the database yet.

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

$product->title; // Returns the post's title

$product->content; // Returns the post's content

$product->the_content; // Returns the post's content via the 'the_content' filter
```


# Helper Methods

```php
Product::single(); // Returns the current model if on a single page or in the loop

Product::exists(15); // Returns (bool) true or false

Product::mostRecent($limit = 1); // Returns the most recent post

Product::mostRecent(10); // Returns the most recent 10 posts [Product, Product, Product, Product]

Product::count($postStatus = 'publish'); // Efficient way to get the number of models (Don't use count(WP_Model::all()))

$product->postDate($format = 'd-m-Y'); // Returns the post date based on the format supplied

$product->get($attribute, $default) // Get attribute from the model

$product->set($attribute, $value) // Set attribute of the model

$product->post() // Returns the WP_Post object (This will be the post at load, any updates to the post (title, content, etc) will not be reflected)

$product->permalink() // Returns the post permalink

$product->hasFeaturedImage() // Returns TRUE if a featured image has been set or FALSE if not

$product->featuredImage($defaultURL) // Returns the featured image URL

$product->toArray() // Returns an array representation of the model

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

# Virtual Properties
If you would like to add virtual properties to your models, you can do this by adding a method named the virtual property's name prefixed with '_get'

```php

Class Product extends WP_Model
{
    ...

    public $virtual = [
        'humanWeight'
    ];

    public function _getHumanWeight()
    {  
        return $this->weight . 'Kg';
    }
}

$product = Product::find(15);
echo $product->humanWeight;
```

# Default Properties
To set default values for the attributes in your model use the $default property. The key of this array will be the attribute you wish to set a default value for and the value will be the default value.

```php

Class Product extends WP_Model
{
    ...

    public $default = [
        'color' => 'black'
    ];
}

$product = new Product;
echo $product->color; // black
```

# Filter Properties
If you need a property to be parsed before it is returned you can use a filter method. You must add the attribute name to a array named $filter and create a method prefixed with ‘_filter’, this method must take one argument, this will be the property value.

Alternatively, if you want to send the value through an existing function (intval(), number_format(), your_function(), etc) you can do this by naming the desired function as the value using the assoc array syntax.
Note: as the example code shows, you can use both methods of filtering simultaneously.

When you set an attribute's value to an object that is an instance of WP_Model or when you save an array of WP_Models, both will result in the model(s) being saved individually. The parent model will only store the child models IDs (or array of IDs). To retrieve these attribute values as instantiated models set the filter property value to the class of the desired model. 
```php

Class Product extends WP_Model
{
    ...

    public $filter = [
        'weight'
        'stock' => 'number_format',
        'seller' => Seller::class,
        'related' => Product::class,
    ];

    public function _filterWeight($value){
        return intval($value);
    }
}

$product = Product::insert([
    'weight' => 250,
    'stock' => '3450',
    'seller' => Seller::find(3),
    'related' => [
        new Product,
        new Product,
        new Product,
    ]
]);

$product->weight;  // (int) 250
$product->stock;   // (string) 3,450
$product->seller;  // (object) Seller
$product->related; // (array) [Product, Product, Product]
```
**Note:** WP_Model dynamically loads child models as and when they are requested. If you dump the model without explicitly requesting the child model (eg $product->seller) the parent model will only store the child model's ID.

# Serialization

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

**Result:**
```json
{
    "ID":           15,
    "title":        "The post title",
    "content":      "The post content",
    "color":        "blue",
    "HumanWeight":  "250Kg"
}
```

# Advanced Finding

**Where(String $metaKey, String $metaValue)**
**Where(Array $WPQuery)**

where() is a simple interface into WP_Query, the method can accept two string arguments meta_value and meta_key.

For complex queries supply the method with a single array as the argument. The array will be automatically broken down into tax queries and meta queries, WP_Query will then be executed and will return an array of models.

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

**finder()**

The finder() method allows you to create a custom finder method, this is the best way to contain frequently used WP_Querys inside your model's class. To create a custom finder first make a method in your model named your finders name and prefixed with '_finder', this method must return an array. The array will be given directly to the constructor of a WP_Query. The results of the WP_Query will be returned by the finder() method. You can provide additional arguments to the finder method by providing an array to the second argument of the static method finder() as shown below ('heavyWithArgs').

If you would like to post-process the results of your custom finder you can add a '_postFinder' method. This method must accept one argument which will be the array of found posts.

```php

Class Product extends WP_Model
{
    ...

    public function _finderHeavy($args)
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
    public function _postFinderHeavy($results)
    {  
        return array_map(function($model){
            if($model->color == 'green'){
                return $model->color;
            }
        }, $results);
    }


    // Finder with optional args
    public function _finderHeavyWithArgs($args)
    {  
        return [
            'paged'      => $args['page'], // 3
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
}

$heavyProducts = Product::finder('heavy');

// Finder with optional args
$heavyProducts = Product::finder('heavyWithArgs', ['page' => 3]); 
```

# Events
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

# Taxonomies

If you would like to have any taxonomies loaded into the model, add the optional public property $taxonomies (array of taxonomy slugs) to the class.
```php
Class Product extends WP_Model
{
    ...
    public $taxonomies = [
        'category',
    ];
}
```

You can set a models taxonomies by providing it in the array when instantiating the model, this array can be a combination of term slugs or term _ids.
The model's terms can be accessed by getting the property named the taxonomy name. 
```php
$product = Product::insert([
    'title' => 'product',
    'color' => 'blue',
    'weight' => '250',
    'category' => ['home', 3]
]);

$product->category; // ['Home', 'Office'];
```

If you want direct access to the taxonomy objects you can do this by using the getTaxonomy() method. The first argument is the taxonomy name, the second argument is optional and it is the property to be extracted from the term object. Not providing the second argument will return WP_Term objects.

```php
$product->getTaxonomy('category'); // [WP_Term, WP_Term];
$product->getTaxonomy('category', 'term_id'); // [2, 3];
$product->getTaxonomy('category', 'name'); // ['Home', 'Office'];
```

You can add a taxonomy by using the addTaxonomy() method. The first argument is the taxonomy name, the second argument can either be the term_id (must be an integer) or the term slug (must be provided as a string). If the term could not be found the method will return FALSE.

If you want to add multiple terms to a model you can use the addTaxonomies() method. The second argument must be an array of term slugs and/or term_ids.

```php
$product->addTaxonomy('category', 'home');
$product->addTaxonomy('category', 3);

$product->addTaxonomies('category', ['home', 'office']);
$product->addTaxonomies('category', ['home', 3]);
$product->addTaxonomies('category', [2, 3]);
```

To remove terms from the model you can use the removeTaxonomy() and removeTaxonomies() methods. These work in the same fashion as the addTaxonomy() and addTaxonomies() method as shown above.
```php
$product->removeTaxonomy('category', 'home');
$product->removeTaxonomy('category', 3);

$product->removeTaxonomies('category', ['home', 'office']);
$product->removeTaxonomies('category', [2, 3]);
```

To remove all terms associated to the model of the specified taxonomy use clearTaxonomy().
```php
$product->clearTaxonomy('category');
$product->getTaxonomy('category'); // [];
```

No change to the models taxonomies will be written to the database until you call the save() method.
