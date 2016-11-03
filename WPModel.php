<?php

Abstract Class WPModel
{
    protected $attributes = [];
    protected $dirty = false;
    protected $booted = false;
    public $ID = FALSE;
    public $title;
    public $content;
    public $_post;

    public function __construct($post = FALSE){    	
    	$this->triggerEvent('booting');
    	if($post instanceof WP_Post){
    		$this->ID = $post->ID;
    		$this->boot();
    	}else if(is_integer($post)){
    		if(Self::exists($post)){
    			$this->ID = $post;
    			$this->boot();
    		}else{
    			throw new Exception("Post Does not exist");	
    		}
    	}
    	$this->booted = true;
    	$this->triggerEvent('booted');
    }

    protected function boot(){
    	$this->_post = Self::asPost($this->ID);
    	$this->title = $this->_post->post_title;
    	$this->content = $this->_post->post_content;

    	foreach($this->attributes as $attribute){
    		$this->$attribute = get_post_meta($this->ID, $attribute, TRUE);
    	}
    }

    protected function triggerEvent($event){
    	if(method_exists($this, $event)){
    		$this->$event($this);
    	}
    }

    public static function register($args = []){
		$name = ( new ReflectionClass(get_called_class()) )->newInstanceWithoutConstructor()->name;
		$defualts = [
			'public' => true,
			'label' => ucfirst($name)
		];
		register_post_type($name, array_merge($defualts, $args));
	}

    public static function exists($id){
    	return (get_post_status($id) !== FALSE);
    }

    public function asPost(){
		return get_post($this->ID);
	}

	public function get($attribute){
		return $this->$attribute;
	}

	public function set($attribute, $value){
		$this->$attribute = $value;
	}

	//-----------------------------------------------------
	// RELATIONSHIPS 
	//-----------------------------------------------------
	public static function hasMany($model, $forignKey, $localKey){
		return $model::where($forignKey, $this->get($localKey));
	}

	//-----------------------------------------------------
	// MAGIC
	// -----------------------------------------------------
	public function __set($name, $value){
		if($this->booted){
			$this->dirty = true;
		}

		$this->$name = $value;
	}

	public function __get($name){
		if(property_exists($this, $name)){
			// Security issue, Permissons not respected
			return $this->$name;
		}else if(method_exists($this, $name)){
			return $this->$name();
		}
	}

    //-----------------------------------------------------
	// FIND
	// -----------------------------------------------------
   	public static function find($id){
   		$class = get_called_class();
   		return new $class($id);
   	}

   	public static function in($ids = []){
   		$arr = [];
   		if(is_array($ids)){
			foreach($ids as $key => $id){
				if(Self::exists($id)){
					$arr[] = Self::find($id); 
				}
			}
		}else{
			foreach(func_get_args() as $key => $id){
				if(Self::exists($id)){
					$arr[] = Self::find($id); 
				}
			}
		}

		return $arr;
   	}

   	public static function where($key, $value = FALSE){
   		if(is_array($key)){
   			$params = [
				'meta_query' => []
			];


			foreach($key as $meta) {
				$params['meta_query'][] = [
					'key'       => $meta['key'],
					'value'     => $meta['value'],
					'compare'   => isset($meta['compare'])? $meta['compare'] : '=',
					'type'      => isset($meta['type'])? $meta['type'] : 'CHAR'
				];
			}

			$query = new WP_Query($params);
   		}else{
   			$query = new WP_Query([
				'meta_query'        => [
					[
						'key'       => $key,
						'value'     => $value,
						'compare'   => '=',
					],
				]
			]);
   		}

		

		$arr = [];
		foreach($query->get_posts() as $key => $post){
			$arr[] = Self::find($post->ID); 
		}

		return $arr;
   	}


	//-----------------------------------------------------
	// SAVE
	// -----------------------------------------------------
	public function save($args = []){
		$this->triggerEvent('saving');

		$overwrite = [
			'post_type' => $this->name
		];

		if(is_integer($this->ID)){
			$defualts = [
				'ID'           => $this->ID,
				'post_title'   => $this->title,
				'post_content' => $this->content,
			];

			wp_update_post(array_merge($defualts, $args, $overwrite));
		}else{
			$this->triggerEvent('inserting');
			$defualts = [
				'post_status'  => 'publish',
				'post_title'   => $this->title,
				'post_content' => $this->content,
			];

			$this->ID = wp_insert_post(array_merge($defualts, $args, $overwrite));
			$this->triggerEvent('inserted');
		}

		foreach($this->attributes as $attribute){
    		update_post_meta($this->ID, $attribute, $this->$attribute);
    	}
    	$this->triggerEvent('saved');
    	$this->dirty = FALSE;
	}
}

?>