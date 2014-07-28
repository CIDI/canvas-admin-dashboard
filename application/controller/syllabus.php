<?php

class Syllabus extends Controller
{
  public function index($course_id) {
  	// $model = $this->loadModel('Course_metaModel');
		
	$data = array(
		// 'syllabus'=>$model->findOne(array(
		// 	'institution_id'=>$this->institution['id'],
		// 	'meta_category_id'=>1,
		// 	'meta_name'=>'syllabus_body',
		// 	'course_id'=>$course_id
		// )),
		'syllabus'=>$this->canvasApi->getCourseSyllabus($course_id)
	);
	
	$this->render('syllabus/index', $data);
  }
}
