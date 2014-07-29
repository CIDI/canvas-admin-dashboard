<?php

class Canvas extends Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->require_authentication();
    }

    private function require_authentication() {
        if(isset($_SESSION['canvas-admin-dashboard']['user']) && stripos($_SESSION['canvas-admin-dashboard']['user']['roles'], 'Administrator')) {
            if(!$this->institution['oauth_token'] &&  strpos($_GET['url'], 'admin/institution') !== 0) {
                header('Location: ' . URL . 'admin/institution');
                exit;
            }
        } else {
            //Check to see if the lti handshake passes
            require_once './application/libs/ims-blti/blti.php';

            $context = new BLTI(LTI_SHARED_SECRET, false, false);
            if ($context->valid && stripos($context->info['roles'], 'Administrator')) {
                $_SESSION['canvas-admin-dashboard']['user'] = array(
                    'display_name'=>$context->info['lis_person_name_full'],
                    'login_id'=>$context->info['custom_canvas_user_login_id'],
                    'roles'=>$context->info['roles']
                );

                $institution_model = $this->loadModel('InstitutionModel');

                $institution = $institution_model->findOne( array(
                    'api_domain'=>$context->info['custom_canvas_api_domain']
                ));

                if (!$institution) {
                    $institution_model->insert( array(
                        'api_domain'=>$context->info['custom_canvas_api_domain'],
                        'name'=>$context->info['tool_consumer_instance_name'],
                        'slug'=>preg_replace('/\.instructure.com$/', '', $context->info['custom_canvas_api_domain'])
                    ));

                    $institution = $institution_model->findByKey($this->db->lastInsertId());

                    header('Location: ' . URL . 'admin/institution');
                }

                $_SESSION['canvas-admin-dashboard']['institution']['id'] = $institution['id'];
            } else {
                // if not, show message
                $this->render('admin/unauthorized');exit;
            }
        }
    }

    public function process_accounts($canvas_term_id) {
        $file = $this->report_prefix($canvas_term_id) . 'accounts.csv';
        $model = 'account';
        $this->import_csv($file, $model, $canvas_term_id);

        $institution_model = $this->loadModel('InstitutionModel');
        $institution = $institution_model->findByKey($this->institution['id']);

        $account_meta_model = $this->loadModel('Account_metaModel');

        $account_meta_model->delete(array(
            'canvas_term_id'=>$canvas_term_id,
            'institution_id'=>$institution['id']
        ));

        $this->account_meta($canvas_term_id, $institution['primary_canvas_account_id']);
    }

    public function process_xlists($canvas_term_id) {
        $file = $this->report_prefix($canvas_term_id) . 'xlists.csv';
        $model = 'xlist';
        $this->import_csv($file, $model, $canvas_term_id);
    }

    public function process_courses($canvas_term_id) {
        $file = $this->report_prefix($canvas_term_id) . 'courses.csv';
        $model = 'course';
        $this->import_csv($file, $model, $canvas_term_id);
    }

    public function process_enrollments($canvas_term_id) {
        $file = $this->report_prefix($canvas_term_id) . 'enrollments.csv';
        $model = 'enrollment';
        $this->import_csv($file, $model, $canvas_term_id);

        $this->enrollment_counts($canvas_term_id, $institution['id']);
    }


    public function process_users($canvas_term_id) {
        $file = $this->report_prefix($canvas_term_id) . 'users.csv';
        $model = 'user';
        set_time_limit(300);
        $this->import_csv($file, $model, $canvas_term_id);
    }
    public function process_terms() {
        $file = $this->report_prefix() . 'terms.csv';
        $model = 'term';
        $this->import_csv($file, $model);
    }

    protected function report_prefix($canvas_term_id='') {
        $file_name_prefix = $this->institution['id'] . '_';

        if($canvas_term_id!='') {
          $file_name_prefix .= $canvas_term_id . '_';
        }

        return $file_name_prefix;
    }

    public function dashboard() {
        $filter = array('account'=>15);

        $account_model = $this->loadModel('AccountModel');
        $account_stats = $account_model->stats($filter);

        echo "really?";
        print_r($account_stats);exit;
    }

    public function collect_data($term, $category, $item='all') {
        $course_model = $this->loadModel('CourseModel');
        $active_courses = $course_model->find_active($term);

        if ($category == 'syllabus') {
            $resource_pattern = 'courses/:course_id?include[]=syllabus_body';
            $report = $this->loadReport('SyllabusReport');
            $meta_name = 'body';
        } else if ($category == 'lti') {
            $resource_pattern = 'courses/:course_id/external_tools';
            $report = $this->loadReport('LtiReport');
        }

        foreach ($active_courses as $i => $course) {
            $api_url = str_replace(':course_id', $course['canvas_course_id'], $resource_pattern);
            $response = $this->canvasApi->curlGet($api_url);

            // run report logic
            $meta_category_model = $this->loadModel('Meta_categoryModel');
            $meta_category = $meta_category_model->findOne(array('category_name'=>$category));

            $report->process($term, $item, $response, $course['canvas_course_id'], $meta_category['id']);

        
            // if ($i > 30) {
            //     break;
            // }
        }
    }

    private function import_csv($fname, $model_name, $canvas_term_id='', $remove_file=true) {
        $model = $this->loadModel(ucfirst($model_name) . 'Model');
        $chunksize = 50;

        $delete_filter = array('institution_id'=>$this->institution['id']);

        if($canvas_term_id) {
            $delete_filter['canvas_term_id'] = $canvas_term_id;
        }

        $model->delete($delete_filter);

        $upload_folder = PATH_UPLOADS;
        $csv_file = $upload_folder . '/' . $fname;

        if( ! $fh = fopen($csv_file, 'r') ) {
            throw new Exception("Could not open $fname for reading.");
        }

        $i=0;
        $buffer = array();

        $field_map = explode(',', fgets($fh));

        while(!feof($fh)) {
            $buffer[] = fgets($fh);
            $i++;
            if( ($i % $chunksize) == 0 ) {
                $this->commit_buffer($buffer, $field_map, $model, $canvas_term_id);
                $buffer = array(); //blank out the buffer.
            }
        }

        //clean out remaining buffer entries.
        if( count($buffer) ) { $this->commit_buffer($buffer, $field_map, $model, $canvas_term_id); }

        if($remove_file) {
            unlink($csv_file);
        }

    }

    private function commit_buffer($buffer, $field_map, $model, $canvas_term_id) {
        foreach( $buffer as $line ) {
            $fields = str_getcsv($line);
            //create inserts
            $data = array();

            foreach ($fields as $i=>$field_value_raw) {
                $field_value = trim($field_value_raw);

                if($field_value != '') {
                    $field = trim($field_map[$i]);

                    $data[$field] = $field_value;
                }
            }

            if(count($data) > 0) {
                $data['institution_id'] = $this->institution['id'];

                $data['synced_at'] = NOW;
                if (!isset($data['canvas_term_id'])) {
                    $data['canvas_term_id'] = $canvas_term_id;
                }


                try {
                    $model->insert($data);
                } catch (PDOException $exception) {
                    $DUPLICATE_KEY_CODE = 1062;

                    // duplicate keys have been identified for users and enrollments 
                    // seems to be based off of users that have more than one login id
                    // not relevant for the report, so skipping any duplicate records
                    if($exception->errorInfo[1] != $DUPLICATE_KEY_CODE) {
                        throw $exception;
                    }
                }
            }
        }
        //run inserts
        $buffer = array(); //blank out the buffer.
    }
    private function account_meta($term_id, $parent_id, $depth=1, $index=1, $max_depth=20) {
        if($depth > $max_depth) {
            throw new Exception("Maximum account meta depth exceeded, depth=".$depth);
        }
        // model for storing account records to the database
        $account_model = $this->loadModel('AccountModel');
        $account_meta_model = $this->loadModel('Account_metaModel');
        
        // get the account list from canvas
        $account_filter = array(
            'canvas_parent_id'=>$parent_id,
            'institution_id'=>$this->institution['id']
        );
        $accounts = $account_model->findAll($account_filter);
        
        // keep track of how many results need to be processed in this batch
        $resultCount = count($accounts);
    
        // only try processing if there are results to be processed
        if($resultCount > 0) {
            // loop through colleges
            foreach ($accounts as $account) {
                $left_index = $index;
                
                // move on to find children accounts
                $right_index = $this->account_meta(
                    $term_id,
                    $account['canvas_account_id'],
                    $depth+1,
                    $left_index+1,
                    $max_depth
                );
                $index=$right_index;
                // collect all properties that would need to be inserted into the database
                $properties = array(
                    'canvas_account_id'=>$account['canvas_account_id'],
                    'canvas_term_id'=>$term_id,
                    'depth'=>$depth,
                    'lft'=>$left_index,
                    'rght'=>$right_index,
                    'institution_id'=>$this->institution['id']
                );

                $account_meta_model->insert($properties);
                $index++;
            }

        }
        return $index;
    }

    public function updateenrollmentcounts($canvas_term_id) {
        $this->enrollment_counts($canvas_term_id, $this->institution['id']);
    }

    private function enrollment_counts($canvas_term_id, $institution_id) {
        $enrollment_count_model = $this->loadModel('Enrollment_countModel');

        $enrollment_count_model->delete(array(
            'canvas_term_id'=>$canvas_term_id,
            'institution_id'=>$institution_id
        ));

        // uber fun query to insert course counts including cross listed courses
        $query = $this->db->prepare(
            "-- sub query refactor
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
            WHERE t.institution_id = :institution AND t.canvas_term_id = :term
        ) AS e

        LEFT JOIN (
            -- prefilter
            SELECT *
            FROM xlists as t
            WHERE t.institution_id = :institution AND t.canvas_term_id = :term
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
            WHERE t.institution_id = :institution AND t.canvas_term_id = :term
        ) as e
        JOIN (
            -- prefilter
            SELECT *
            FROM xlists as t
            WHERE t.institution_id = :institution AND t.canvas_term_id = :term
        ) as x ON (
            e.canvas_section_id = x.canvas_section_id
        )
    )
) AS term_enrollments
-- count role enrollments for a course
GROUP BY canvas_course_id, role, institution_id, canvas_term_id
ORDER BY canvas_term_id, institution_id, canvas_course_id, role, canvas_user_id"
        );
        
        $query->execute(array(
            'term'=>$canvas_term_id,
            'institution'=>$institution_id
        ));
    }
}
