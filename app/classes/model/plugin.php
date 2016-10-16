<?php

class Model_Plugin extends ORM{
	
	protected $_has_many = array(
		'copy_steps' => array('model' => 'steps_copy', 'through' => 'plugins_steps', 'far_key' => 'step_id'),
		'download_steps' => array('model' => 'steps_download', 'through' => 'plugins_steps', 'far_key' => 'step_id'),
		'versions' => array('model' => 'plugins_version'),
	);
	
	protected $_sorting = array(
		'name' => 'ASC'
	);
	
	public function rules(){
		return array(
			'name' => array(
				array('not_empty'),
				array('min_length', array(':value', 1)),
				array('max_length', array(':value', 255)),
				array(array($this, 'unique'), array('name', ':value')),
			),
			'unicode_version' => array(
				array('max_length', array(':value', 10)),
				//Incidentally, the version number must in the format "v.x.y.z" where v,x,y, and z are integers. It can be a minimum of 2 elements and maximum of 4 (so x.y, x.y.z, or v.x.y.z).
				array('regex', array(':value', '/^[0-9]{1,3}(:?\.[0-9]{1,3}){0,3}$/')),
			),
			'ansi_version' => array(
				array('max_length', array(':value', 10)),
				array('regex', array(':value', '/^[0-9]{1,3}(:?\.[0-9]{1,3}){0,3}$/')),
			),
			'description' => array(
				array('max_length', array(':value', 1000)),
			),
			'full_description' => array(
				array('max_length', array(':value', 5000)), // Currently not used
			),
			'author' => array(
				array('min_length', array(':value', 1)),
				array('max_length', array(':value', 255)),
			),
			'homepage' => array(
				array('max_length', array(':value', 200)),
			),
			'source_url' => array(
				array('max_length', array(':value', 200)),
			),
			'lastest_update' => array(
				array('max_length', array(':value', 1000)),
			),
			'stability' => array(
				array('max_length', array(':value', 255)),
			),
			'aliases' => array(
				array('max_length', array(':value', 255)),
			),
			'dependencies' => array(
				array('max_length', array(':value', 500)),
			),
		);
	}
	
	public function filters(){
		return array(
			'homepage' => array(
				array('Model_Plugin::makeHttp'),
			),
			'source_url' => array(
				array('Model_Plugin::makeHttp'),
			),
			'stability' => array(
				array('Model_Plugin::goodToNull'),
			),
			'aliases' => array(
				array('Model_Plugin::formatAliases'),
			),
			'dependencies' => array(
				array('Model_Plugin::formatAliases'),
			),
			'library' => array(
				array('Model_Plugin::makeBool'),
			),
			TRUE => array(
				array('trim'),
			),
		);
	}
	
	public function ansi_install_steps(){
		return self::steps(0, $this->id);
	}
	
	public function unicode_install_steps(){
		return self::steps(1, $this->id);
	}
	
	public function ansi_uninstall_steps(){
		return self::steps(2, $this->id);
	}
	
	public function unicode_uninstall_steps(){
		return self::steps(3, $this->id);
	}
	
	protected static function steps($plugin_type, $id){
		return ORM::factory('Plugins_Step')
			->select('*')
			->join('steps_downloads', 'left')->on('steps_downloads.id', '=', 'plugins_step.step_id')->on('type', '=', Db::expr(0))
			->join('steps_copies', 'left')->on('steps_copies.id', '=', 'plugins_step.step_id')->on('type', '=', Db::expr(1))
			->join('steps_run', 'left')->on('steps_run.id', '=', 'plugins_step.step_id')->on('type', '=', Db::expr(2))
			->join('steps_delete', 'left')->on('steps_delete.id', '=', 'plugins_step.step_id')->on('type', '=', Db::expr(3))
			->where('plugins_step.plugin_id', '=', $id)
			->where('plugins_step.plugin_type', '=', $plugin_type)
			->find_all();
	}
	
	public function add_step($step, $plugin_type, $order){
		 return Db::insert('plugins_steps', array(
			'plugin_id',
			'step_id',
			'`order`',
			'plugin_type',
			'type'
		))->values(array(
			$this->id,
			$step->id,
			$order,
			$plugin_type,
			$step->step_type
		))->execute();
	}
	
	public function remove_all_steps(){
		// Deleting from plugins_steps will not remove steps_downloads etc from the db.
		/*ORM::factory('Plugins_Step')
			->where('plugins_steps.plugin_id', '=', $this->id)
			->delete_all();*/
			
		Db::query(Database::DELETE, '
DELETE steps_copies, steps_delete, steps_downloads, steps_run, plugins_steps FROM plugins_steps
LEFT JOIN steps_downloads ON steps_downloads.id = plugins_steps.step_id AND type = 0
LEFT JOIN steps_copies ON steps_copies.id = plugins_steps.step_id AND type = 1
LEFT JOIN steps_run ON steps_run.id = plugins_steps.step_id AND type = 2
LEFT JOIN steps_delete ON steps_delete.id = plugins_steps.step_id AND type = 3
WHERE plugins_steps.plugin_id = :plugin_id'
		)->param(':plugin_id', $this->id)
		->execute();

	}
	
	public function remove_all_versions(){
		Db::query(Database::DELETE, 'DELETE FROM plugins_versions
WHERE plugins_versions.plugin_id = :id'
		)->param(':id', $this->id)
		->execute();
	}
	
	/* validation */
// Make sure the urls start with http or https unless there is no url
	public static function makeHttp($address){
		if($address != ''){
			if(preg_match('#^https?://#i', $address) === 0){
				$address = 'http://'.$address;
			}
		}
		return $address;
	}
	
// Stability only needs to be entered if it's not "good". It can be other things.
	public static function goodToNull($stability){
		if($stability == 'good'){
			return NULL;
		}
		return $stability;
	}
	
// There can be more than one alias. Callback to format these and make commas uniform ("a, b", "a ,    b", "a,b"). 
	public static function formatAliases($aliases){
		return preg_replace('/\s*,\s*/', ', ', $aliases);
	}

// Used for checkboxes. Makes truey values 1 and everything else 0
	public static function makeBool($val){
		if($val == 1){
			return 1;
		}else{
			return 0;
		}
	}
} 
