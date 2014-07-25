<?php

class EnrollmentModel extends BaseModel {
	public function get_enrollments() {
		$query_string = "SELECT * FROM enrollments GROUP BY role";
		$query = $this->db->query($query_string);
		$result = $query->fetchAll();
		foreach ($result as $role) {
			$current_role = $role['role'];
			echo $current_role . '<br>';
			$filter = array('role'=>$current_role);
			$this->enrolled_courses($filter);
		}
	}
	public function enrolled_courses($filter=array()) {
		$query_string = "SELECT canvas_course_id, COUNT(*) FROM enrollments WHERE role = :role GROUP BY canvas_course_id";
		$query = $this->db->prepare($query_string);
		$query->execute($filter);
		$result = $query->fetchAll();
		// print_r($result);
		echo '<ol>';
		foreach ($result as $course) {
			echo '<li>';
			print_r($course['canvas_course_id']);
			echo ' - ';
			print_r($course['COUNT(*)']);
			echo '</li>';
		}
		echo '</ol>';
		// return $result;
	}
	public function course_enrollments($filter=array()) {
		$query_string = "SELECT canvas_course_id, COUNT(*) FROM enrollments WHERE role = :role GROUP BY canvas_course_id";
		$query = $this->db->prepare($query_string);
		$query->execute($filter);
		$result = $query->fetchAll();
		foreach ($result as $role) {

		}
	}
}