<?php

class AccountModel extends BaseModel {
	static $ORDER = "name, accounts.canvas_account_id";

	public function toggle($filter, $state='') {
		// find the account being toggled
		$account = $this->findOne($filter, '', 'JOIN account_meta ON (account_meta.canvas_account_id = accounts.canvas_account_id)');

		if(!$account) {
			var_dump($_POST);
			throw new Exception("Account not found (".$filter['canvas_account_id'].")", 1);
			
		}

		if($state === '') {
			$state = $account['whitelist'] ? 0 : 1;
		}

		$query = $this->db->prepare('
			UPDATE account_meta
			SET
				whitelist = :state
			WHERE
				institution_id = :institution_id
				AND canvas_term_id = :canvas_term_id
				AND lft >= :lft
				AND rght <= :rght 
		');

		$properties = $filter;
		unset($properties['canvas_account_id']);
		$properties['lft'] = (int)$account['lft'];
		$properties['rght'] = (int)$account['rght'];
		$properties['state'] = $state;

		$query->execute($properties);

		return $state;
	}
}