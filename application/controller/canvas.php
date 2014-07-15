<?php

class Canvas extends Controller
{
    public function accounts() {
        $file = 'accounts.csv';
        $model = 'account';
        $this->import_csv($file, $model);
    }

    public function courses() {
        $file = 'courses.csv';
        $model = 'course';
        $this->import_csv($file, $model);
    }

    public function enrollments() {
        $file = 'enrollments.csv';
        $model = 'enrollment';
        $this->import_csv($file, $model);
    }

    public function terms() {
        $file = 'terms.csv';
        $model = 'term';
        $this->import_csv($file, $model);
    }

    public function users() {
        $file = 'users.csv';
        $model = 'user';
        set_time_limit(300);
        $this->import_csv($file, $model);
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

        
            if ($i > 30) {
                break;
            }
        }
    }

    private function import_csv($fname, $model_name) {
        $model = $this->loadModel(ucfirst($model_name) . 'Model');
        $chunksize = 50;

        $upload_folder = realpath(__DIR__ . '/../../public/uploads/');
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
                $this->commit_buffer($buffer, $field_map, $model);
                $buffer = array(); //blank out the buffer.
            }
        }

        //clean out remaining buffer entries.
        if( count($buffer) ) { $this->commit_buffer($buffer, $field_map, $model); }

    }

    private function commit_buffer($buffer, $field_map, $model) {
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
                $data['synced_at'] = NOW;
                try {
                    $model->insert($data);
                } catch (PDOException $exception) {
                    $DUPLICATE_KEY_CODE = 1062;

                    if($exception->errorInfo[1] == $DUPLICATE_KEY_CODE) {
                        /*$model->delete(array($primary_key_column=>$primary_key));
                        $model->insert($data);

                        echo "delete insert complete - $primary_key<br>";*/
                    } else {
                        throw $exception;
                    }
                }
            }
        }
        //run inserts
        $buffer = array(); //blank out the buffer.
    }
}
