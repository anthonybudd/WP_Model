<?php

/**
 * WPModel
 *
 * A simple drop-in abstract class for creating active
 * record style eloquent-esque models of Wordpress Posts.
 *
 * @todo
 * - Relationships
 * - Document Funtions
 * - Support data types: Array, Integer
 * @author     AnthonyBudd <anthonybudd94@gmail.com>
 */
Abstract Class WPModel
{
	protected $attributes = [];
	protected $data = [];
	protected $booted = FALSE;
	public $dirty = FALSE;
	public $ID = FALSE;
	public $prefix = '';
	public $title;
	public $content;
	public $_post;

	const PATCH_METHOD_SET_NULLS = 'set_nulls';


	/**
	 * Create a new instace with data
	 * @param Array $insert Asoc array of data to start the instace with
	 */
	public function __construct(Array $insert = [])
	{    
		$this->check();

		if(count($insert) !== 0){
			foreach($insert as $attribute => $value){
				if(in_array($attribute, array_merge($this->attributes, ['title', 'content']))){
					$this->set($attribute, $value);
				}
			}
		}

		$this->boot();
	}


	/**
	 * Create a new instace with data and save
	 * @param Array $insert Asoc array of data to start the instace with
	 */
	public static function insert(Array $insert = []){
		return ( new Deal($insert) )->save();
	}


	/**
	 * Check that the model does not use any
	 * parameters reseveed for WPModel
	 * @throws Exception
	 * @return true
	 */
	protected function check()
	{
		$unallowedAttributes = ['_id', 'ID', 'title', 'content', '_post'];

		foreach($this->attributes as $attribute){	
			if(in_array($attribute, $unallowedAttributes)){
				throw new Exception("The attribute name: {$attribute}, is reserved for WPModel");
			}
		}

		return TRUE;
	}


	/**
	 * [boot description]
	 * @return [type] [description]
	 */
	protected function boot()
	{
		$this->triggerEvent('booting');

		if(is_integer($this->ID)){
			$this->_post = get_post($this->ID);
			$this->title = $this->_post->post_title;
			$this->content = $this->_post->post_content;

			foreach($this->attributes as $attribute){
				$this->data[$attribute] = get_post_meta($this->ID, ($this->prefix.$attribute), TRUE);
			}
		}

		$this->booted = true;
		$this->triggerEvent('booted');
	}


	/**
	 * Fire Event if the event method exists
	 * @param  String $event event name
	 * @return Boolean
	 */
	protected function triggerEvent($event)
	{
		if(method_exists($this, $event)){
			$this->$event($this);
		}
	}


	/**
	 * Register the post type using the name
	 * propery as the post type name
	 * @param  Array  $args register_post_type() args
	 * @return Boolean
	 */
	public static function register($args = [])
	{
		$postTypeName = Self::getName();

		$defualts = [
			'public' => true,
			'label' => ucfirst($postTypeName)
		];

		register_post_type($postTypeName, array_merge($defualts, $args));

		Self::addHooks();

		return TRUE;
	}


	//-----------------------------------------------------
	// HOOKS
	// -----------------------------------------------------
	public static function addHooks(){
		add_action(('save_post'), [get_called_class(), 'onSave'], 9999999999);
	}
 
	public static function removeHooks(){
		remove_action(('save_post'), [get_called_class(), 'onSave'], 9999999999);
	}

	public static function onSave($ID){
		if(get_post_status($ID) == 'publish' &&
			Self::exists($ID)){ // If post is the right post type
			$post = Self::find($ID);
			$post->save();
		}
	}





	//-----------------------------------------------------
	// UTILITY METHODS
	// -----------------------------------------------------
	/**
	 * Get the name propery of the inherited class.
	 * @return String
	 */
	public static function getName(){
		$class = get_called_class();
		return ( new ReflectionClass($class) )->getProperty('name')->getValue( (new $class) );
	}


	/**
	 * Check if the post exists by Post ID
	 * @param  String|Integer  $id   Post ID
	 * @param  Boolean $type Require post to be the same post type
	 * @return Boolean
	 */
	public static function exists($id, $type = TRUE)
	{	
		if($type){
			if(
				(get_post_status($id) !== FALSE) &&
				(get_post_type($id) == Self::getName())){
				return TRUE;
			}
		}else{
			return (get_post_status($id) !== FALSE);
		}

		return FALSE;
	}


	/**
	 * Get property of model
	 * @param  property name $attribute [description]
	 * @return requested property or NULL
	 */
	public function get($attribute)
	{
		return @$this->data[$attribute];
	}


	/**
	 * Set property of model
	 * @param String $attribute
	 * @param String $value
	 */
	public function set($attribute, $value)
	{
		$this->data[$attribute] = $value;
	}


	/**
	 * Get the original post of the model
	 * @return WP_Post
	 */
	public function post()
	{
		return $this->_post;
	}


	public static function new(){
		$class = get_called_class();
		return new $class();
	}


	//-----------------------------------------------------
	// MAGIC METHODS
	// -----------------------------------------------------
	public function __set($attribute, $value)
	{
		if($this->booted){
			$this->dirty = true;
		}

		if(in_array($attribute, $this->attributes)){
			$this->data[$attribute] = $value;
		}
	}


	public function __get($attribute)
	{
		if(in_array($attribute, $this->attributes)){
			return $this->data[$attribute];
		}else if(method_exists($this, $attribute)){
			$clone = Self::findBypassBoot($this->ID);
			$relationship = $clone->$attribute();
			
			if(is_array($relationship)){
				return $relationship;
			}

			return NULL;
		}
	}

	//-----------------------------------------------------
	// RELATIONSHIPS 
	//-----------------------------------------------------
	public static function hasMany($model, $forignKey, $localKey)
	{
		if(in_array($localKey, ['id', 'ID', 'post_id'])){
			$localKey = '_id';
		}
		return $model::where($forignKey, $this->get($localKey));
	}


	//-----------------------------------------------------
	// FINDERS
	// -----------------------------------------------------
	public static function find($ID)
	{
		if(Self::exists($ID)){
			$class = Self::new();
			$class->ID = $ID;
			$class->boot();
			return $class;
		}

		return Self::new();
	}

	public static function findBypassBoot($ID)
	{
		$className = get_called_class();
		$class = new $className();
		$class->ID = $ID;
		return $class;
	}

	/**
	 * [findOrFail description]
	 * @throws  \Exception
	 * @param  Integer $id post ID
	 * @return Self
	 */
	public static function findOrFail($ID)
	{
		if(!Self::exists($ID)){
			throw new Exception("Post not found");
		}

		return Self::find($ID);
	}

	public static function all(){
		$args = [
			'post_type' => Self::getName(),
			'posts_per_page' => '9999999999999',
		];

		return ( new WP_Query($args) )->get_posts();
	}

	public static function list($metaKey = NULL){
		$self = get_called_class();
		$posts = $self::all();
		$return = [];

		foreach($posts as $post){
			if(is_null($metaKey)){
				$return[$post->ID] = $post;
			}if(in_array($metaKey, ['title', 'post_title'])){
				$return[$post->ID] = $post->post_title;
			}else{
				$return[$post->ID] = get_post_meta($post->ID, $metaKey, FALSE);
			}
		}

		return $return;
	}

	public static function finder($finder){
		$method = $finder.'Finder';

		if(!in_array($method, array_column(( new ReflectionClass(get_called_class()) )->getMethods(), 'name'))){
			throw new Exception("Finder not found");
		}

		$self = get_called_class();
		$args = $self::$method();

		if(!is_array($args)){
			throw new Exception("Finder Method musy treyun an array");
		}

		return ( new WP_Query($args) )->get_posts();
	}
	
	// -----------------------------------------------------
	// WHERE
	// -----------------------------------------------------
	public static function where($key, $value = FALSE)
	{
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

	public static function in($ids = [])
	{
		$results = [];
		if(!is_array($ids)){
			$ids = func_get_args();
		}

		foreach($ids as $key => $id){
			if(Self::exists($id)){
				$results[] = Self::find($id); 
			}
		}

		return $results;
	}


	//-----------------------------------------------------
	// SAVE
	// -----------------------------------------------------
	public function save($args = [])
	{
		$this->triggerEvent('saving');

		$overwrite = [
			'post_type' => $this->name
		];

		Self::removeHooks();

		if(is_integer($this->ID)){
			$defualts = [
				'ID'           => $this->ID,
				'post_title'   => $this->title,
				'post_content' => ($this->content !== NULL)? $this->content :  ' ',
			];

			wp_update_post(array_merge($defualts, $args, $overwrite));
		}else{
			$this->triggerEvent('inserting');
			$defualts = [
				'post_status'  => 'publish',
				'post_title'   => $this->title,
				'post_content' => ($this->content !== NULL)? $this->content :  ' ',
			];

			$this->ID = wp_insert_post(array_merge($defualts, $args, $overwrite));
			$this->_post = get_post($this->ID);
			$this->triggerEvent('inserted');
		}

		Self::addHooks();

		foreach($this->attributes as $attribute){
			update_post_meta(
				$this->ID,
				($this->prefix.$attribute),
				((@$this->data[$attribute] !== NULL)? $this->data[$attribute] : '') );
		}	

		update_post_meta($this->ID, '_id', $this->ID);
		$this->triggerEvent('saved');
		$this->dirty = FALSE;
		return $this;
	}


	// -----------------------------------------------------
	// DELETE
	// -----------------------------------------------------
	public function delete(){
		$this->triggerEvent('deleting');
		wp_delete_post($this->ID);
		$this->triggerEvent('deleted');
	}


	public function hardDelete(){
		$this->triggerEvent('hardDeleting');

		foreach($this->attributes as $attribute){
			delete_post_meta($this->ID, $attribute);
			$this->data[$attribute] = NULL;
		}

		wp_delete_post($this->ID);
		$this->triggerEvent('hardDeleted');
	}


	//-----------------------------------------------------
	// PATCHING 
	//-----------------------------------------------------
	public function patch($method = FALSE)
	{
		$this->triggerEvent('patching');

		foreach(array_merge($this->attributes, ['title', 'content']) as $attribute){
			switch($method) {
				case 'set_nulls':
					update_post_meta($this->ID, $attribute, @$_REQUEST[$attribute]);
					break;
				
				default:
					if(isset($_REQUEST[$attribute])){
						update_post_meta($this->ID, $attribute, @$_REQUEST[$attribute]);
					}
					break;
			}
		}

		$this->triggerEvent('patched');
	}


	public static function patchable($method = FALSE)
	{
		if(isset($_REQUEST['_model']) &&$_REQUEST['_model'] === Self::getName()){

			if(isset($_REQUEST['_id'])){
				$post = Self::find($_REQUEST['_id']);
			}else{
				$className = get_called_class();
				$post = new $className();
				$post->save();
			}

			$post->patch($method);
		}
	}
}

?>