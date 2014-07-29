<?php

class Syllabus extends Controller
{
  public function index() {

  	$parts = explode('/', $_GET['url']);

  	if(count($parts) < 3) {
  		//some error...
  		exit;
  	}

  	$institution_code = $parts[1];
  	$course_id = $parts[2];

  	if(!is_numeric($course_id)) {
  		// institutional based filters to find a course
  		// needs to dynamically find the course ID based off of $parts
  	}

  	if(is_numeric($course_id)) {
	  	$this->initializeCanvasApi($institution_code);

		$data = array(
			'syllabus'=>$this->canvasApi->getCourseSyllabus($course_id)
		);
	} else {
		$data = array(
			'message'=>'Course not specified.'
		);
	}

  $data['institution'] = $this->institution;
	
	$this->render('syllabus/index', $data);
  }
}
