<?php

class CourseModel extends BaseModel {
	public function find_active($canvas_term_id, $institution_id) {
		$query_string = "SELECT c.*, ec.enrollments FROM (
				SELECT * FROM courses AS t
				WHERE t.status = 'active'
				AND t.canvas_term_id = :canvas_term_id
				AND t.institution_id = :institution_id
			) AS c
			JOIN (
				SELECT * FROM enrollment_counts AS t
				WHERE t.role = 'student'
				AND t.enrollments > 0
				AND t.canvas_term_id = :canvas_term_id
				AND t.institution_id = :institution_id
			) AS ec 
			ON (c.canvas_course_id = ec.canvas_course_id)
		";
		$query = $this->db->prepare($query_string);
		$query->execute(array('canvas_term_id' => $canvas_term_id, 'institution_id' => $institution_id));
		$result = $query->fetchAll();
		return $result;
	}
}