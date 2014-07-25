<?php

/**
 * This is the "base controller class". All other "real" controllers extend this class.
 */
class Controller
{
    /**
     * @var null Database Connection
     */
    public $db = null;

    /**
     * Whenever a controller is created, open a database connection too. The idea behind is to have ONE connection
     * that can be used by multiple models (there are frameworks that open one connection per model).
     */
    function __construct()
    {
        $this->openDatabaseConnection();
        $this->initializeCanvasApi();
				
        session_start();
    }

    /**
     * Open the database connection with the credentials from application/config/config.php
     */
    private function openDatabaseConnection()
    {
        // set the (optional) options of the PDO connection. in this case, we set the fetch mode to
        // "objects", which means all results will be objects, like this: $result->user_name !
        // For example, fetch mode FETCH_ASSOC would return results like this: $result["user_name] !
        // @see http://www.php.net/manual/en/pdostatement.fetch.php
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

        // generate a database connection, using the PDO connector
        // @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
        $this->db = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $options);
    }
		
		private function initializeCanvasApi($url=CANVAS_API_URL, $token=CANVAS_API_TOKEN) {
			$this->canvasApi = new CanvasApi($url, $token);
		}

    /**
     * Load the model with the given name.
     * loadModel("SongModel") would include models/songmodel.php and create the object in the controller, like this:
     * $songs_model = $this->loadModel('SongsModel');
     * Note that the model class name is written in "CamelCase", the model's filename is the same in lowercase letters
     * @param string $model_name The name of the model
     * @return object model
     */
    public function loadModel($model_name)
    {
        require_once 'application/models/basemodel.php';
        
        $table_name = $model_name;
        
        if(file_exists('application/models/' . strtolower($model_name) . '.php')) {
          require_once 'application/models/' . strtolower($model_name) . '.php';
        } else {
          $model_name = 'BaseModel';
        }
        
        
        // return new model (and pass the database connection to the model)
        return new $model_name($this->db, $table_name);
    }

    public function loadReport($report_name)
    {
        require_once 'application/libs/reports/basereport.php';
        
        if(file_exists('application/libs/reports/' . strtolower($report_name) . '.php')) {
          require_once 'application/libs/reports/' . strtolower($report_name) . '.php';
        } else {
          $report_name = 'BaseReport';
        }
        
        // return new model (and pass the database connection to the model)
        return new $report_name($this);
    }

    public function render($view, $data_array = array())
    {
        // load Twig, the template engine
        // @see http://twig.sensiolabs.org
        $twig_loader = new Twig_Loader_Filesystem(PATH_VIEWS);
        
        // custom settings
        $twig = new Twig_Environment($twig_loader, array('debug' => true));
        $twig->addExtension(new Twig_Extension_Debug());
        
        $data_array['_app'] = array(
          'request' => array('url' => $_GET['url'], 'view' => $view, 'controller' => strtolower(get_class($this)), 'data' => $data_array),
          'session' => $_SESSION['canvas-admin-dashboard']
        );

        // render a view while passing the to-be-rendered data
        echo $twig->render($view . PATH_VIEW_FILE_TYPE, $data_array);
    }
    
    public function deleteFiles($files) {
      foreach($files as $path) {
        // normalize the destination path
        $absolute_destination = realpath(realpath(dirname(__FILE__) . "/../..") . $path);
        
        // create the absolute_destination directory if it doesn't exist
        if(file_exists($absolute_destination)) {
          unlink($absolute_destination);
          
          if(file_exists($absolute_destination)) {
            throw new Exceptoin("Unable to read/write to destination `$path`");
          }
        }
      }
    }
    
    public function uploadFiles($destination, $filesData, $options=array(
        'maxsize'=>200000,
        'types'=>array('image/jpeg', 'image/png'),
        'extensions'=>array('jpg', 'png'),
        'unsafe_chars'=>'/[^a-z0-9.-_]/'
      )
    ) {
      // normalize the destination path
      $absolute_destination = realpath(dirname(__FILE__) . "/../..") . $destination;
      
      // create the absolute_destination directory if it doesn't exist
      if(!file_exists($absolute_destination)) {
        mkdir($absolute_destination, 0755, true);
      }
      
      $absolute_destination = realpath($absolute_destination) . '/';
      
      if(!file_exists($absolute_destination)) {
        throw new Exceptoin("Unable to read/write to destination `$destination`");
      }
      
      $files = $filesData['files'];
      
      $allowedExts = $options['extensions'];
      
      $results = array();
      
      foreach($filesData['fields'] as $field) {
        $fileNames = $files['name'][$field];
        
        foreach($fileNames as $index=>$file) {
          // clean up the file name
          $file = preg_replace($options['unsafe_chars'], '_', strtolower($file));
          
          $temp = explode(".", $file);
          $extension = end($temp);
          
          $type = $files["type"][$field][$index];
          $size = $files["size"][$field][$index];
          $error = $files["error"][$field][$index];
          $temp_file = $files["tmp_name"][$field][$index];
          
          if (!in_array($type, $options['types'])) {
            throw new Exception("Invalid mime type `$type` (allowed types are: `" . implode($options['types'], '`, `') . '`)');
          } elseif($size > $options['maxsize']) {
            throw new Exception("Invalid file size `$size` bytes (max allowed is " . $options['maxsize'] . " bytes)");
          } elseif(!in_array($extension, $allowedExts)) {
            throw new Exception("Invalid file extension `$extension`");
          } else {
            if ($error > 0) {
              throw new Exception("Error uploading files. Error code of `$error` given.");
            } else {
              if (file_exists($absolute_destination . $file)) {
                throw new Exception("File `$file` already exists");
              } else {
                move_uploaded_file($temp_file, $absolute_destination . $file);
                
                // store the websafe path
                $results[] = $destination . $file;
              }
            }
          }
        }
      }
      
      return $results;
    }
    
    public function authenticate($credentials) {
      if($credentials['email'] != '' && $credentials['password'] != '') {
        $user_model = $this->loadModel('UserModel');
        
        $user = $user_model->findOne(array(
          'email'=>$credentials['email']
        ));
        
        if($user) {
          if(!$user['salt']) {
            $user['salt'] = uniqid(mt_rand(), true);
          }
          
          $hashed_password = hash('sha512', $credentials['password'] . $user['salt']);
          
          if($user['password_hash'] == $hashed_password) {
            $_SESSION['c2dt_iu']['user'] = $user;
            
            return true;
          }
        }
      }
      
      return false;
    }
    
    public function endSession() {
      $_SESSION['c2dt_iu'] = array();
    }
    
    public function authenticatedUser() {
      if(isset($_SESSION['c2dt_iu']['user'])) {
        $user_model = $this->loadModel('UserModel');
        
        $user = $user_model->findByKey($_SESSION['c2dt_iu']['user']['id']);
        $_SESSION['c2dt_iu']['user'] = $user;
        
        return $user;
      } else {
        return null;
      }
    }
    
    public function requiresAuthentication($type=null) {
      $user = $this->authenticatedUser();
      
      // if a type is specified, make sure the authenticated user is the specified type
      if($type && $user['type'] != $type) {
        // if not, get out of here
        header('Location: ' . URL);
        exit;
      } else {
        // return the user
        return $user;
      }
    }
}
