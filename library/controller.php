<?php
class Controller {
   
	public $path;
	public $post;
   	public $model;

	function __construct($_model, $path){
		$this->path = $this->_clean($path);
		$this->post = $this->_clean($_POST);
		$this->setModel($_model);
	}
	
	public function setModel($model_name){
      	$model_name = ucfirst($model_name);
      	$model_file = APP."/models/{$model_name}.php";
      	if(file_exists($model_file)){
	  	 	include($model_file);
		 	$model_class  = $model_name;
		 	$this->model = new $model_class();
      	}
  	}

	private function _clean($data){
   		//2do cleaning
   		return $data;
   	}

	public static function clean($string){
		return(preg_replace('/[^a-zA-Z]/i','',$string));
	}

	function jason($r = '', $debug = false){
		if(!$debug)
		header('Content-type: application/json');
		return(json_encode($r));
	}
	
	 function convertToTime($seconds){
		$hours = floor($seconds / 3600);
		$mins = floor($seconds / 60 % 60);
		$secs = floor($seconds % 60);

		return("{$hours}h {$mins}m {$secs}s");
	}

	function convertToKm($distance){
		return(round($distance / 1000, 2) . 'km');
	} 
	
}