<!-- //Added ability to filter out all the trips that less than 10 meters/seconds -->
<!-- //And pagination so that the frontend can request for specific page, 10 records per-page by default, and frontend can pass a [record_per_page] query string to the end point. -->

<?php
class ApiController extends Controller {

	private int $record_per_page = 10;

    function index(){
        echo($this->jason(['error'=>1, 'message'=>'Please, specify the API endpoint.']));
    }
		
	function trips(){
        $criteria = ['covered_distance >=' => 10, 'duration >=' => 10];

        if(@$this->post['record_per_page']){
            $this->record_per_page = $this->post['record_per_page'];
        }
        
        $total_record = $this->model->countRecord('trips', ['params'=>$criteria]);

        $number_of_page = ceil ($total_record/$this->record_per_page);
        
        $current_page = preg_replace('/[^0-9]/i','',@$this->path[0]);
        $current_page = ($current_page and $current_page <= $number_of_page) ? $current_page : 1;

        $starting_point = ($current_page-1) * $this->record_per_page;
        
        $trips = $this->model
        ->select()
        ->table('trips')
        ->where($criteria)
        ->order('')
        ->limit([$starting_point,$this->record_per_page])
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
