<?php
/**
* Sorter
*
* Sortes files to multiple folders
*
* @package      PHP Library
* @subpackage   phplibrary
* @category     Sort
* @author       Zlatan Stajić <contact@zlatanstajic.com>
*/
namespace phplibrary;

/**
* Sortes files to multiple folders
*/
class Sorter {
    /**
    * Sorter class report
    * 
    * @var Array
    */
    protected $report   = array(
        'folders' => array(
            'number' => array(
                'created'     => 0,
                'not_created' => 0,
            ),
            'report' => array(
                'created'     => array(),
                'not_created' => array(),
            ),
        ),
        'files' => array(
            'number' => array(
                'copied'     => 0,
                'not_copied' => 0,
                'moved'      => 0,
                'not_moved'  => 0,
            ),
            'report' => array(
                'copied'     => array(),
                'not_copied' => array(),
                'moved'      => array(),
                'not_moved'  => array(),
            ),
        ),
    );
    
    // -------------------------------------------------------------------------
    
    /**
    * Deploy values
    * 
    * @var Array
    */
    protected $deploy = array(
        'where_to_read_files'           => '',
        'where_to_create_directories'   => '',
        'number_of_directories'         => 0,
        'folder_suffix'                 => '',
        'operation'                     => '',
        'types'                         => array(),
    );
    
    // -------------------------------------------------------------------------
    
    /**
    * PHP settings
    * 
    * @var Array
    */
    protected $settings = array(
        'max_execution_time' => 3600,
    );
    
    // -------------------------------------------------------------------------
    
    /**
    * Class constructor
    * 
    * @param Array $params
    * 
    * @return void
    */
    public function __construct($params=array())
    {
        empty($params) ? NULL : $this->settings = $params;
        
        ini_set('max_execution_time', $this->settings['max_execution_time']);
    }
    
    // -------------------------------------------------------------------------
    
    /**
    * Deploy sorting process 
    * 
    * @param Array $params
    * 
    * @return Array
    */
    public function deploy($params)
    {
        $this->deploy = $params;
        
        $this->create_directories();
        $this->transport_files($this->get_files(), $this->deploy['operation']);
        
        return $this->report();
    }
    
    // -------------------------------------------------------------------------
    
    /**
    * Crawl for files
    * 
    * @return Array
    */
    protected function get_files()
    {
        $arr_files = array();
        
        if (file_exists($this->deploy['where_to_read_files']))
        {
            $files              = scandir($this->deploy['where_to_read_files']);
            $number_of_files    = 0;
            $counter            = 1;
            
            foreach ($files as $file)
            {
                if ($counter > 2)
                {
                    if (stripos($file, '.'))
                    {
                        $extension         = pathinfo($file, PATHINFO_EXTENSION);
                        $extension_lowered = strtolower($extension);
                        
                        if (empty($this->deploy['types']) || in_array($extension_lowered, $this->deploy['types']))
                        {
                            array_push($arr_files, array(
                                'path'      => $this->deploy['where_to_read_files'] . $file,
                                'directory' => $this->deploy['where_to_read_files'],
                                'file'      => $file,
                                'title'     => basename($file, '.' . $extension),
                            ));
                            
                            $number_of_files += 1;
                        }
                    }
                }
                
                $counter++;
            }
        }
        
        return $arr_files;
    }
    
    // -------------------------------------------------------------------------

    /**
    * Create directories
    * 
    * @return void
    */
    protected function create_directories()
    {
        for ($i=0; $i<$this->deploy['number_of_directories']; $i++)
        {
            $folder = $this->folder_name($i);
            
            if ( ! file_exists($folder))
            {
                if (@mkdir($folder))
                {
                    $this->report['folders']['number']['created']++;
                    array_push($this->report['folders']['report']['created'], $folder);
                }
                else
                {
                    $this->report['folders']['number']['not_created']++;
                    array_push($this->report['folders']['report']['not_created'], $folder);
                }
            }
        }
    }
    
    // -------------------------------------------------------------------------

    /**
    * Transport files to created directories
    * 
    * @param Array $files
    * @param String $operation
    * 
    * @return void
    */
    protected function transport_files($files, $operation)
    {
        if ( ! empty($files))
        {
            foreach ($files as $item)
            {
                $location_from  = $this->deploy['where_to_read_files'];
                $location_from .= $item['file'];
                
                $location_to    = $this->deploy['where_to_create_directories'];
                $location_to   .= substr($item['file'], 0, 3);
                $location_to   .= $this->deploy['folder_sufix'];
                $location_to   .= '/';
                $location_to   .= $item['file'];
                
                if ( ! file_exists($location_to))
                {
                    switch ($operation)
                    {
                        case 'm': $this->move_files($location_from, $location_to, $item['file']); break;
                        default: $this->copy_files($location_from, $location_to, $item['file']);
                    }
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    
    /**
    * Information about sorting process
    * 
    * @return Array
    */
    protected function report()
    {
        $report  = 'Folders created/not created: ';
        $report .= $this->report['folders']['number']['created'];
        $report .= '/';
        $report .= $this->report['folders']['number']['not_created'];
        $report .= '<br/>';
        $report .= 'Files copied/not copied: ';
        $report .= $this->report['files']['number']['copied'];
        $report .= '/';
        $report .= $this->report['files']['number']['not_copied'];
        $report .= '<br/>';
        $report .= 'Files moved/not moved: ';
        $report .= $this->report['files']['number']['moved'];
        $report .= '/';
        $report .= $this->report['files']['number']['not_moved'];
        $report .= '<br/>';

        return array(
            'string' => $report,
            'array'  => array(
                'usage'  => getrusage(),
                'result' => $this->report,
            )
        );
    }
    
    // -------------------------------------------------------------------------
    
    /**
    * Folder name
    * 
    * @param int $i
    * 
    * @return String
    */
    private function folder_name($i)
    {
        switch (strlen($i))
        {
            case 1:
                {
                    $folder_prefix = '00' . $i;
                } break;
            case 2:
                {
                    $folder_prefix = '0' . $i;
                } break;
            case 3:
                {
                    $folder_prefix = $i;
                } break;
            default: $folder_prefix = '';
        }
        
        return $this->deploy['where_to_create_directories'] . $folder_prefix . $this->deploy['folder_sufix'];
    }
    
    // -------------------------------------------------------------------------
    
    /**
    * Copy files from one location to another
    * 
    * @param String $location_from
    * @param String $location_to
    * @param String $file
    * 
    * @return void
    */
    private function copy_files($location_from, $location_to, $file)
    {
        if (copy($location_from, $location_to))
        {
            $this->report['files']['number']['copied']++;
            array_push($this->report['files']['report']['copied'], $file);
        }
        else
        {
            $this->report['files']['number']['not_copied']++;
            array_push($this->report['files']['report']['not_copied'], $file);
        }
    }
    
    // -------------------------------------------------------------------------
    
    /**
    * Move files from one location to another
    * 
    * @param String $location_from
    * @param String $location_to
    * @param String $file
    * 
    * @return void
    */
    private function move_files($location_from, $location_to, $file)
    {
        if (rename($location_from, $location_to))
        {
            $this->report['files']['number']['moved']++;
            array_push($this->report['files']['report']['moved'], $file);
        }
        else
        {
            $this->report['files']['number']['not_moved']++;
            array_push($this->report['files']['report']['not_moved'], $file);
        }
    }
    
    // -------------------------------------------------------------------------
}
?>