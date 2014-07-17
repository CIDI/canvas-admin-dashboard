<?php

class Home extends Controller
{
  public function index()
  {
  	$model = $this->loadModel('TermModel');
		
		$data = array(
			'term'=>$model->findAll(array(
				'term_id'=>'201420'
			)),
			'syllabus'=>$this->canvasApi->getCourseSyllabus(258347),
			'model'=>$model
		);
		
		$this->render('home/index', $data);
  }
}
