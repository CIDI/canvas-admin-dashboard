<?php

require_once 'canvas.php';
class Admin extends Canvas
{
  public function index() {
    $_SESSION['canvas-admin-dashboard']['institution_id'] = 1;

    $report_match = array('accounts'=>false, 'courses'=>false, 'enrollments'=>false, 'users'=>false);
    $files = scandir(PATH_UPLOADS);

    foreach($files as $file_name) {
      $report = preg_replace('/\.csv$/', '', $file_name);

      if(isset($report_match[$report])) {
        $report_match[$report] = true;
      }
    }

    $data = array('reports'=>$report_match);

    $term_model = $this->loadModel('TermModel');
    $data['terms'] = $term_model->findAll(array('institution_id'=>$_SESSION['canvas-admin-dashboard']['institution_id']));

    if(!count($data['terms'])) {
      header('Location: ' . URL . 'admin/terms');
    } else {
      $this->render('admin/index', $data);
    }
  }
  
  public function terms() {
    $report_match = array('terms'=>false);
    $files = scandir(PATH_UPLOADS);

    foreach($files as $file_name) {
      $report = preg_replace('/\.csv$/', '', $file_name);

      if(isset($report_match[$report])) {
        $report_match[$report] = true;
      }
    }

    $data = array('reports'=>$report_match);

    $this->render('admin/index', $data);

  }

  public function upload($canvas_term_id='') {
    if(isset($_POST['filters']['term'])) {
      $canvas_term_id=$_POST['filters']['term'];
    }

    $uploaddir = PATH_UPLOADS;
    echo '<pre>';

    foreach ($_FILES as $filename => $file_data) {
      # code...
      $uploadfile = $uploaddir . '/' . $filename . '.csv';

      if (move_uploaded_file($file_data['tmp_name'], $uploadfile)) {
        echo "File `$filename.csv` is valid, and was successfully uploaded.\n";
      } else {
        echo "Problem uploading  `$filename.csv`\n";exit;
      }

      $import_method = "process_$filename";
      $this->$import_method($canvas_term_id);
    }

    header("Location: " . URL . "admin/index");
  }

}
