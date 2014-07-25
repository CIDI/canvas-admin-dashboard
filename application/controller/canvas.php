<?php

class Canvas extends Controller
{
    public function process_accounts($canvas_term_id) {
        $file = $this->report_prefix($canvas_term_id) . 'accounts.csv';
        $model = 'account';
        $this->import_csv($file, $model, $canvas_term_id);

        $institution_model = $this->loadModel('InstitutionModel');
        $institution = $institution_model->findByKey($_SESSION['canvas-admin-dashboard']['institution_id']);

        $account_meta_model = $this->loadModel('Account_metaModel');

        $account_meta_model->delete(array(
            'canvas_term_id'=>$canvas_term_id,
            'institution_id'=>$institution['id']
        ));

        $this->account_meta($canvas_term_id, $institution['primary_canvas_account_id']);
    }

    public function process_xlist($canvas_term_id) {
        $file = $this->report_prefix($canvas_term_id) . 'xlist.csv';
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
        $file_name_prefix = $_SESSION['canvas-admin-dashboard']['institution_id'] . '_';

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

            $report->process($item, $response, $course['canvas_course_id'], $meta_category['id']);

        
            // if ($i > 30) {
            //     break;
            // }
        }
    }

    private function import_csv($fname, $model_name, $canvas_term_id='', $remove_file=true) {
        $model = $this->loadModel(ucfirst($model_name) . 'Model');
        $chunksize = 50;

        $delete_filter = array('institution_id'=>$_SESSION['canvas-admin-dashboard']['institution_id']);

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
                $data['institution_id'] = $_SESSION['canvas-admin-dashboard']['institution_id'];

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
            'institution_id'=>$_SESSION['canvas-admin-dashboard']['institution_id']
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
                    'institution_id'=>$_SESSION['canvas-admin-dashboard']['institution_id']
                );

                $account_meta_model->insert($properties);
                $index++;
            }

        }
        return $index;
    }
}
