<?php
class Model {
	public $config = null;
	public $datasourcePDO = null;
	private $select = '';
	private $table = '';
	private $where = '';
	private $exists = '';
	private $in = '';
	private $leftJoin = '';
	private $group = '';
	private $order = '';
	private $limit = '';

	public function __construct(){
   		global $config;
		if($this->config == null){
			$this->config = $config;
			if(is_array($config['database'])){
				try {
					$this->datasourcePDO = new PDO(
					"mysql:host=localhost;dbname={$this->config['database']['database_name']}",
					$this->config['database']['username'],
					$this->config['database']['password'],
					array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
				} catch (PDOException $ex) {
					echo($ex->getMessage());
					exit;
				}
				
			}
		}
   	}	

	public function fetchData($table, $condition = []){
		try {
			$table = $this->config['database']['pre'].$table;
			/* 
			$condition['columns'] = columns to select
			$condition['params'] = where
			$condition['others'] = other [group, order and/or limit]
			*/

			if(!empty($condition['columns'])){
				$columns = implode(', ', $condition['columns']);
			}
			else{
				$columns = '*';
			}
			
			$where = '';
			$values = [];
			if(!empty($condition['params'])){
				foreach($condition['params'] as $index => $val){
					if(str_contains($index, '>') || str_contains($index, '<')){
						$params[] = "$index :".$this->clean($index);
					}
					else{
						$params[] = "$index= :".$this->clean($index);
					}
					$values[":".$this->clean($index)] = $val;
				}

				$where = 'WHERE '.implode(' AND ', $params);
			}

			$query = "SELECT $columns FROM $table {$condition['join']} $where {$condition['exists']} {$condition['in']} {$condition['others']}";
			//return $query;
			$stmt = $this->datasourcePDO->prepare($query);

			/* if(!empty($condition['params'])){
				foreach($condition['params'] as $index => $val){
					$stmt->bindParam(":$index", $this->input($val));
				}
			} */

			if($stmt->execute($values)) {
				while($row = $stmt->fetch(PDO::FETCH_ASSOC))
					$return_result[] = $row;
				return $return_result;
			} else {
				return false;
			}
		} catch (PDOException $e) {
			//throw $th;
		}
	}

	public function countRecord($table, $condition = []):int{
		try {
			$table = $this->config['database']['pre'].$table;
			/* 
			$condition['params'] = where
			*/
	
			$where = '';
			$values = [];
			if(!empty($condition['params'])){
				foreach($condition['params'] as $index => $val){
					if(str_contains($index, '>') || str_contains($index, '<')){
						$params[] = "$index :".$this->clean($index);
					}
					else{
						$params[] = "$index= :$index";
					}
					$values[":".$this->clean($index)] = $val;
				}

				$where = 'WHERE '.implode(' AND ', $params);
			}

			$query = "SELECT COUNT(*) total FROM $table $where";
			//return $query;
			$stmt = $this->datasourcePDO->prepare($query);

			if($stmt->execute($values)) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				return $row['total'];
			} else {
				return false;
			}
		} catch (PDOException $e) {
			//throw $th;
		}
	}

	public function select($str = []){
		$this->select = $str;
		return $this;
	}

	public function table($str = null){
		$this->table = $str; //$this->config['database']['pre'].$str;
		return $this;
	}

	public function where($str){
		$this->where = $str;
		return $this;
	}

	public function exists($str = []){
		$this->exists = null;
		if(is_array($str) and sizeof($str) > 0){
			$qr = $this->where ? ' AND ' : ' WHERE ';

			foreach($str as $st){
				$query[] = "EXISTS(SELECT $st[0] FROM ".$this->config['database']['pre']."$st[1] WHERE $st[2])"; 
			}

			$this->exists = $qr.implode(' AND ', $query);
			return $this;
		}
		return $this;
	}

	public function in($str = []){
		$this->exists = null;
		if(is_array($str) and sizeof($str) > 0){
			$qr = $this->where ? ' AND ' : ' WHERE ';

			foreach($str as $st){
				$query[] = "$st[0] IN(SELECT $st[0] FROM ".$this->config['database']['pre']."$st[1] WHERE $st[2])"; 
			}

			$this->in = $qr.implode(' AND ', $query);
			return $this;
		}
		return $this;
	}

	public function leftJoin($str){
		if(is_array($str) and sizeof($str) > 0){
			foreach($str as $st){
				$query[] = ' LEFT JOIN '.$this->config['database']['pre'].$st[0]." ON $st[1]";
			}

			$this->leftJoin = implode(' ', $query);
			return $this;
		}
		return $this;
	}

	public function group($str){
		$this->group = $str ? ' GROUP BY '.$str : '';
		return $this;
	}

	public function order($str){
		$this->order = $str ? ' ORDER BY '.$str : '';
		return $this;
	}

	public function limit($str = []){
		if(!empty($str) and is_array($str)){
			$this->limit = " LIMIT ".implode(',', $str);
		}
		return $this;
	}

	public function result(){
		if($this->table == '')
			return('Please, select a database table.');

		$condition['columns'] = $this->select;
		$condition['join'] = $this->leftJoin;
		$condition['params'] = $this->where;
		$condition['exists'] = $this->exists;
		$condition['in'] = $this->in;
		$condition['others'] = "{$this->group} {$this->order} {$this->limit}";
		return(
			$this->fetchData($this->table, $condition)
		);
	}

	public static function clean($string){
		return(preg_replace('/[^a-zA-Z]/i','',$string));
	}
}