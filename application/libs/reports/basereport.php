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

	public function process($report, $data, $course_id, $meta_category_id, $save=true) {
		if ($report == 'all' || array_search($report, $this->reports)) {
			
			if($save) {
				$this->course_meta_model->delete(array(
		            'course_id'=> $course_id,
		            'meta_category_id'=> $meta_category_id
		        ));
		        
				$fields = $this->track();

				if(!count($fields)) {
					$fields = get_object_vars($data);
				}

				foreach($fields as $field) {
					$meta_value = '';

					if(isset($data->$field)) {
						$meta_value = $data->$field;
					}

			        $this->course_meta_model->insert(array(
			            'course_id'=> $course_id,
			            'meta_category_id'=> $meta_category_id,
			            'meta_name'=> $field,
			            'meta_value'=> $meta_value,
			            'synced_at'=> NOW,
			            'sort'=> 1
			        ));
				}
			}
			
			if($report == 'all') {
				$this->all($data, $course_id, $meta_category_id);
			} else {
				$this->$report($data);
			}
		}
	}

	public function all($data, $course_id, $meta_category_id){
		

		foreach($this->reports as $report_name) {
			$this->process($report_name, $data, $course_id, $meta_category_id, false);
		}
	}
}