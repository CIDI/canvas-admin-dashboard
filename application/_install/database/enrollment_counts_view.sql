-- sub query refactor
INSERT INTO enrollment_counts (role, canvas_course_id, institution_id, canvas_term_id, enrollments)
SELECT role, canvas_course_id, institution_id, canvas_term_id, count(*) as enrollments
FROM
(
	(
		-- all enrollments that don't have a match in the xlist table
		SELECT e.role, e.canvas_course_id, e.institution_id,
			e.canvas_term_id, e.canvas_user_id
		FROM (
			-- prefilter to speed up the query (takes over an hour if you don't)
		    SELECT *
		    FROM enrollments as t
		    WHERE t.institution_id = 3 AND t.canvas_term_id = 522
		) AS e

		LEFT JOIN (
			-- prefilter
		    SELECT *
		    FROM xlists as t
		    WHERE t.institution_id = 3 AND t.canvas_term_id = 522
		) AS x ON (
		    x.canvas_section_id = e.canvas_section_id
		)
		-- filter out any records that joined
		WHERE x.canvas_xlist_course_id IS NULL
	)

	UNION
	(
		-- all enrollments that have a xlist record
		-- select the xlist course ID instead
		SELECT e.role, x.canvas_xlist_course_id as canvas_course_id, e.institution_id,
			e.canvas_term_id, e.canvas_user_id
	    FROM (
	    	-- prefilter
	    	SELECT *
	    	FROM enrollments as t
	    	WHERE t.institution_id = 3 AND t.canvas_term_id = 522
	    ) as e
		JOIN (
			-- prefilter
			SELECT *
			FROM xlists as t
			WHERE t.institution_id = 3 AND t.canvas_term_id = 522
		) as x ON (
		    e.canvas_section_id = x.canvas_section_id
		)
	)
) AS term_enrollments
-- count role enrollments for a course
GROUP BY canvas_course_id, role, institution_id, canvas_term_id
ORDER BY canvas_term_id, institution_id, canvas_course_id, role, canvas_user_id