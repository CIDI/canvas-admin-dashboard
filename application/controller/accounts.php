<?php

require_once 'canvas.php';

class Accounts extends Canvas
{
  public function index() {
		// getaccount
		$data = array(
			'primary_account'=>$this->institution['primary_canvas_account_id']
		);

		$query = $this->db->prepare('
			SELECT * FROM (
				SELECT * FROM accounts
				WHERE institution_id = :institution
				AND canvas_term_id = :term
			) AS a
			JOIN (
				SELECT * FROM account_meta
				WHERE institution_id = :institution
				AND canvas_term_id = :term
			) AS am ON (am.canvas_account_id = a.canvas_account_id)
			ORDER BY lft ASC, rght ASC
		');

		$query->execute(array(
			'institution'=>$this->institution['id'],
			'term'=>$_GET['filters']['term']
		));

		$data['accounts'] = $query->fetchAll();
		$data['filters'] = $_GET['filters'];
		
		$this->render('accounts/subaccounts', $data);
  }
	
	public function toggle() {
		if(isset($_POST['include'])) {
			$state = 1;
			$account = $_POST['include'];
		} else if(isset($_POST['exclude'])) {
			$state = 0;
			$account = $_POST['exclude'];
		}

		$account_model = $this->loadModel('AccountModel');
		$account_model->toggle(array(
			'canvas_account_id'=>$account,
			'canvas_term_id'=>$_POST['term'],
			'institution_id'=>$this->institution['id'],
		), $state);

		$states = array('exclude', 'include');

		echo '{"state":"' . $states[$state] . '"}';
	}
}
