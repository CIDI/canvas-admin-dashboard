SELECT * 
FROM course_meta
WHERE meta_category_id = 2
AND course_id = :canvas_course_id
ORDER BY sort
LIMIT 0 , 30