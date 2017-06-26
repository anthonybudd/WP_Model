<?php

class Order extends WP_Model{
	public $postType = 'order';

	public $attributes = [
		'saved_product',
		'products',
		'product',
	];

	public $filter = [
		'saved_product' => Product::class,
		'products' => Product::class,
		'product' => Product::class,
	];
}