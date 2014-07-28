CREATE VIEW enrollment_counts AS
SELECT `role`, `canvas_course_id`, `institution_id`, count(*) as enrollments FROM enrollments
GROUP BY canvas_course_id, role, institution_id
ORDER BY institution_id, canvas_course_id, role