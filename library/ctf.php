<?php
require 'model.php';
require 'controller.php';

class Ctf{

  private $path_info = null;
  private $url_segments = null;
	private $controller = null;
	private $model_name = null;
	private $action = null;
	
	public function main(){
    $this->path_info = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (!empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');

    //might be useless
    $this->setSegments();
    $this->setController();
    $this->setAction();

    if(!method_exists($this->controller, $this->action))
      echo(
        $this->controller->jason(
          ['error'=>1, 'message'=>'The selected API endpint does not exist.']
        )
      );
    else
        $this->controller->{$this->action}();
  }  
   
  public function setSegments(){
    $this->url_segments = !empty($this->path_info) ? array_filter(explode('/', $this->path_info)) : null;
  }
  
  public function setController(){
    $controller_file = APP."/controllers/apiController.php";
    $controller_name = "Api";
    $this->model_name = $controller_name;
	
    include($controller_file);
    $controller_class = $controller_name.'Controller';

    $path = "";
    if(is_array($this->url_segments)){
        $path = array_slice($this->url_segments, 1);
    }
    $this->controller = new $controller_class($this->model_name, $path);
  }
  
  
  public function setAction(){
      $this->action = $this->clean(!empty($this->url_segments[1]) ? $this->url_segments[1]:'index');
  }

  public function clean($string){
    return(preg_replace('/[^a-z]/i','',$string));
  }
}
