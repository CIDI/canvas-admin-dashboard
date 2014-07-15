<?php

class BaseModel {
	static  $SELECT = ""; 
	static $JOIN = ""; 
	static $ORDER ="";
	static $GROUP = "";
	
  public $id;
	
	function __construct($db, $name='BaseModel') {
		try {
        $this->db = $db;
    } catch (PDOException $e) {
        exit('Database connection could not be established.');
    }
		
		$this->connection = $db;
		
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
    $this->name = $name == 'BaseModel' ? strtolower(get_class($this)) : $name;
    $this->table = strtolower($this->pluralize(preg_replace('/Model$/', '', $this->name)));

		return $this;
	} 
    
  public function find($properties=array(), $selectArg="", $joinArg="", $orderArg="", $groupArg="") {
	
    if ($selectArg == ""){
    	$select=static::$SELECT;	
    } else {
    	$select = $selectArg;
    }
    	
    if ($joinArg == ""){
    	$join=static::$JOIN;
    } else {
    	$join = $joinArg;
    }
    
    if ($groupArg == ""){
    	$group=static::$GROUP;
    } else {
    	$group = $groupArg;
    }
    
    if ($orderArg == ""){
    	$order=static::$ORDER;
    } else {
    	$order = $orderArg;
    }
    
    $selectStatement = "*";
    $joinStatement = "";
    $whereFilter = '';
    $groupStatement = '';
    $orderStatement = "";
    
    if ($select != "") {
    	$selectStatement = $select; 
    }
     
    if ($join != "") {
    	$joinStatement = $join; 
    }
    
    if ($group != "") {
    	$groupStatement = 'GROUP BY ' . $group; 
    }
    
    if ($order != "") {
    	$orderStatement = "ORDER BY $order"; 
    }

    foreach($properties AS $field=>$value) {
      if($field) {
        if($value == 'NULL') {
          $filterOperator = 'IS';
          $filterValue = 'NULL';
        } else {
          $filterOperator = '=';
          $filterValue = ":$field";
        }
        
        if(!strstr($field, '__')) {
            $whereFilter .= "$this->table.$field $filterOperator $filterValue AND ";
        } else {
            $column = str_replace('__', '.', $field);
            $whereFilter .= "$column $filterOperator $filterValue AND ";
        }
      } elseif (!$value) {
        unset($properties[$field]);
      }
    }
		
		$whereFilter = preg_replace('/AND $/', '', $whereFilter);
		
		if($whereFilter != '') {
			$whereFilter = 'WHERE ' . $whereFilter;
		}
		
    $querystring = "SELECT $selectStatement
                    FROM $this->table
                    $joinStatement
                    $whereFilter
                    $groupStatement
                    $orderStatement";
		
    $query = $this->connection->prepare($querystring);
    $query->execute($properties);

    return $query;
  }
  
  public function insert($properties=array()) {
      $columns = '';
      $values = '';
      $data = array();
      
      foreach($properties AS $field=>$value) {
          if($value != '') {
              $columns .= "$field, ";
              $values .= ":$field, ";
              $data[$field] = $value;
          }
      }
			
			$columns = preg_replace('/, $/', '', $columns);
			$values = preg_replace('/, $/', '', $values);
      
      $querystring = "INSERT INTO $this->table ($columns)
                      VALUES ($values)";

      $query = $this->connection->prepare($querystring);
      $result = $query->execute($data);

      return $query;
  }
	
	public function update($properties=array(), $filter=array()) {
		$setStatement = '';
		$data = array();
		
		if (!is_array($properties) || count($properties) == 0) {
			return false;
		}
		
    foreach($properties AS $field=>$value) {
      if ($field !== 'id') {
        $setStatement .= "$field = ";
        if($value !== '') {
          $setStatement .= ":$field, ";
        } else {
          $setStatement .= "NULL, ";
        }
      }
      
      if($value !== '') {
        $data[$field] = $value;
      }
    }

    $setStatement = preg_replace('/, $/', '', $setStatement);
    
		$whereFilter = "";
  		
		if (!is_array($filter) || count($filter) === 0) {
			$whereFilter = "$this->table.id = :id";
		} else {
		
			foreach($filter AS $field=>$value) {
				if(!strstr($field, '__')) {
						$whereFilter .= "$this->table.$field = :$field AND";
				} else {
						$column = str_replace('__', '.', $field);
						$whereFilter .= "$column = :$field AND";
				}
				
				if($value !== '') {
					$data[$field] = $value;
				}
			}
	
			$whereFilter = preg_replace('/ AND$/', '', $whereFilter);
		}
		
		$querystring = "UPDATE $this->table
				SET
				$setStatement
				WHERE $whereFilter";

		$query = $this->connection->prepare($querystring);
		$result = $query->execute($data);
		
		return $query;
  }
    
  public function delete($properties=array(), $softDelete=false) {
  		$whereFilter = "";
  		
  		if (!is_array($properties) || count($properties) == 0) {
  			return false;
  		}
  		
  		foreach($properties AS $field=>$value) {
            if(!strstr($field, '__')) {
                $whereFilter .= "$this->table.$field = :$field AND ";
            } else {
                $column = str_replace('__', '.', $field);
                $whereFilter .= "$column = :$field AND ";
            }
        }

		$whereFilter = preg_replace('/ AND $/', '', $whereFilter);
		
		if ($softDelete == true) {
			$querystring = "UPDATE $this->table
							SET deleted_at = '" . date('Y-m-d H:i:s') . "'
							WHERE $whereFilter";
		} else {
			$querystring = "DELETE FROM $this->table
							WHERE $whereFilter";
		}
		
		$query = $this->connection->prepare($querystring);
        $result = $query->execute($properties);
		
		return $query;                            
  }
	
	public function deleteByKey($id, $softDelete=true) {
		return $this->delete(array('id'=>$id), $softDelete);
	}
    
    public function findAll($properties=array(), $select="", $join="", $order="", $group="") {
        return $this->find($properties, $select, $join, $order, $group)->fetchAll();
    }
    
    public function findByKey($id, $select="", $join="", $order="", $group="") {         
        return $this->findOne(array('id'=>$id), $select, $join, $order, $group);
    }
    
    public function findOne($properties=array(), $select="", $join="", $order="", $group="") {
        return $this->find($properties, $select, $join, $order, $group)->fetch();
    }
    
    public function pluralize($string) {
      if(in_array(strtolower($string), array('activity', 'meta_category'))) {
        $result = preg_replace('/y$/', 'ies', $string);
      } elseif(in_array(strtolower($string), array('progress', 'course_meta'))) {
        $result = $string;
      } else {
        $result = $string . 's';
      }
      
      return $result;
    }
}