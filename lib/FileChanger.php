<?php
// TODO: Error Messages Warning: chmod(): Permission denied in /var/www/test_WP/src/wp-content/plugins/fix-permission/lib/FileChanger.php on line 84

// TODO: Lang
// TODO: Restfull API
// TODO: add filler or action

interface FileChanger {
    
    public function apply(string $path);
}

class UpdatePermission implements FileChanger {

    /**
     * Static array of available in plugin chmod flags. Array keys is string, value is octal number.    
     *
     * @since 1.0.0
     *
     * @access public
     * @var array $permission_flags array('0600' => 0600, ...
     */

    public static $permission_flags = array(
        '0600' => 0600,
        '0644' => 0644,
        '0764' => 0764,
        '0776' => 0776,
        '0777' => 0777
        );

    /**
     * User selected and checked permission flag,    
     *
     * @since 1.0.0
     *
     * @access public
     * @var int $permission_flags 
     */    
    private $permission_flag;
    
    /**
     * Switch test mode, when real action don't run.
     *
     * @since 1.0.0
     *
     * @access private
     * @var bool $test_mode 
     */    
    private $test_mode;
    
    /**
     * Constructor method, set $test_mode and $permission_flag properties
     *
     * @since 1.0.0
     *
     * @access public
     * @param boolean $mode status of test mode
     * @param string $flag_str permission flag, used like key of $permission_flags
     * @return void
     */  
    public function __construct(bool $mode, string $flag_str) {
        $this->test_mode = $mode;
        
        if(isset(UpdatePermission::$permission_flags[$flag_str])) {
            $this->permission_flag = UpdatePermission::$permission_flags[$flag_str];
        } else
            $this->permission_flag = 0764;
    }

    /**
     * Run selected action chmod.
     *
     * @since 1.0.0
     *
     * @access public
     * @param string $path Full path to the file
     * @return bool
     */    
    public function apply(string $path) {
        $result = true;
        if (!$this->test_mode)
            $result = chmod($path, $this->permission_flag);
        
        return $result;
    }
}

class DeleteFile implements FileChanger {

    /**
     * Switch test mode, when real action don't run.
     *
     * @since 1.0.0
     *
     * @access private
     * @var bool $test_mode 
     */     
    private $test_mode;

    /**
     * Constructor method, set $test_mode property
     *
     * @since 1.0.0
     *
     * @access public
     * @param boolean $mode status of test mode
     * @param string $flag_str permission flag, used like key of $permission_flags
     * @return void
     */    
    public function __construct(bool $mode) {
        $this->test_mode = $mode;
    }

    /**
     * Run selected action rmdir or unlink.
     *
     * @since 1.0.0
     *
     * @access public
     * @param string $path Full path to the file
     * @return bool
     */    
    public function apply(string $path) {
        $result = true;
        if (!$this->test_mode)
            if (is_dir($path)) {
                $result = rmdir($path);
            } else
                $result = unlink($path);
            
        return $result;
    }
}

abstract class FilesLoopAbstract {

    /**
     * Switch test mode, when real action don't run.
     *
     * @since 1.0.0
     *
     * @access private
     * @var array $path_array 
     */      
    private $path_array = array();
    
    /**
     * Information about each specified path.
     *
     * @since 1.0.0
     *
     * @access private
     * @var array $statuses 
     */      
    private $statuses = array();

    /**
     * Information about counts changed files.
     *
     * @since 1.0.0
     *
     * @access private
     * @var array $progress_count 
     */      
    private $progress_count = array();

    /**
     * Logging all path founded. 
     *
     * @since 1.0.0
     *
     * @access public
     * @var array $log 
     */      
    public $log = array();

    /**
     * Switch test mode, when real action don't run.
     *
     * @since 1.0.0
     *
     * @access public
     * @var bool $test_mode 
     */      
    public $test_mode = true;

    /**
     * Switch recursion mode.
     *
     * @since 1.0.0
     *
     * @access public
     * @var bool $recursion 
     */  
    public $recursion = false;

    /**
     * Hold processed path key for correct statistic.
     *
     * @since 1.0.0
     *
     * @access private
     * @var int $active_path_key 
     */    
    private $active_path_key;
    
    
    /**
     * Factory method return objects of interface FileChanger
     *
     * @since 1.0.0
     * @abstract
     * @access public
     * @return FileChanger 
     */      
    abstract public function getAction(): FileChanger;

    /**
     * Validate user specified path, normalize, check relative path and set property
     *
     * @since 1.0.0
     *
     * @access public
     * @param array $paths user specified paths
     * @return void
     */    
    public function setPaths(array $paths) {

        foreach ($paths as $key => $item) {
            // Clear strange not visible symbols
            $item = preg_replace('/[\x00-\x1F\x7F\s]/u', '', $item);
            if (mb_strpos($item,'/..') !== FALSE)
                $item = realpath($item);

            if (empty($item)) {
                unset($paths[$key]);
                continue;
            }

            $tmp_status = 'Not exist ';
            // Normalization and add base of path if it relative
            if (mb_strpos( wp_normalize_path($item), wp_normalize_path(ABSPATH)) === FALSE) {
                $item = wp_normalize_path(ABSPATH.$item);
            } else {
                $item = wp_normalize_path($item);
            }
            
            // If path end by / - delete this symbol
            $lastchar = mb_substr($item,-1);
            if ($lastchar  == '/') {
                $num = mb_strlen($item)-1;
                $item = mb_substr($item,0,$num);
            }

            $paths[$key] = $item;
            if (file_exists($item))
                $tmp_status = 'Exist ';
            
            if (is_file($item))
                $tmp_status .= 'file';
            elseif(is_dir($item))
                $tmp_status .= 'dir';
            elseif(is_link($item))
                $tmp_status .= 'link';
            
            $this->statuses[$key] = $tmp_status;
        }
        $this->path_array = $paths;
    }

    /**
     * Foreach paths from $path_array property and call applyAction()
     *
     * @since 1.0.0
     *
     * @access public
     * @param array $paths user specified paths
     * @return void
     */ 
    public function runLoop () {
        foreach ($this->path_array AS $key => $path) {
            $this->active_path_key = $key;
            $this->progress_count[$key] = 1;
            $this->applyAction($path);
        }
    }

    /**
     * Method with recursion. Look all files on path in property.
     *
     * @since 1.0.0
     *
     * @access public
     * @param string $path Path to file or directory
     * @return bool 
     */    
    private function applyAction(string $path) {
        if (!file_exists($path))
            return false;
        
        $changerObject = $this->getAction();

        if (is_dir($path) && $this->recursion) {
            $dir_handle = opendir($path);

            if (!$dir_handle)
                return false;
            while($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (!is_dir($path."/".$file)) {
                        if ($changerObject->apply($path."/".$file))
                            $this->progress_count[$this->active_path_key]++;
                        $this->log[] = $path."/".$file; 
                    } else
                        $this->applyAction($path.'/'.$file);
                }
            }
            closedir($dir_handle);
        }
        
        $changerObject->apply($path);
        $this->log[] = $path;
        
        return true;
    }

    /**
     * Return all paths like string. Each of item separated by line break.
     *
     * @since 1.0.0
     *
     * @access public
     * @return string 
     */      
    public function getPaths() {
        if (!empty($this->path_array))
            return implode(PHP_EOL, $this->path_array);
    }
    
    /**
     * Return all Statuses. Each of item separated by line break.
     *
     * @since 1.0.0
     *
     * @access public
     * @return string 
     */       
    public function getStatuses() {
        if (!empty($this->statuses)) {
            $result = '';
            foreach ($this->statuses As $key => $value) {
                $result .= $value.'. Сhanged:'.$this->progress_count[$key].PHP_EOL;
            }
        
            return $result;
        }
    }
}

class DeleteLoop extends FilesLoopAbstract {

    /**
     * Implements the required method. Returns an object of the action class.
     *
     * @since 1.0.0
     *
     * @access public
     * @return object 
     */       
    public function getAction(): \FileChanger {
        return new DeleteFile($this->test_mode);
    }
}

class PermissionLoop extends FilesLoopAbstract {

    /**
     * String of selected permission flag. Need for transfer it to action class.
     * 
     * @access public
     * 
     * @var string $flag_str
     */
    public $flag_str = '';

    /**
     * Implements the required method. Returns an object of the action class.
     *
     * @since 1.0.0
     *
     * @access public
     * @return object 
     */     
    public function getAction(): \FileChanger {
        return new UpdatePermission($this->test_mode,$this->flag_str);
    }
}