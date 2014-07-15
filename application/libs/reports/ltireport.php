<?php

class LtiReport extends BaseReport {
	function config() {
		$this->resource_pattern = 'courses/:course_id/external_tools';

		// $this->register('has_syllabus');
		$this->track();
	}

	// public function has_syllabus($meta_record){

	// 	echo 'hi'; exit;

	// }
}