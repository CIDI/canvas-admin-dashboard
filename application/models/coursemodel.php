<?php

class CourseModel extends BaseModel {
	public function find_active($canvas_term_id) {
		$query_string = "SELECT c.*, ec.enrollments FROM courses AS c
						 JOIN enrollment_counts AS ec ON c.canvas_course_id = ec.canvas_course_id
						 WHERE c.status = 'active'
						 AND c.canvas_term_id = :canvas_term_id
						 AND ec.role = 'student'
						 AND ec.enrollments > 0";
		$query = $this->db->prepare($query_string);
		$query->execute(array('canvas_term_id' => $canvas_term_id));
		$result = $query->fetchAll();
		return $result;
	}
}