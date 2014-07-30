<?php

require_once 'canvas.php';
class Admin extends Canvas
{
  public function index() {
    $data = array();

    $term_model = $this->loadModel('TermModel');
    $data['terms'] = $term_model->findAll(array('institution_id'=>$this->institution['id']));
    $data['institution'] = $this->institution;

    if(!count($data['terms'])) {
      header('Location: ' . URL . 'admin/terms');
    } else {
      $this->render('admin/index', $data);
    }
  }

  public function import($canvas_term_id='') {
    $filters = array('term'=>$canvas_term_id);

    if(isset($_POST['filters']['term'])) {
      $filters = $_POST['filters'];
      $canvas_term_id = $_POST['filters']['term'];
    }

    if($canvas_term_id == '') {
      header('Location: ' . URL . 'admin/index');
      return;
    }

    $term_model = $this->loadModel('TermModel');
    $term = $term_model->findOne(array(
      'institution_id'=>$this->institution['id'],
      'canvas_term_id'=>$canvas_term_id
    ));

    if(!$term) {
      header('Location: ' . URL . 'admin/index');
      return;
    }

    $report_match = array('accounts'=>false, 'courses'=>false, 'xlists'=>false, 'enrollments'=>false);
    $files = scandir(PATH_UPLOADS);

    foreach($files as $file_name) {
      $report = preg_replace('/' . $this->institution['id'] . '_' . $canvas_term_id . '_([^\.]+)\.csv$/', '$1', $file_name);

      if(isset($report_match[$report])) {
        $report_match[$report] = true;
      }
    }

    $data = array('reports'=>$report_match, 'filters'=>$filters);
    $data['terms'] = $term_model->findAll(array('institution_id'=>$this->institution['id']));
    $data['institution'] = $this->institution;

    if($canvas_term_id) {
      $stats_filter = array(
        'institution'=>$this->institution['id'],
        'term'=>$canvas_term_id
      );

      foreach($report_match as $report=>$value) {
        $query = $this->db->prepare(
          'SELECT count(*) as `count`, min(synced_at) as earliest_sync, max(synced_at) as latest_sync
          FROM ' . $report . ' WHERE institution_id = :institution AND canvas_term_id = :term'
        );

        $query->execute($stats_filter);

        $data['counts'][$report] = $query->fetch();
      }
    }

    $this->render('admin/import', $data);
  }
  
  public function institution() {
    $data = array('institution'=>$this->institution);
    $this->render('admin/institution', $data);
  }
  public function institution_save() {
    $institution_model = $this->loadModel('InstitutionModel');
    $properties = array(
      'id'=>$this->institution['id'],
      'name'=>$_POST['institution']['name'],
      'slug'=>$_POST['institution']['slug'],
      'logo'=>$_POST['institution']['logo'],
      'primary_canvas_account_id'=>$_POST['institution']['primary_canvas_account_id']
    );

    if($_POST['institution']['oauth_token']) {
      require_once './application/libs/cryptastic/cryptastic.php';
      $properties['oauth_token'] = $_POST['institution']['oauth_token'];
      //encrypt token
      $cryptastic = new cryptastic;
      $key = $cryptastic->pbkdf2(ENCRYPTION_KEY, ENCRYPTION_SALT, 1000, 32);
      $properties['oauth_token'] = $cryptastic->encrypt($properties['oauth_token'], $key);
    }

    $institution_model->update($properties);
    
    header('Location: ' . URL .'admin/index');
  }

  public function terms() {
    $report_match = array('terms'=>false);
    $files = scandir(PATH_UPLOADS);

    foreach($files as $file_name) {
      $report = preg_replace('/' . $this->institution['id'] . '_(terms)\.csv$/', '$1', $file_name);

      if(isset($report_match[$report])) {
        $report_match[$report] = true;
      }
    }

    $data = array('reports'=>$report_match);
    $data['institution'] = $this->institution;

    $this->render('admin/import', $data);

  }

  public function users() {
    $report_match = array('users'=>false);
    $files = scandir(PATH_UPLOADS);

    foreach($files as $file_name) {
      $report = preg_replace('/' . $this->institution['id'] . '_(users)\.csv$/', '$1', $file_name);

      if(isset($report_match[$report])) {
        $report_match[$report] = true;
      }
    }

    $data = array('reports'=>$report_match);
    $data['institution'] = $this->institution;
    
    $this->render('admin/import', $data);

  }

  public function upload($canvas_term_id='') {
    if(isset($_POST['filters']['term'])) {
      $canvas_term_id=$_POST['filters']['term'];
    }

    $file_name_prefix = $this->report_prefix($canvas_term_id);

    $uploaddir = PATH_UPLOADS;
    foreach ($_FILES as $report_name => $file_data) {
      if ($file_data['tmp_name'] !== '') {
        $file_name = $file_name_prefix . $report_name . '.csv';
        # code...
        $uploadfile = $uploaddir . '/' . $file_name;

        if (move_uploaded_file($file_data['tmp_name'], $uploadfile)) {
          echo "File `$file_name` is valid, and was successfully uploaded.\n";
        } else {
          echo "Problem uploading  `".$file_data['tmp_name']."` ($report_name)\n";exit;
        }

        $import_method = "process_$report_name";
        $this->$import_method($canvas_term_id);
      }
    }

    header("Location: " . URL . "admin/index");
  }

}
