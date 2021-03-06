<?php

class BaseReport {
	function __construct($controller) {
		$this->controller = $controller;
		$this->reports = array();

		$this->register();
		$this->interesting_data = array();

		$this->course_meta_model = $this->controller->loadModel('Course_metaModel');

		$this->config();

		return $this;
	}

	public function config() {

	}

	public function register($method_name=''){
		if($method_name == 'all') {
			throw new Exception("&ldquo;all&rdquo; is a reserved name. You cannot register this report.");
		} 
		if($method_name != '') {
			$this->reports[] = $method_name;
		}

		return $this->reports;
	}

	public function track($key=''){
		if($key != '') {
			$this->interesting_data[] = $key;
		}

		return $this->interesting_data;
	}

	public function process($institution, $term, $report, $data, $course_id, $meta_category_id, $save=true) {
		// if there is no data to process, we are done here
		if(!$data || !count($data)) {
			return;
		}
		
		if ($report == 'all' || array_search($report, $this->reports)) {
			
			if($save) {
				$this->course_meta_model->delete(array(
		            'course_id'=> $course_id,
		            'meta_category_id'=> $meta_category_id,
		            'institution_id'=> $institution,
		            'canvas_term_id'=> $term
		        ));
		    $dataType = gettype($data);
		    if ($dataType == 'object') {
		    	$dataArray = array($data);
		    } else {
		    	$dataArray = $data;
		    }
				$fields = $this->track();
				if(!count($fields)) {
					$fields = array_keys(get_object_vars($dataArray[0]));
				}

		    foreach ($dataArray as $sort => $item) {
					foreach($fields as $field) {
						$meta_value = '';

						if(isset($item->$field)) {
							$meta_value = $item->$field;
						}
						$metaType = gettype($meta_value);

						if($metaType == 'object') {
							$meta_array = get_object_vars($meta_value);
						} else {
							$meta_array = array($meta_value);
						}

						foreach ($meta_array as $key => $value) {
							$meta_name = $field;

							if($metaType == 'object') {
								$meta_name .= '__' . $key;
							}
							$value_meta_type = gettype($value);
							if($value_meta_type == 'object') {
								$value = json_encode($value);
							}
					        $this->course_meta_model->insert(array(
					            'course_id'=> $course_id,
					            'meta_category_id'=> $meta_category_id,
					            'meta_name'=> $meta_name,
					            'meta_value'=> $value,
					            'synced_at'=> NOW,
					            'sort'=> $sort,
					            'institution_id'=> $institution,
					            'canvas_term_id'=> $term
					        ));
						}
					}
		    }
			}
			
			if($report == 'all') {
				$this->all($institution, $term, $data, $course_id, $meta_category_id);
			} else {
				$this->$report($data);
			}
		}
	}

	public function all($institution, $term, $data, $course_id, $meta_category_id){
		

		foreach($this->reports as $report_name) {
			$this->process($institution, $term, $report_name, $data, $course_id, $meta_category_id, false);
		}
	}
}