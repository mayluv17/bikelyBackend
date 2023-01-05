<?php
class ApiController extends Controller {
	
    function index(){
        echo($this->jason(['error'=>1, 'message'=>'Please, specify the API endpoint.']));
    }
		
	function trips(){
        
        $trips = $this->model
        ->select()
        ->table('trips')
        ->limit([0,25])
        ->result();

        if(is_array($trips)){
            $content['success'] = 1;
            $content['data'] = $trips;
            echo $this->jason($content);
        }
        else{
            echo $this->jason(['error'=>1, 'message'=>'No records found.']);
        }
	}
}
