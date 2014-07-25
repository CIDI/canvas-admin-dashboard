SELECT 
count(c.canvas_course_id) as course_count,
cm_name.id as cm_name_id, cm_name.meta_name as cm_name_meta_name, cm_name.meta_value as tool_name,
    cm_name.synced_at as cm_name_synced_at, cm_name.course_id as cm_name_course_id, cm_name.sort as cm_name_sort,
cm_icon.id as cm_icon_id, cm_icon.meta_name as cm_icon_meta_name, cm_icon.meta_value as tool_icon,
    cm_icon.synced_at as cm_icon_synced_at, cm_icon.course_id as cm_icon_course_id, cm_icon.sort as cm_icon_sort
FROM `courses` as c
JOIN enrollment_counts as ec ON (
    c.canvas_course_id = ec.canvas_course_id
    AND ec.role = 'student'
    AND ec.enrollments > 0
    AND c.canvas_term_id = :canvas_term_id
)
JOIN course_meta as cm_name ON (
    c.canvas_course_id = cm_name.course_id
    AND cm_name.meta_category_id = 2
    AND cm_name.meta_name = 'name'
) 
LEFT JOIN course_meta as cm_icon ON (
    cm_name.course_id = cm_icon.course_id
    AND cm_icon.meta_category_id = 2
    AND cm_icon.meta_name = 'icon_url'
    AND cm_name.sort = cm_icon.sort
) 
GROUP BY tool_name
ORDER BY 
tool_name