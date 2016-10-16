<?php

class Controller_Plugins extends Controller{
	
	public function before(){
		parent::before();
		
		if($this->logged_in !== true && $this->request->action() != "generate_xml" && $this->request->action() != 'generate_validate_json') {
			$this->request->redirect('users/login', 401);
		}
	}
	
	public function action_index(){
		$model_plugin = new Model_Plugin();
		
		$this->body = new View('plugins/index');
		if ($this->request->query('order') == 'lastmod') {
			$this->body->plugins = $model_plugin->order_by('last_modified','DESC')->find_all();
		} else {
			$this->body->plugins = $model_plugin->find_all();
		}
	}
	
	public function action_add(){
		$plugin = new Model_Plugin();
		
		$plugin->values($_POST);

		$plugin->last_modified = date('Y-m-d H:i:s');
                $plugin->last_mod_user = $this->user->username;
		
		$this->body = new View('plugins/add');
		
		$steps = array();
		$ansi_install_steps = array();
		$unicode_install_steps = array();
		$ansi_uninstall_steps = array();
		$unicode_uninstall_steps = array();
		
		if($this->request->method() === Request::POST){
			
			// validate the steps
			if(isset($_POST['ansi_install'], $_POST['ansi_install'][0]) === true){
				self::validate_steps($_POST['ansi_install'], $valid, $ansi_install_steps);
			}
			if(isset($_POST['unicode_install'], $_POST['unicode_install'][0]) === true){
				self::validate_steps($_POST['unicode_install'], $valid, $unicode_install_steps);
			}
			if(isset($_POST['ansi_uninstall'], $_POST['ansi_uninstall'][0]) === true){
				self::validate_steps($_POST['ansi_uninstall'], $valid, $ansi_uninstall_steps);
			}
			if(isset($_POST['unicode_uninstall'], $_POST['unicode_uninstall'][0]) === true){
				self::validate_steps($_POST['unicode_uninstall'], $valid, $unicode_uninstall_steps);
			}
			
			$versions = array();
			
			$prev_error = false;
			
			if(isset($_POST['version'], $_POST['version'][0]) === true){
				foreach($_POST['version'] as $version){
					$model = new Model_Plugins_Version();
					$model->values($version);
					
					try{
						$model->check();
					}catch(ORM_Validation_Exception $e){
						if($prev_error !== false){
							$prev_error->merge($e);
						}else{
							$prev_error = $e;
						}
					}
					
					$versions[] = $model;
				}
			}
			
			$plugin->url = Url::title($plugin->name);
			
			try{
				$plugin->check();
			}catch(ORM_Validation_Exception $e){
				if($prev_error !== false){
					$prev_error->merge($e);
				}else{
					$prev_error = $e;
				}
			}
			
			if($prev_error === false){
				Db::query(null, 'START TRANSACTION');
				
				try{
					$plugin->save();
					
					$steps = array($ansi_install_steps, $unicode_install_steps, $ansi_uninstall_steps, $unicode_uninstall_steps);
					foreach($steps as $i=>$type_steps){
						foreach($type_steps as $j=>$step){
							$step->save();
							
							$plugin->add_step($step, $i, $j);
						}
					}
					
					foreach($versions as $model_version){
						$model_version->plugin_id = $plugin->id;
						
						$model_version->save();
					}
					
					Db::query(null, 'COMMIT');
                    self::plugin_updated($plugin->name);
				}catch(Exception $e){
					Db::query(null, 'ROLLBACK');
					throw $e;
				}
				
				if($this->request->is_ajax() === true){
					$this->render = false;
					$this->response->headers('Content-Type', 'application/json');
					$this->response->body(json_encode(array(
						'url' => 'plugins/view/'.$plugin->url
					)));
					return;
				}else{
					$this->request->redirect('plugins/view/'.$plugin->url);
				}
			}else{
				if($this->request->is_ajax() === true){
					$this->render = false;
					$this->response->headers('Content-Type', 'application/json');
					$this->response->body(json_encode(array(
						'errors' => $prev_error->errors('add_plugin')
					)));
					return;
				}else{
					$this->body->errors = $errors;
				}
			}
		}else{
			$this->body->errors = array();
		}
		
		$this->body->validate = $plugin;
		$this->body->ansi_install_steps = array();
		$this->body->unicode_install_steps = array();
		$this->body->ansi_uninstall_steps = array();
		$this->body->unicode_uninstall_steps = array();
		$this->body->add = true;
	}
	
	public function action_edit(){
		$plugin = new Model_Plugin(array('url' => $this->request->param('id')));
		
		$plugin->values($_POST);
		
		$this->body = new View('plugins/add');
		
		$plugin->last_modified = date('Y-m-d H:i:s');
                $plugin->last_mod_user = $this->user->username;
		
		$steps = array();
		$ansi_install_steps = array();
		$unicode_install_steps = array();
		$ansi_uninstall_steps = array();
		$unicode_uninstall_steps = array();
		
		if($this->request->method() === Request::POST){
			
			// validate the steps
			if(isset($_POST['ansi_install'], $_POST['ansi_install'][0]) === true){
				self::validate_steps($_POST['ansi_install'], $valid, $ansi_install_steps);
			}
			if(isset($_POST['unicode_install'], $_POST['unicode_install'][0]) === true){
				self::validate_steps($_POST['unicode_install'], $valid, $unicode_install_steps);
			}
			if(isset($_POST['ansi_uninstall'], $_POST['ansi_uninstall'][0]) === true){
				self::validate_steps($_POST['ansi_uninstall'], $valid, $ansi_uninstall_steps);
			}
			if(isset($_POST['unicode_uninstall'], $_POST['unicode_uninstall'][0]) === true){
				self::validate_steps($_POST['unicode_uninstall'], $valid, $unicode_uninstall_steps);
			}
			
			$versions = array();
			
			$prev_error = false;
			
			if(isset($_POST['version'], $_POST['version'][0]) === true){
				foreach($_POST['version'] as $version){
					$model = new Model_Plugins_Version();
					$model->values($version);
					
					try{
						$model->check();
					}catch(ORM_Validation_Exception $e){
						if($prev_error !== false){
							$prev_error->merge($e);
						}else{
							$prev_error = $e;
						}
					}
					
					$versions[] = $model;
				}
			}
			
			$plugin->url = Url::title($plugin->name);
			
			try{
				$plugin->check();
			}catch(ORM_Validation_Exception $e){
				if($prev_error !== false){
					$prev_error->merge($e);
				}else{
					$prev_error = $e;
				}
			}
			
			if($prev_error === false){
				Db::query(null, 'START TRANSACTION');
				
				try{
					$plugin->save();
					
					$plugin->remove_all_steps();
					
					$steps = array($ansi_install_steps, $unicode_install_steps, $ansi_uninstall_steps, $unicode_uninstall_steps);
					foreach($steps as $i=>$type_steps){
						foreach($type_steps as $j=>$step){
							if($step instanceof Model_Steps_Download){
								$fileInfo = self::get_file_md5($step->url);
								
								if($fileInfo === false){
									// Error downloading file. Continue anyway. Could throw an error here.
								}else{
									$step->md5 = $fileInfo['md5'];
									
									Model_Hash::insert($plugin, $step, $fileInfo);
								}
							}
                            if ($step instanceof Model_Steps_Copy) {
                                if (!$step->isgpup) {
                                    $step->isgpup = 0;
                                }
                                if (!$step->replace) {
                                    $step->replace = 0;
                                }
                                if (!$step->recursive) {
                                    $step->recursive = 0;
                                }
                                if (!$step->validate) {
                                    $step->validate = 0;
                                }
                                if (!$step->backup) {
                                    $step->backup = 0;
                                }
                                if (!$step->is_dir) {
                                    $step->is_dir = 0;
                                }
                            }
							$step->save();
							
							$plugin->add_step($step, $i, $j);
						}
					}
					
					$plugin->remove_all_versions();
					
					foreach($versions as $model_version){
						$model_version->plugin_id = $plugin->id;
						
						$model_version->save();
					}
					
					Db::query(null, 'COMMIT');
                    self::plugin_updated($plugin->name);
				}catch(Exception $e){
					Db::query(null, 'ROLLBACK');
					throw $e;
				}
			
				if($this->request->is_ajax() === true){
					$this->render = false;
					$this->response->headers('Content-Type', 'application/json');
					$this->response->body(json_encode(array(
						'url' => 'plugins/view/'.$plugin->url
					)));
					
					return;
				}else{
					$this->request->redirect('plugins/view/'.$plugin->url);
				}
			}else{
				if($this->request->is_ajax() === true){
					$this->render = false;
					$this->response->headers('Content-Type', 'application/json');
					$this->response->body(json_encode(array(
						'errors' => $prev_error->errors('add_plugin')
					)));
					
					return;
				}else{
					$this->body->errors = $plugin->validate()->errors();
				}
			}
		}else{
			$this->body->errors = array();
		}
		
		$this->body->validate = $plugin;
		$this->body->versions = $plugin->versions->find_all();
		$this->body->ansi_install_steps = $plugin->ansi_install_steps();
		$this->body->unicode_install_steps = $plugin->unicode_install_steps();
		$this->body->ansi_uninstall_steps = $plugin->ansi_uninstall_steps();
		$this->body->unicode_uninstall_steps = $plugin->unicode_uninstall_steps();
		$this->body->add = false;
	}

	public function action_delete(){
		if($this->admin !== true){
			throw new HTTP_Exception_404();
		}
		
		$plugin = new Model_Plugin(array('url' => $this->request->param('id')));
		
		$plugin->remove_all_steps();
		$plugin->remove_all_versions();
		$plugin->delete();
		
		$this->request->redirect('plugins');
        self::plugin_updated($plugin->name);
	}
	
	public function action_view(){
		$plugin = new Model_Plugin(array('url' => $this->request->param('id')));
		
		if($plugin->id == null){
			//404
		}
		
		$this->body = new View('plugins/view');
		
		$this->body->plugin = $plugin;
		$this->body->ansi_install_steps = $plugin->ansi_install_steps();
		$this->body->unicode_install_steps = $plugin->unicode_install_steps();
		$this->body->ansi_uninstall_steps = $plugin->ansi_uninstall_steps();
		$this->body->unicode_uninstall_steps = $plugin->unicode_uninstall_steps();
		$this->body->versions = $plugin->versions->find_all();
	}
	
	public function action_get_md5(){
		$url = $_POST['url'];
		
		$this->response->headers('Content-Type', 'application/json');
		$this->render = false;
		
		$fileInfo = self::get_file_md5($url);
		
		if($fileInfo === false){
			$this->response->status(500);
			$this->response->body(json_encode(array(
				'error' => true
			)));
		}else{
			//TODO: perhaps this could be cached somewhere (session?) to save us downloading again?
			
			$this->response->body(json_encode($fileInfo));
		}
	}
	
	public function action_store_valid_hash(){
		$hash = new Model_Validhash();
		
		$hash->values($_POST);
		$hash->addedDate = date('Y-m-d');
		$hash->username = $this->user->username;
		
		$this->response->headers('Content-Type', 'application/json');
		$this->render = false;
		
		try {
			$hash->save();
			
			$this->response->body(json_encode(array(
				'error' => false
			)));	
		}catch(ORM_Validation_Exception $e){
			$this->response->body(json_encode(array(
				'error' => true
			)));	
		}
        self::validation_updated();
	}
	
	protected static function get_file_md5($url){
		$tmp_name = tempnam('/tmp', 'npd_');
		
		try{
			// We may have to use stream_context_create() with default_socket_timeout for php if the setting is too short
			
			// Copy doesn't encode spaces in urls so we do it ourselves. I don't know about other special symbols.
			//TODO: other special symbols?
			$success = copy(str_replace(' ', '%20', $url), $tmp_name);
			
			// This method here is more helpful for some things (http status codes) but uses more memory.
			/*$request = Request::factory($url)
				->execute();*/
		}catch(Exception $e){
			// Can't get the url!
			
			$success = false;
		}
		
		if($success === true){
			$archive = new ZipArchive();
			$success = $archive->open($tmp_name);
			
			if($success === true){
				for($i=0; $i<$archive->numFiles; $i++){
					$stats = $archive->statIndex($i);
					$fileList[] = $stats['name'];
					
					if($stats['size'] != 0){
						$hashList[] = md5($archive->getFromIndex($i));
					}else{
						$hashList[] = '';
					}
				}
				
				$archive->close();
				
				$ret = array(
					'md5' => md5_file($tmp_name),
					'files' => $fileList,
					'hashes' => $hashList,
				);
			}else{
				// Not a zip archive
				$ret = array(
					'md5' => md5_file($tmp_name)
				);
			}
		}else{
			$ret = false;
		}
		
		unlink($tmp_name);
		
		return $ret;
	}
	
	public function action_add_xml(){
		$this->body = new View('plugins/add-xml');
		
		if(isset($_POST['xml']) === true){
			libxml_use_internal_errors();
			
			try{
				$xml = new SimpleXMLElement($_POST['xml']);
			}catch(Exception $e){
				// Really badly formed xml
				$this->body->error = 'Your xml is badly formed.';
			}
			
			//die(print_r(libxml_get_errors(), true));
			
			if(isset($xml) === true && $xml !== false){
				if($xml->getName() == 'plugin'){
					$plugin = new Model_Plugin();
					
					$plugin_url = Url::title($xml['name']);
					
					$plugin->last_modified = date('Y-m-d H:i:s');
			                $plugin->last_mod_user = $this->user->username;

					$plugin->name = $xml['name'];
					$plugin->url = $plugin_url;
					
					//TODO: Should these be run through htmldecodechars?
					$plugin->unicode_version = (string) $xml->unicodeVersion;
					$plugin->ansi_version = (string) $xml->ansiVersion;
					$plugin->homepage = (string) $xml->homepage;
					$plugin->description = str_replace('\n', "\n", (string) $xml->description);
					$plugin->author = (string) $xml->author;
					$plugin->source_url = (string) $xml->sourceUrl;
					$plugin->latest_update = str_replace('\n', "\n", (string) $xml->latestUpdate);
					$plugin->stability = (string) $xml->stability;
					
					// Versions
					$versions = array();
					
					if(isset($xml->versions) === true){
						foreach($xml->versions->children() as $version){
							$model_version = new Model_Plugins_Version();
							$model_version->number = (string) $version['number'];
							$model_version->md5 = (string) $version['md5'];
							
							$versions[] = $model_version;
						}
					}
					
					// Aliases
					if(isset($xml->aliases) === true){
						$aliases = array();
						foreach($xml->aliases->children() as $alias){
							$aliases[] = (string) $alias['name'];
						}
						$plugin->aliases = implode(', ', $aliases);
					}
					
					// Dependencies
					if(isset($xml->dependencies) === true){
						$dependencies = array();
						foreach($xml->dependencies->children() as $dependency){
							$dependencies[] = (string) $dependency['name'];
						}
						$plugin->dependencies = implode(', ', $dependencies);
					}
					
					// Install and uninstall steps
					// These can be in two formats!!
					
					$ansi_install_steps = array();
					$unicode_install_steps = array();
					
					if(isset($xml->install) === true){
						foreach($xml->install->children() as $elem){
							$name = $elem->getName();
							if($name == 'unicode'){
								foreach($elem->children() as $childElem){
									$unicode_install_steps[] = self::parse_xml_step($childElem);
								}
							}elseif($name == 'ansi'){
								foreach($elem->children() as $childElem){
									$ansi_install_steps[] = self::parse_xml_step($childElem);
								}
							}else{
								// We do this 2x because we want two seperate model instances
								$ansi_install_steps[] = self::parse_xml_step($elem);
								$unicode_install_steps[] = self::parse_xml_step($elem);
							}
						}
					}
					
					$ansi_uninstall_steps = array();
					$unicode_uninstall_steps = array();
					
					if(isset($xml->uninstall) === true){
						foreach($xml->uninstall->children() as $elem){
							$name = $elem->getName();
							if($name == 'unicode'){
								foreach($elem->children() as $childElem){
									$unicode_uninstall_steps[] = self::parse_xml_step($childElem);
								}
							}elseif($name == 'ansi'){
								foreach($elem->children() as $childElem){
									$ansi_uninstall_steps[] = self::parse_xml_step($childElem);
								}
							}else{
								// We do this 2x because we want two seperate model instances
								$ansi_uninstall_steps[] = self::parse_xml_step($elem);
								$unicode_uninstall_steps[] = self::parse_xml_step($elem);
							}
						}
					}
					
					// These should probably be validated properly first.
					$plugin->check();
					
					Db::query(null, 'TRANSACTION START');
					
					try{
						$plugin->save();
						
						foreach($versions as $model_version){
							$model_version->plugin_id = $plugin->id;
							
							$model_version->save();
						}
						
						foreach($ansi_install_steps as $i=>$step){
							$step->save();
							
							$plugin->add_step($step, 0, $i);
						}
						
						foreach($unicode_install_steps as $i=>$step){
							$step->save();
							
							$plugin->add_step($step, 1, $i);
						}
						
						foreach($ansi_uninstall_steps as $i=>$step){
							$step->save();
							
							$plugin->add_step($step, 2, $i);
						}
						
						foreach($unicode_uninstall_steps as $i=>$step){
							$step->save();
							
							$plugin->add_step($step, 3, $i);
						}
						
						Db::query(null, 'COMMIT');
						
						$this->request->redirect('plugins/view/'.$plugin_url);
					}catch(Exception $e){
						Db::query(null, 'ROLLBACK');
						
						throw $e;
					}
				}
			}
			
			$this->body->xml = $_POST['xml'];
		}
	}

	public function action_edit_xml(){
		$this->body = new View('plugins/add-xml');
		
		if(isset($_POST['xml']) === true){
			libxml_use_internal_errors();
			
			try{
				$xml = new SimpleXMLElement($_POST['xml']);
			}catch(Exception $e){
				// Really badly formed xml
				$this->body->error = 'Your xml is badly formed.';
			}
			
			if(isset($xml) === true && $xml !== false){
				if($xml->getName() == 'plugin'){
					$plugin = new Model_Plugin(array('url' => $this->request->param('id')));
					
					$plugin_url = Url::title($xml['name']);
					
					$plugin->last_modified = date('Y-m-d H:i:s');
			                $plugin->last_mod_user = $this->user->username;

					$plugin->name = $xml['name'];
					$plugin->url = $plugin_url;
					$plugin->unicode_version = (string) $xml->unicodeVersion;
					$plugin->ansi_version = (string) $xml->ansiVersion;
					$plugin->homepage = (string) $xml->homepage;
					$plugin->description = str_replace('\n', "\n", (string) $xml->description);
					$plugin->author = (string) $xml->author;
					$plugin->source_url = (string) $xml->sourceUrl;
					$plugin->latest_update = (string) str_replace('\n', "\n", $xml->latestUpdate);
					$plugin->stability = (string) $xml->stability;
					
					// Versions
					$versions = array();
					
					if(isset($xml->versions) === true){
						foreach($xml->versions->children() as $version){
							$model_version = new Model_Plugins_Version();
							$model_version->number = (string) $version['number'];
							$model_version->md5 = (string) $version['md5'];
							
							$versions[] = $model_version;
						}
					}
					
					// Aliases
					if(isset($xml->aliases) === true){
						$aliases = array();
						foreach($xml->aliases->children() as $alias){
							$aliases[] = (string) $alias['name'];
						}
						$plugin->aliases = implode(', ', $aliases);
					}
					
					// Dependencies
					if(isset($xml->dependencies) === true){
						$dependencies = array();
						foreach($xml->dependencies->children() as $dependency){
							$dependencies[] = (string) $dependency['name'];
						}
						$plugin->dependencies = implode(', ', $dependencies);
					}
					
					$ansi_install_steps = array();
					$unicode_install_steps = array();
					
					if(isset($xml->install) === true){
						foreach($xml->install->children() as $elem){
							$name = $elem->getName();
							if($name == 'unicode'){
								foreach($elem->children() as $childElem){
									$unicode_install_steps[] = self::parse_xml_step($childElem);
								}
							}elseif($name == 'ansi'){
								foreach($elem->children() as $childElem){
									$ansi_install_steps[] = self::parse_xml_step($childElem);
								}
							}else{
								// We do this 2x because we want two seperate model instances
								$ansi_install_steps[] = self::parse_xml_step($elem);
								$unicode_install_steps[] = self::parse_xml_step($elem);
							}
						}
					}
					
					$ansi_uninstall_steps = array();
					$unicode_uninstall_steps = array();
					
					if(isset($xml->uninstall) === true){
						foreach($xml->uninstall->children() as $elem){
							$name = $elem->getName();
							if($name == 'unicode'){
								foreach($elem->children() as $childElem){
									$unicode_uninstall_steps[] = self::parse_xml_step($childElem);
								}
							}elseif($name == 'ansi'){
								foreach($elem->children() as $childElem){
									$ansi_uninstall_steps[] = self::parse_xml_step($childElem);
								}
							}else{
								// We do this 2x because we want two seperate model instances
								$ansi_uninstall_steps[] = self::parse_xml_step($elem);
								$unicode_uninstall_steps[] = self::parse_xml_step($elem);
							}
						}
					}
					
					// These should probably be validated properly first.
					$plugin->check();
					
					Db::query(null, 'TRANSACTION START');
					
					try{
						$plugin->save();
						
						$plugin->remove_all_versions();
						
						foreach($versions as $model_version){
							$model_version->plugin_id = $plugin->id;
							
							$model_version->save();
						}
						
						$plugin->remove_all_steps();
						
						foreach($ansi_install_steps as $i=>$step){
							$step->save();
							
							$plugin->add_step($step, 0, $i);
						}
						
						foreach($unicode_install_steps as $i=>$step){
							$step->save();
							
							$plugin->add_step($step, 1, $i);
						}
						
						foreach($ansi_uninstall_steps as $i=>$step){
							$step->save();
							
							$plugin->add_step($step, 2, $i);
						}
						
						foreach($unicode_uninstall_steps as $i=>$step){
							$step->save();
							
							$plugin->add_step($step, 3, $i);
						}
						
						Db::query(null, 'COMMIT');
						
						$this->request->redirect('plugins/view/'.$plugin_url);
					}catch(Exception $e){
						Db::query(null, 'ROLLBACK');
						
						throw $e;
					}
				}
			}
			
			$this->body->xml = $_POST['xml'];
		}
	}
	
	protected static function parse_xml_step($childElem){
		$model = null;
		$childName = $childElem->getName();
		
		//var_dump((string) $childElem); exit;
		
		if($childName == 'download'){
			$model = new Model_Steps_Download();
			
			$model->url = (string) $childElem;
		}elseif($childName == 'copy'){
			$model = new Model_Steps_Copy();
			
			$model->from = (string) $childElem['from'];
			
			if(isset($childElem['to']) === true){
				$model->to = (string) $childElem['to'];
				$model->is_dir = 1;
			}elseif(isset($childElem['toFile']) === true){
				$model->to = (string) $childElem['toFile'];
				$model->is_dir = 0;
			}else{
				return null;
			}
			
			$model->validate = $childElem['validate'] == 'true' ? 1 : 0;
			$model->backup = $childElem['backup'] == 'true' ? 1 : 0;
			$model->replace = $childElem['replace'] == 'true' ? 1 : 0;
			$model->recursive = $childElem['recursive'] == 'true' ? 1 : 0;
			$model->isgpup = $childElem['isGpup'] == 'true' ? 1 : 0;
		}elseif($childName == 'delete'){
			$model = new Model_Steps_Delete();
			
			$model->delete = (string) $childElem['file'];
		}elseif($childElem == 'run'){
			$model = new Model_Steps_Run();
			
			$model->run = (string) $childElem['run'];
			$model->arguments = (string) $childElem['arguments'];
			$model->outside = $childElem['outside'] == 'true' ? 1 : 0;
		}
		
		return $model;
	}
	
	public function action_generate_xml(){
		libxml_use_internal_errors(true);
		
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><plugins />');
		
		// Prepend XML from config file
		
		$prepend = Kohana::find_file('config', 'xmlprepend', 'xml'); //config ignores array or string option
		
		if(count($prepend) === 0){
			echo "The XML file config/xmlprepend.xml could not be read.";
			
			exit;
		}
		
		$prependX = simplexml_load_file($prepend[0]);
		
		if($prependX === false){
			echo "The XML file config/xmlprepend.xml is not well formed.<br>\n";
			
			foreach(libxml_get_errors() as $error) {
				echo "&nbsp;&nbsp;Line ", $error->line, ': ', $error->message, "<br>\n";
			}
			
			exit;
		}
		
		SimpleXML::appendXML($xml, $prependX);
		
		unset($prependX); // Free memory
		
		// Generate the XML from the database
		
		$plugins = ORM::factory('Plugin')->find_all();
		
		foreach($plugins as $plugin){
			$pluginX = $xml->addChild('plugin');
			// Attributes don't seem to need HTML::chars
			$pluginX['name'] = $plugin->name;
			
			if($plugin->ansi_version && !$plugin->library){
				$pluginX->addChild('ansiVersion', $plugin->ansi_version);
			}
			if($plugin->unicode_version && !$plugin->library){
				$pluginX->addChild('unicodeVersion', $plugin->unicode_version);
			}
			
			if($plugin->aliases){
				$aliasesX = $pluginX->addChild('aliases');
				
				$aliases = explode(', ', HTML::chars($plugin->aliases));
				
				foreach($aliases as $alias){
					$aliasX = $aliasesX->addChild('alias');
					$aliasX['name'] = $alias;
				}
			}
			
			if($plugin->dependencies){
				$dependenciesX = $pluginX->addChild('dependencies');
				
				$dependencies = explode(', ', HTML::chars($plugin->dependencies));
				
				foreach($dependencies as $dependency){
					$dependencyX = $dependenciesX->addChild('plugin');
					$dependencyX['name'] = $dependency;
				}
			}
			
			if($plugin->description){
				$pluginX->addChild('description', HTML::chars(str_replace("\n", '\n', $plugin->description)));
			}
			
			if($plugin->library){
				$pluginX->addChild('isLibrary', 'true');
			}

			if($plugin->author){
				$pluginX->addChild('author', HTML::chars($plugin->author));
			}
			if($plugin->homepage){
				$pluginX->addChild('homepage', HTML::chars($plugin->homepage));
			}
			if($plugin->source_url){
				$pluginX->addChild('sourceUrl', HTML::chars(str_replace("\n", '\n', $plugin->source_url)));
			}
			
			//TODO: dependencies
			
			$versions = $plugin->versions->find_all();
			
			if(count($versions) > 0){
				$versionsX = $pluginX->addChild('versions');
				
				foreach($versions as $version){
					$versionX = $versionsX->addChild('version');
					
					$versionX['number'] = $version->number;
					$versionX['md5'] = $version->md5;
					// Don't include version comments here as they don't add any value in the XML and just take up space.
				}
			}
			
			if($plugin->latest_update){
				$pluginX->addChild('latestUpdate', HTML::chars(str_replace("\n", '\n', $plugin->latest_update)));
			}
			
			if($plugin->stability){
				$pluginX->addChild('stability', HTML::chars($plugin->stability));
			}
			
			if($plugin->min_version){
				$pluginX->addChild('minVersion', $plugin->min_version);
			}
			if($plugin->max_version){
				$pluginX->addChild('maxVersion', $plugin->max_version);
			}
			
			$installX = $pluginX->addChild('install');

			if($plugin->unicode_version){
				$unicodeX = $installX->addChild('unicode');
				
				self::add_steps($unicodeX, $plugin->unicode_install_steps());
				
				$steps = $plugin->unicode_uninstall_steps();
				
				if(count($steps) > 0){
					$uninstallX = $pluginX->addChild('remove');
					
					$unicodeX = $uninstallX->addChild('unicode');
					
					self::add_steps($unicodeX, $steps);
				}
			}
			
			if($plugin->ansi_version){
				$ansiX = $installX->addChild('ansi');
				
				self::add_steps($ansiX, $plugin->ansi_install_steps());
				
				$steps = $plugin->ansi_uninstall_steps();
				
				if(count($steps) > 0){
					if(isset($uninstallX) === false){
						$uninstallX = $pluginX->addChild('remove');
					}
					
					$ansiX = $uninstallX->addChild('ansi');
					
					self::add_steps($ansiX, $steps);
				}
			}
		}
		
		Session::instance()->write();
		
		$this->response->headers('Content-Type', 'text/xml');
		
		if($this->request->query('download')){
			$this->response->headers('Content-Disposition', 'attachment; filename="plugins.xml');
		}
		
		// @see http://www.php.net/manual/en/function.gzdeflate.php#69046
		
		if(Request::accept_encoding('gzip') == true){
			$this->response->headers('Content-Encoding', 'gzip');
			$this->response->send_headers();
			
			ob_end_flush();
			
			echo gzencode($xml->asXML(), 9);
		}elseif(Request::accept_encoding('deflate') == true){
			$this->response->headers('Content-Encoding', 'deflate');
			$this->response->send_headers();
			
			ob_end_flush();
			
			echo gzcompress($xml->asXML(), 9);
		}else{
			$this->response->send_headers();
			
			ob_end_flush();
			
			echo $xml->asXML();
		}
		
		exit;
	}
	
	
	
	public function action_generate_sql(){
		Session::instance()->write();
		
		$this->response->headers('Content-Type', 'text/plain');
		
		if($this->request->query('download')){
			$this->response->headers('Content-Disposition', 'attachment; filename="files.sql');
		}
		
		$hashes = ORM::factory('Validhash')->find_all();
		$prepend = Kohana::find_file('config', 'files_table', 'sql');
		
		$sql = '';
		
		if (count($prepend) !== 0){
			$sql .= file_get_contents($prepend[0]);
		}
		
		$sql .= "BEGIN TRANSACTION;\r\n";
		
		
		foreach($hashes as $hash){
			
			if ($hash->hash != ''){ // Will this ever have no hash?
				$query = Db::insert('FileHash', array('md5sum', 'filename', 'pluginName', 'addedDate', 'status'));
				$query->values(array(
					$hash->hash,
					$hash->file,
					NULL,
					$hash->addedDate,
					$hash->response,
				));
				$sql .= $query. ';  --' . $hash->username . "\r\n";
			}
		}
		
		
		
		$sql .= "COMMIT;\r\n";
		
		// @see http://www.php.net/manual/en/function.gzdeflate.php#69046
		
		if(Request::accept_encoding('gzip') == true){
			$this->response->headers('Content-Encoding', 'gzip');
			$this->response->send_headers();
			
			ob_end_flush();
			
			echo gzencode($sql, 9);
		}elseif(Request::accept_encoding('deflate') == true){
			$this->response->headers('Content-Encoding', 'deflate');
			$this->response->send_headers();
			
			ob_end_flush();
			
			echo gzcompress($sql, 9);
		}else{
			$this->response->send_headers();
			
			ob_end_flush();
			
			echo $sql;
		}
		
		exit;
	}

	public function action_generate_validate_json(){
		Session::instance()->write();

		$this->response->headers('Content-Type', 'text/json');

		if($this->request->query('download')){
			$this->response->headers('Content-Disposition', 'attachment; filename="validate.json"');
		}

		$hashes = ORM::factory('Validhash')->find_all();
		$prepend = Kohana::find_file('config', 'files_table', 'sql');

		$validate = '';

		$validate .= "{\r\n";

		$first = true;

		foreach($hashes as $hash){

			if ($hash->hash) {

				if (!$first) {
					$validate .= ",\r\n";
				}
				$first = false;
				$validate .= '"' . $hash->hash . '": "' . $hash->response . '"';
			}
		}



		$validate .= "}\r\n";

		// @see http://www.php.net/manual/en/function.gzdeflate.php#69046

		if(Request::accept_encoding('gzip') == true){
			$this->response->headers('Content-Encoding', 'gzip');
			$this->response->send_headers();

			ob_end_flush();

			echo gzencode($validate, 9);
		}elseif(Request::accept_encoding('deflate') == true){
			$this->response->headers('Content-Encoding', 'deflate');
			$this->response->send_headers();

			ob_end_flush();

			echo gzcompress($validate, 9);
		}else{
			$this->response->send_headers();

			ob_end_flush();

			echo $validate;
		}

		exit;
	}
	
	protected static function add_steps($xml, $steps){
		foreach($steps as $step){
			if($step->from !== null){
				$copyX = $xml->addChild('copy');
				
				$copyX['from'] = $step->from;
				
				if($step->is_dir){
					$copyX['to'] = $step->to;
				}else{
					$copyX['toFile'] = $step->to;
				}
				
				if($step->validate){
					$copyX['validate'] = 'true';
				}
				if($step->backup){
					$copyX['backup'] = 'true';
				}
				if($step->replace){
					$copyX['replace'] = 'true';
				}
				if($step->isgpup){
					$copyX['isGpup'] = 'true';
				}
				if($step->recursive){
					$copyX['recursive'] = 'true';
				}

			}elseif($step->url !== null){
				$downloadX = $xml->addChild('download', HTML::chars($step->url));
			}elseif($step->run !== null){
				$runX = $xml->addChild('run');
				
				$runX['file'] = $step->run;
				$runX['arguments'] = $step->arguments;
				$runX['outsideNpp'] = $step->outside;
			}else{
				$deleteX = $xml->addChild('delete');
				
				$deleteX['file'] = $step->delete;
			}
		}
	}
	
	protected static function validate_steps($steps, & $valid, & $models){
		foreach($steps as $step){
			if(isset($step['copy']) === true){
				$model = new Model_Steps_Copy();
				$model->values($step['copy']);
				
				if(isset($step['copy']['variable']) === true){
					$model->variable = $step['copy']['variable'];
				}
			}elseif(isset($step['download']) === true){
				$model = new Model_Steps_Download();
				$model->values($step['download']);
			}elseif(isset($step['delete']) === true){
				$model = new Model_Steps_Delete();
				$model->values($step['delete']);
				
				if(isset($step['delete']['variable']) === true){
					$model->variable = $step['delete']['variable'];
				}
			}elseif(isset($step['run']) === true){
				$model = new Model_Steps_Run();
				$model->values($step['run']);
			}else{
				$valid = false;
			}
			
			$valid = $valid === true ? $model->check() : false;
			
			$models[] = $model;
		}
	}

    private function plugin_updated($plugin_name) {
        $handle = FALSE;
        try {
            $handle = fopen("/www/updates/plugin.update", "x");
        } catch (Exception $e) {}
        if ($handle) {
            fwrite($handle, $plugin_name);
            fclose($handle);
        }
    }

    private function validation_updated() {
        $handle = FALSE;
        try {
            $handle = fopen("/www/updates/validation.update", "x");
        } catch (Exception $e) {}
        
        if ($handle) {
            fclose($handle);
        }
    }

}
