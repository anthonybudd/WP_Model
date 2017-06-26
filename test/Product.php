<?php

Class Product extends WP_Model
{
	public $postType = 'product';

	public $attributes = [
		'location',
		'price',
		'color',
		'type',
		'weight',
		'stock_left',
		'items_sold',
	];

	public $taxonomies = [
		'category'
	];

	public $default = [
		'color' => 'black'
	];

	public $virtual = [
		'available'
	];

	public $filter = [
		'stock_left' => 'intval',
		'items_sold' => 'number_format',
		'weight',
	];


	public static function apple(){
		return 'APPLE';
	}

	public function _filterWeight($value){
		return intval($value);
	}

	// Virtual
	public function _getAvailable(){
		return 'virtual';
	}

	public function _finderHeavy()
    {  
        return [
            'meta_query' => [
                [
                    'key' => 'color',
                    'value' => 'blue'
                ]
            ]
        ];
    }

	public function _finderBlue()
    {  
        return [
            'meta_query' => [
                [
                    'key' => 'color',
                    'value' => 'blue'
                ]
            ]
        ];
    }

    public function _postFinderBlue($results)
    {  
        return array_map(function($model){
            return $model->color;
        }, $results);
    }

	// Events
	public function booting($object){
		global $events;
		$events['booting'] = TRUE;
	}

	public function booted($object){
		global $events;
		$events['booted'] = TRUE;
	}

	public function saving($model){
		global $events;
		$events['saving'] = TRUE;
	}

	public function inserting($object){
		global $events;
		$events['inserting'] = TRUE;
	}

	public function inserted($object){
		global $events;
		$events['inserted'] = TRUE;
	}

	public function saved($object){
		global $events;
		$events['saved'] = TRUE;
	}

	public function deleting($object){
		global $events;
		$events['deleting'] = TRUE;
	}

	public function deleted($object){
		global $events;
		$events['deleted'] = TRUE;
	}

	public function hardDeleting($object){
		global $events;
		$events['hardDeleting'] = TRUE;
	}

	public function hardDeleted($object){
		global $events;
		$events['hardDeleted'] = TRUE;
	}
}