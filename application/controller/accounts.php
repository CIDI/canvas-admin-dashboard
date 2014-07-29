<?php

class Accounts extends Controller
{
  public function index() {
		// getaccount
		$data = array(
			'primary_account'=>$this->institution['primary_canvas_account_id']
		);
		print_r($data);exit;
		$this->render('accounts/index', $data);
  }
	
	public function state($state) {
		$data = array(
			'include'=>($state === 'include' ? 1 : 0)
		);
		
		if(!isset($_POST['id'])) {
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Account id is required', true, 500);
			
			return;
		}
		
		$filter = array(
			'parent_id'=>$_POST['id']
		);
		
		$model = $this->loadModel('AccountModel');
		$model->update($data, $filter);
		
		$filter = array(
			'id'=>$_POST['id']
		);
		
		$model->update($data, $filter);
	}
	
	public function subaccounts($id, $import='') {
		
		if($import === 'import') {
			// run the import process
			$this->import($id);
		}
		
		// get all of the accounts that are in the database now
		$accountModel = $this->loadModel('AccountModel');
	  	$data = array(
			'accounts'=>$accountModel->findAll(
				array('depth'=>2),
				'accounts.*, parent_account.name AS parent_name, parent_account.include AS parent_include',
				'RIGHT JOIN accounts AS parent_account ON (accounts.parent_id = parent_account.id)',
				'parent_name, name'
			)
		);
		
		// render the accounts view
		$this->render('accounts/subaccounts', $data);
	}
	
	/*
	 * $this->import()
	 *
	 * Recursive function for importing courses from Canvas into a database.
	 *
	 * Data is paged from Canvas, max of 50 results per page. This method will get all
	 * accounts from Canvas under the specified parent account up to a depth specified
	 *
	 * Parameters: 
	 *
	 * $id is the account to import children for
	 * $page represents which page this pass will start at
	 * $per_page controls how many results are retrieved for each pass
	 * $depth is how deep we are (tracked in db to represent institution (depth=0), college (depth=1), department (depth=2)
	 * $max_depth determines how far the recursive function should search in the accounts list (default is 2)
	 *
	 * Usage:
	 *
	 * Assumes existance within panique/php-mvc-advanced with cidi/canvas/canvas-api and popeit/hotwired-php-models insjected in
	 *
	 * $this->import($parent_account_id);
	 *
	 * Stores results in table `accounts` with the following structure:
	 *
	 * _________________________________
	 * | id | name | parent_id | depth |
	 * ---------------------------------
	 *
	 */
	private function import($id, $page=1, $per_page=50, $depth=1, $max_depth=2) {
		// model for storing account records to the database
		$accountModel = $this->loadModel('AccountModel');
		
		// get the account list from canvas
		$accountList = $this->canvasApi->listSubAccounts($id, $page, $per_page);
		$accounts = json_decode($accountList, true);
		
		// keep track of how many results need to be processed in this batch
		$resultCount = count($accounts);
	
		// only try processing if there are results to be processed
		if($resultCount > 0) {
			// loop through colleges
			foreach ($accounts as $account) {
				// collect all properties that would need to be inserted into the database
				$properties = array(
					'name'=>$account['name'],
					'id'=>$account['id'],
					'parent_id'=>$account['parent_account_id'],
					'depth'=>$depth
				);
				
				// checking like this was pretty slow... so we just skip the check and wrap the insert with a try catch
				// assumes there is a primary key constraint on the id column that will generate an error if a duplicate
				// record is inserted
				//$result = $accountModel->findByKey($properties['id']);
				
				//if (!$result) {
				try{
					$accountModel->insert($properties);
				} catch(Exception $e) {
					//pass
				}
				//}
				
				// only go deeper if we are on page the first page of an account
				if($page == 1 && $depth < $max_depth) {
					// move on to the next account
					$this->import($properties['id'], 1, $per_page, $depth+1, $max_depth);
				}
			}
		}
		
		// more results at this layer
		if ($resultCount === $per_page){
			// go get 'em
			$this->import($id, $page+1, $per_page, $depth, $max_depth);
		}
	}
}
