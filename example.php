<?php


Class Brand extends WPModel{
	public $name = 'brand';

	protected $attributes = [
		'location',
	];

	public function products(){
		return Brand::hasMany(Product::class, 'brand_id', 'id');
	}
}

Class Product extends WPModel
{
	public $name = 'product';

	protected $attributes = [
		'location',
		'price',
		'type'
	];

	// Events (booting, booted, inserting, inserted, saving, saved)
	public function saved($object){
		echo __METHOD__;
		echo "<br>";
	}
}



//-----------------------------------------------------
// Register
//-----------------------------------------------------
Product::register();

Product::register([
	'singular_name' => 'Product'
]);


//-----------------------------------------------------
// New
//-----------------------------------------------------
$p = new Product();
$p->type = 'white';
$p->location = 'London';
$p->title = 'title';
$p->content = 'content';
$p->save();


//-----------------------------------------------------
// Getting
//-----------------------------------------------------
$postObject = get_post(15);
$p = new Product(postObject);

$p = Product::find(15);

$p = new Product(15);


//-----------------------------------------------------
// Where
// -----------------------------------------------------
$results = Product::where('location', 'London');

$result = Product::where([
	[
		'key' => 'location',
		'value' => 'London',
	],[
		'key'     => 'price',
		'value'   => '50',
	],
]);


//-----------------------------------------------------
// In
// -----------------------------------------------------
$products = Product::in(14, 15);

$products = Product::in([14, 15]);

//-----------------------------------------------------
// Relationships
// -----------------------------------------------------

$brand = Brand::find(16);
$brand->products;


die();
?>