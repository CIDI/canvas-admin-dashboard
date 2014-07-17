<?php

class ReportModel extends BaseModel {
	public function query($report_code, $term_id) {
		$properties = array(
			'code'=>$report_code
		);

		$report = $this->findOne($properties);

		// check if query is executable
		if($report['executable']) {
			$filters = array(
				'canvas_term_id'=>$term_id
			);

			// execute query
			$query = $this->connection->prepare($report['sql_query']);
			$query->execute($filters);

			$result = $query->fetchAll();
		} else {
			throw new Exception("Attempted to run query that is not executable");
		}

		return $result;
	}
}