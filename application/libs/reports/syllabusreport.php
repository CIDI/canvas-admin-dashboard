<?php

class SyllabusReport extends BaseReport {
	function config() {
		$this->resource_pattern = 'courses/:course_id?include[]=syllabus_body';

		$this->register('has_syllabus');
		$this->track('syllabus_body');
	}
}