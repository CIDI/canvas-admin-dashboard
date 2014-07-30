SELECT 
count(c.canvas_course_id) as course_count,
c.institution_id,
cm_name.id as cm_name_id, cm_name.meta_name as cm_name_meta_name, cm_name.meta_value as tool_name,
    cm_name.synced_at as cm_name_synced_at, cm_name.course_id as cm_name_course_id, cm_name.sort as cm_name_sort,
cm_icon.id as cm_icon_id, cm_icon.meta_name as cm_icon_meta_name, cm_icon.meta_value as tool_icon,
    cm_icon.synced_at as cm_icon_synced_at, cm_icon.course_id as cm_icon_course_id, cm_icon.sort as cm_icon_sort
FROM (
	SELECT * FROM courses AS t
	WHERE t.canvas_term_id = :term
    AND t.institution_id = :institution
	) AS c
JOIN (
	SELECT * FROM enrollment_counts AS t
	WHERE t.role = 'student'
    AND t.enrollments > 0
	AND t.canvas_term_id = :term
    AND t.institution_id = :institution
) AS ec ON ( c.canvas_course_id = ec.canvas_course_id )
JOIN (
	SELECT * FROM course_meta AS t
    WHERE t.meta_category_id = 2
    AND t.meta_name = 'name'
	AND t.canvas_term_id = :term
    AND t.institution_id = :institution
) AS cm_name ON ( c.canvas_course_id = cm_name.course_id )
LEFT JOIN (
	SELECT * FROM course_meta as t
    WHERE t.meta_category_id = 2
    AND t.meta_name = 'icon_url'
	AND t.canvas_term_id = :term
    AND t.institution_id = :institution
) AS cm_icon ON (
    cm_name.course_id = cm_icon.course_id
    AND cm_name.sort = cm_icon.sort
) 
GROUP BY tool_name
ORDER BY tool_name