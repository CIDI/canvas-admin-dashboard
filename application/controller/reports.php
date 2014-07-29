<?php

class Reports extends Controller {

  public function index() {
 	$parts = explode('/', $_GET['url']);

  	if(count($parts) < 3) {
  		//some error...
  		throw new Exception("Cannot find the report you are looking for. Not enough parameters.");
  		
  		exit;
  	}

  	$institution_code = $parts[1];
  	$report_code = $parts[2];

  	if(count($parts) > 3) {
  		$view = $parts[3];
  	} else {
  		$view = 'index';
  	}

	$data = array('messages'=>array());
	$filters = array();
	$filters['term'] = $this->getDefaultTerm();
	
	$this->initializeCanvasApi($institution_code);

	if (!$this->institution) {
		//errror
		throw new Exception("No institution found");
		
		exit;
	}
	$data['institution'] = $this->institution;

	$filters['institution'] = $this->institution['id'];

  	$report_model = $this->loadModel('ReportModel');
  	$term_model = $this->loadModel('TermModel');

  	$terms = $term_model->findAll(array(
  		'institution_id' => $this->institution['id']
  	));

  	$data['terms'] = $terms;

  	if(isset($_GET['filters']['course'])) {
  		$filters['course'] = $_GET['filters']['course'];
  		$course_model = $this->loadModel('CourseModel');

  		$data['course'] = $course_model->findOne(array('canvas_course_id'=>$filters['course']));
  	}
  	
	try {
		$report_data = $report_model->query($report_code, $filters);

		$data['report_data'] = $report_data;
	} catch(Exception $error) {
		$data['messages']['error'] = $error->getMessage();
	}

	$data['filters'] = $filters;

	$view_path = 'reports/' . $report_code . '/' . $view;
	
	if(!realpath(dirname(__DIR__) . '/views/' . $view_path . '.twig')) {
		$view_path = 'reports/view';
	}

	try {

		$this->render($view_path, $data);

	} catch(Exception $error) {
		print_r($error);exit;
	}
  }
  protected function getDefaultTerm() {
  	if(isset($_GET['filters']['term'])) {
  		$term = $_GET['filters']['term'];
  	} else {
  		$query = $this->db->prepare('
  			SELECT * FROM terms 
  			WHERE institution_id= :institution
  			AND start_date < :date
  			OR start_date IS NULL
  			ORDER BY start_date DESC
  		');
  		$futureDate = new DateTime();
  		$futureDate->add(new DateInterval('P30D'));
  		$query->execute(array(
  			'institution'=>$this->institution['id'],
  			'date'=>$futureDate->format('Y-m-d')
  		));
  		$result = $query->fetch();

  		$term = $result['canvas_term_id'];
  	}
  	return $term;
  }
}
