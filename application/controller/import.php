<?php

class Import extends Controller
{
	public function account($canvas_term_id) {
		$account_meta_model = $this->loadModel('Account_metaModel');
		$account_meta_model->delete(array('canvas_term_id'=>$canvas_term_id));
		$this->account_meta($canvas_term_id);
	}
	private function account_meta($term_id, $parent_id=PRIMARY_CANVAS_ACCOUNT_ID, $depth=1, $index=1, $max_depth=20) {
		if($depth > $max_depth) {
			throw new Exception("Maximum account meta depth exceeded, depth=".$depth);
		}
		// model for storing account records to the database
		$account_model = $this->loadModel('AccountModel');
		$account_meta_model = $this->loadModel('Account_metaModel');
		
		// get the account list from canvas
		$account_filter = array(
			'canvas_parent_id'=>$parent_id
		);
		$accounts = $account_model->findAll($account_filter);
		
		// keep track of how many results need to be processed in this batch
		$resultCount = count($accounts);
	
		// only try processing if there are results to be processed
		if($resultCount > 0) {
			// loop through colleges
			foreach ($accounts as $account) {
				$left_index = $index;
				
				// move on to find children accounts
				$right_index = $this->account_meta(
					$term_id,
					$account['canvas_account_id'],
					$depth+1,
					$left_index+1,
					$max_depth
				);
				$index=$right_index;
				// collect all properties that would need to be inserted into the database
				$properties = array(
					'canvas_account_id'=>$account['canvas_account_id'],
					'canvas_term_id'=>$term_id,
					'depth'=>$depth,
					'lft'=>$left_index,
					'rght'=>$right_index
				);

				$account_meta_model->insert($properties);
				$index++;
			}

		}
		return $index;
	}
}