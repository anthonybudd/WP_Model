<?php


Class Product extends WPModel
{
	public $name = 'product';

	protected $attributes = [
		'location',
		'price',
		'type'
	];

	// Events
	public function booting($object){
		echo __METHOD__;
		echo "<br>";
	}

	public function booted($object){
		echo __METHOD__;
		echo "<br>";
	}

	public function inserting($object){
		echo __METHOD__;
		echo "<br>";
	}

	public function inserted($object){
		echo __METHOD__;
		echo "<br>";
	}

	public function saving($object){
		echo __METHOD__;
		echo "<br>";
	}

	public function saved($object){
		echo __METHOD__;
		echo "<br>";
	}


    
    public function prefixValue($prefix){}
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
// Getting and Saving
//-----------------------------------------------------
$p = get_post(7);
$p = new Product($p);

$p = Product::find(15);

$p = new Product(15);
$p->type = 'white';
$p->location = 'London';

$p->title = 'title';
$p->content = 'content';
$p->save();



//-----------------------------------------------------
// Where
// -----------------------------------------------------
$results = Product::where('location', 'London');
var_dump($results);


$result = Product::where([
	[
		'key' => 'location',
		'value' => 'London',
	],[
		'key'     => 'price',
		'value'   => '50',
	],
]);
var_dump($results);


//-----------------------------------------------------
// In
// -----------------------------------------------------
$products = Product::in(14, 15);
var_dump($products);

$products = Product::in([14, 15]);
var_dump($products);



die();
?>