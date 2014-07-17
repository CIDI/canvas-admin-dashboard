<?php

class Reports extends Controller {

  public function view($report_code, $view='index') {
	$data = array('messages'=>array());
	$filters = $_GET['filters'];

  	$report_model = $this->loadModel('ReportModel');
  	$term_model = $this->loadModel('TermModel');

  	$terms = $term_model->findAll();

  	$data['terms'] = $terms;

	try {
		$report_data = $report_model->query($report_code, $filters['term']);

		$data['report_data'] = $report_data;
	} catch(Exception $error) {
		$data['messages']['error'] = $error->getMessage();
	}

	$view_path = 'reports/' . $report_code . '/' . $view;
	
	if(!realpath(dirname(__DIR__) . '/views/' . $view_path . '.twig')) {
		$view_path = 'reports/view';
	}

	$this->render($view_path, $data);
  }
}
