<?php

class CanvasApi {
	// This page contains the Canvas API calls necessary to run the USU Canvas Course Syllabus Tracker
	// created by Kenneth Larsen for the Center for Innovative Design and Instruction at Utah State University
	public function __construct($url, $token) {
		// Root url for all api calls and links back to Canvas
		$this->canvasURL = $url;
		// This is the header containing the authorization token from Canvas
		$this->token = $token;
		
		$this->tokenHeader = array("Authorization: Bearer ".$this->token);
	}
	
		function curlGet_old($url) {
			$ch = curl_init($url);
			curl_setopt ($ch, CURLOPT_URL, $this->canvasURL.'/api/v1/'.$url);
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $this->tokenHeader);
			curl_setopt ($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ask for results to be returned

			// Send to remote and return data to caller.
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}
		function http_parse_headers( $header ) {
		    $retVal = array();
		    $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
		    foreach( $fields as $field ) {
		        if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
		            $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
		            if( isset($retVal[$match[1]]) ) {
		                $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
		            } else {
		                $retVal[$match[1]] = trim($match[2]);
		            }
		        }
		    }
	    	return $retVal;
		}
		function curlGet($url) {
			global $token;
			$ch = curl_init($url);
			curl_setopt ($ch, CURLOPT_URL, $this->canvasURL.'/api/v1/'.$url);
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $this->tokenHeader);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ask for results to be returned
			curl_setopt($ch, CURLOPT_VERBOSE, 1); //Requires to load headers
			curl_setopt($ch, CURLOPT_HEADER, 1);  //Requires to load headers
			$result = curl_exec($ch);
			// var_dump($result);

			#Parse header information from body response
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($result, 0, $header_size);
			$body = substr($result, $header_size);
			$data = json_decode($body);
			curl_close($ch);
				
			#Parse Link Information
			$header_info = $this->http_parse_headers($header);
			if(isset($header_info['Link'])){
				$links = explode(',', $header_info['Link']);
				foreach ($links as $value) {
					if (preg_match('/^\s*<(.*?)>;\s*rel="(.*?)"/', $value, $match)) {
						$links[$match[2]] = $match[1];
					}
				}
			}
			#Check for Pagination
            if(isset($links['next'])){
                // Remove the API url so it is not added again in the get call
                $next_link = str_replace($this->canvasURL.'/api/v1/', '', $links['next']);
                $next_data = $this->curlGet($next_link);
                $data = array_merge($data,$next_data);
                return $data;
            }else{
                return $data;
            }
		}
		
		function getCourseSyllabus($courseID){
			$apiUrl = "courses/".$courseID."?include[]=syllabus_body";
			$response = $this->curlGet($apiUrl);
			$responseData = json_decode($response, true);
			return $responseData['syllabus_body'];
		}
		function getTeacher($courseID){
			$getTeacherUrl = "courses/".$courseID."/users/?enrollment_type=teacher";
			$response = $this->curlGet($getTeacherUrl);
			$responseData = json_decode($response, true);
			return $responseData;
		}
		function hasStudents($courseID, $perPage=1){
			$response = $this->curlGet("courses/".$courseID."/enrollments/?type=StudentEnrollment&per_page=".$perPage);
			$checkEnrollmentList = json_decode($response);
		  	$studentCount = count($checkEnrollmentList);
		  	if($studentCount>0){
		  		$hasStudents = 1;
		  	} else {
		  		$hasStudents = 0;
		  	}
			return $hasStudents;
		}
		function listSubAccounts($accountID, $pageNum, $perPage=50){
			$response = $this->curlGet("accounts/".$accountID."/sub_accounts?per_page=".$perPage."&page=".$pageNum);
			return $response;
		}
		function listUserAccounts(){
			// List accounts that the current user can view or manage. 
			// Typically, students and even teachers will get an empty list in response, 
			// only account admins can view the accounts that they are in.
			$response = $this->curlGet("accounts/");
			return $response;
		}
		function listAccountCourses($accountID, $additionalParams, $pageNum, $perPage=50){
			$response = $this->curlGet("accounts/".$accountID."/courses?per_page=".$perPage."&page=".$pageNum."&".$additionalParams);
			return $response;
		}
}