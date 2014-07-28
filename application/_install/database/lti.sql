SELECT 
c.canvas_course_id, c.course_id, c.short_name as course_short_name, c.long_name as course_long_name, c.canvas_account_id, c.canvas_term_id, c.term_id, c.status as course_status, c.start_date as course_start_date, c.end_date as course_end_date, c.synced_at as course_synced_at,
ec.role as enrollment_type, ec.enrollments,
a.account_id, a.canvas_parent_id, a.parent_account_id,
a.name as account_name, a.status as account_status, a.synced_at as account_synced_at,
am.lft, am.rght, am.depth,
p_a.name as parent_account_name, p_a.status as parent_account_status, p_a.synced_at as parent_account_synced_at,
p_am.lft as parent_lft, p_am.rght as parent_rght, p_am.depth as parent_depth,
cm_name.id as cm_name_id, cm_name.meta_name as cm_name_meta_name, cm_name.meta_value as cm_name_meta_value,
    cm_name.synced_at as cm_name_synced_at, cm_name.course_id as cm_name_course_id, cm_name.sort as cm_name_sort,
cm_icon.id as cm_icon_id, cm_icon.meta_name as cm_icon_meta_name, cm_icon.meta_value as cm_icon_meta_value,
cm_icon.synced_at as cm_icon_synced_at, cm_icon.course_id as cm_icon_course_id, cm_icon.sort as cm_icon_sort
FROM `courses` as c
JOIN enrollment_counts as ec ON (
    c.canvas_course_id = ec.canvas_course_id
    AND ec.role = 'student'
    AND ec.enrollments > 0
    AND c.canvas_term_id = :term
)
JOIN accounts as a ON (
    c.canvas_account_id = a.canvas_account_id
)
JOIN account_meta as am ON (
    a.canvas_account_id = am.canvas_account_id 
    AND am.canvas_term_id = c.canvas_term_id
)
JOIN accounts as p_a ON (
    a.canvas_parent_id = p_a.canvas_account_id
)
JOIN account_meta as p_am ON (
    a.canvas_parent_id = p_am.canvas_account_id 
    AND p_am.canvas_term_id = c.canvas_term_id
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
ORDER BY p_am.lft, p_am.rght, p_a.name, 
am.lft, am.rght, 
a.name, a.canvas_account_id, 
c.long_name, c.canvas_course_id,
cm_name.sort, 
cm_icon.sort, 
cm_name.meta_name