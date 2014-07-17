<?php

class AccountModel extends BaseModel {
	static $ORDER = "name, canvas_account_id";
	
	public function stats($filter=array()) {

		// in your controller, call like this
		////
		// $filter = array('account'=>15);
		// $acconut_stats = $account_model->stats($filter);


		$query_string = "SELECT count(*) FROM accounts WHERE canvas_parent_id = :account";

		//echo '<pre>';echo $query_string;echo "\n\n";print_r($filter);exit;

		//$query = $this->db->query($query_string);
		// $result = $query->fetchAll();
		//print_r($query->fetchAll());exit;

		$query = $this->db->prepare($query_string);
		$query->execute($filter);

		$result = $query->fetchAll();

		return $result;
	}
}