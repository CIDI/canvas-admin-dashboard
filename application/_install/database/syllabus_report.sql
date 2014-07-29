SELECT 
c.canvas_course_id, c.course_id, c.short_name as course_short_name, c.long_name as course_long_name, c.canvas_account_id, c.canvas_term_id, c.term_id, c.status as course_status, c.start_date as course_start_date, c.end_date as course_end_date, c.synced_at as course_synced_at, c.institution_id,
ec.role as enrollment_type, ec.enrollments,
a.account_id, a.canvas_parent_id, a.parent_account_id,
a.name as account_name, a.status as account_status, a.synced_at as account_synced_at,
am.lft, am.rght, am.depth,
p_a.name as parent_account_name, p_a.status as parent_account_status, p_a.synced_at as parent_account_synced_at,
p_am.lft as parent_lft, p_am.rght as parent_rght, p_am.depth as parent_depth,
e.canvas_user_id as teacher_canvas_user_id, e.user_id as teacher_user_id, e.canvas_section_id, e.section_id, e.status as teacher_status, e.canvas_associated_user_id, e.associated_user_id, e.synced_at as teacher_synced_at, 
u.login_id as teacher_login_id, u.first_name as teacher_first_name, u.last_name as teacher_last_name, u.email as teacher_email, u.synced_at as users_synced_at, 
cm.id as course_meta_id, cm.meta_category_id, cm.meta_name, cm.meta_value, cm.synced_at as course_meta_synced_at, cm.sort as course_meta_sort
FROM `courses` as c
JOIN enrollment_counts as ec ON (
    c.canvas_course_id = ec.canvas_course_id
    AND ec.role = 'student'
    AND ec.enrollments > 0
    AND c.canvas_term_id = :term
    AND ec.institution_id = :institution
)
JOIN accounts as a ON (
    c.canvas_account_id = a.canvas_account_id
    AND a.institution_id = :institution
)
JOIN account_meta as am ON (
    a.canvas_account_id = am.canvas_account_id 
    AND am.canvas_term_id = c.canvas_term_id
    AND am.institution_id = :institution
)
JOIN accounts as p_a ON (
    a.canvas_parent_id = p_a.canvas_account_id
    AND p_a.institution_id = :institution
)
JOIN account_meta as p_am ON (
    a.canvas_parent_id = p_am.canvas_account_id 
    AND p_am.canvas_term_id = c.canvas_term_id
    AND p_am.institution_id = :institution
)
JOIN enrollments as e ON (
    c.canvas_course_id = e.canvas_course_id
    AND e.role = 'teacher'
    AND e.institution_id = :institution
)
JOIN users as u ON (
    e.canvas_user_id = u.canvas_user_id
    AND u.institution_id = :institution
)
LEFT JOIN course_meta as cm ON (
    c.canvas_course_id = cm.course_id
    AND cm.meta_category_id = 1
    AND meta_name = 'syllabus_body'
    AND cm.institution_id = :institution
) 
WHERE c.institution_id = :institution
ORDER BY p_am.lft, p_am.rght, p_a.name, am.lft, am.rght, a.name, a.canvas_account_id, c.long_name, c.canvas_course_id, u.last_name