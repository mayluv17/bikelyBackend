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
            $content['total_record'] = $total_record;
            $content['number_of_page'] = $number_of_page;
            $content['record_per_page'] = $this->record_per_page;

            foreach ($trips as $trip) {
                $trip['distance'] = $this->convertToKm($trip['covered_distance']);
                $trip['time'] = $this->convertToTime($trip['duration']);
                $data[] = $trip;
            }
            $content['data'] = $data;
            echo $this->jason($content);
        }
        else{
            echo $this->jason(['error'=>1, 'message'=>'No records found.']);
        }
	}

    function stations(){
        $section = @$this->path[0];
        if($section == 'details'){
            $this->stationdetails();
            return;
        }

        $stations = $this->model
        ->select()
        ->table('stations')
        ->result();

        if(is_array($stations)){
            $content['success'] = 1;
            $content['total_record'] = count($stations);

            foreach ($stations as $station) {
                $data[] = $station;
            }
            $content['data'] = $data;
            echo $this->jason($content);
        }
        else{
            echo $this->jason(['error'=>1, 'message'=>'No records found.']);
        }
	}

    function stationdetails(){
        $id = @$this->path[1];

        $station = $this->model
        ->select([
            's.*',
            "(SELECT COUNT(id) FROM trips WHERE departure_station_id='$id') departure_station_total",
            "(SELECT COUNT(id) FROM trips WHERE return_station_id='$id') return_station_total",
            "(SELECT AVG(covered_distance) FROM trips WHERE departure_station_id='$id') average_starting",
            "(SELECT AVG(covered_distance) FROM trips WHERE return_station_id='$id') average_ending"
        ])
        ->table('stations s')
        ->where(['s.id'=>$id])
        ->result()[0];

        $top_5_return_stations = $this->model
        ->select(['return_station_id','return_station_name','COUNT(id) total_trips'])
        ->table('trips')
        ->where(['departure_station_id'=>$id])
        ->group('return_station_id')
        ->order('total_trips DESC')
        ->limit([0,5])
        ->result();

        $top_5_departure_stations = $this->model
        ->select(['departure_station_id','departure_station_name','COUNT(id) total_trips'])
        ->table('trips')
        ->where(['return_station_id'=>$id])
        ->group('departure_station_id')
        ->order('total_trips DESC')
        ->limit([0,5])
        ->result();

        if(is_array($station)){
            $content['success'] = 1;

            $station['average_starting'] = $this->convertToKm($station['average_starting']);
            $station['average_ending'] = $this->convertToKm($station['average_ending']);
            $station['top_5_departure_stations'] = $top_5_departure_stations;
            $station['top_5_return_stations'] = $top_5_return_stations;
            $content['data'] = $station;
            echo $this->jason($content);
        }
        else{
            echo $this->jason(['error'=>1, 'message'=>'No records found for the selected station ID.']);
        }
	}
}
