<?php
/**
 * Database interface
 * @author Alexander Pak <Alexander.Pak@cer.uz>
 */

/**
 * Database class
 */

abstract class Database extends Object
{
    protected $link; //link to mysql database

    protected $query;  //to keep query
    protected $result; //to keep query result
    protected $cache;  //cached results

    protected $history; //list of all queries executed
    protected $errors;  //list of occured errors
    
    protected $mode;

    /*
        debug mode = ABCD
        A - stop when error
        B - output error
        C - log errors
        D - use cache
    */
    const MODE_DEV  = '0111'; //dev mode
    const MODE_LIVE = '0011'; //live mode
    const MODE_SAFE = '1011'; //safe mode

    const TRIGGER_EXIT_ON_ERROR = 0;
    const TRIGGER_OUTPUT_ERRORS = 1;
    const TRIGGER_LOG_ERRORS    = 2;
    const TRIGGER_USE_CACHE     = 3;

    const PATTERN_FIELDS = 'fields';
    const PATTERN_ARRAY  = 'array';

    const DB_TYPE_MYSQL = 'mysql';
    const DB_TYPE_POSTGRE = 'postgre';


    static public function factory($type, $mode)
    {
        switch ($type) {
        case self::DB_TYPE_MYSQL:
            return new Database_Mysql($mode);

        case self::DB_TYPE_POSTGRE:
            return new Database_Postgre($mode);
            
        default:
            return false;
        }        
    }
    
    static public function plug_n_play($type, $mode, ArrayRecursiveObject $config)
    {
        $db = Database::factory($type, $mode);
        $db->connect($config);
        return $db;
    }
        
    public function __construct($mode)
    {
        $this->mode = $mode;
        
        //if (debug mode string is longer than necessary) then { cut it }
        $this->mode = substr($this->mode, 0, 4);
        //if (debug mode string is shorter than necessary) then { pad it with zeroes }
        $this->mode = str_pad($this->mode, 4, '0', STR_PAD_LEFT);

        if ($error = $this->last_error()) {
            $this->error($error);
        }

        $this->query   = '';
        $this->result  = array();
        $this->cache   = array();

        $this->history = array();
        $this->errors  = array();
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {
        //mysql_close($this->link);
    }
    
    abstract public function connect(ArrayRecursiveObject $config);

    abstract public function seek($result, $row);
    
    abstract public function fetch_assoc($result);

    abstract public function fetch_array($result);
    
    /**
     * Retrieve number of rows in query result
     *
     * @return unknown
     */
    abstract public function num_rows();

    /**
     * Retrieve last inserted ID
     *
     * @return int
     */
    abstract public function insert_id();

    /**
     * Retrieve rows number affected by last query
     *
     * @return array
     */
    abstract public function affected_rows();

    /**
     * List table fields
     *
     * @return array
     */
    abstract public function list_fields($table);
    
    /**
     * Output error
     *
     * @param string $message
     */
    protected function error($message)
    {
        $this->Error->handle(1, $message);
    }

    public function prepare($query)
    {
        if ($query instanceof SQL_Query) {
            $query = $query->__toString();
        }
        if (strlen($query)) {
            $args = func_get_args();
            if (count($args) > 1) {
                $this->query = call_user_func_array(array('SQL', 'quote'), $args);
            } else {
                $this->query = $query;
            }
        } else {
            throw new Exception('Empty query');
        }
    }

    /**
     * Send SQL query
     *
     * @return bool
     */
    public function query($query = '', $caching = true)
    {
        if (strlen($query)) {
            $args = func_get_args();
            call_user_func_array(array(&$this, 'prepare'), $args);
        }

        $qid = md5($this->query);
        if (preg_match('/^select\s/i', trim($query)) && $caching && isset($this->cache[$qid]) && $this->mode[self::TRIGGER_USE_CACHE] && is_resource($this->cache[$qid])) {
            //if query is cached use the cached result
            array_push($this->result, $this->cache[$qid]);
            if (!$this->seek(end($this->result), 0)) {
                //$this->error($this->query); // this gives error when doing update after insert for setCommon()
                return $this->query(null, false);
            }

            //save query in history
            array_push($this->history,
                array(
                    'query'  => $this->query,
                    'time'   => 0,
                    'errno'  => 0,
                    'error'  => '',
                    'source' => 'cache',
                )
            );
        } else {
            //record time for query execution
            $start  = getmicrotime();
            $result = $this->execute($this->query);
            $end    = getmicrotime();

            $errno = $this->last_errno();
            $error = $this->last_error();

            //save query in history
//            array_push($this->history,
//                array(
//                    'query'  => $this->query,
//                    'time'   => $end - $start,
//                    'errno'  => $errno,
//                    'error'  => $error,
//                    'source' => 'database',
//                    'result' => $result
//                )
//            );


            //in case of error
            if (!$errno && !strlen($error)) {
                if (is_resource($result)) {
                    $this->cache[$qid] = $result;
                    array_push($this->result, $result);
                }
            } else {

                if ($this->mode[self::TRIGGER_OUTPUT_ERRORS]) {
                    //output error
                    $this->error('Error ' . $errno . ': ' . $error . '<br />' . describe($this->query));
                }

                if ($this->mode[self::TRIGGER_LOG_ERRORS]) {
                    //log error
                    array_push($this->errors,
                        array(
                            'no'    => $errno,
                            'text'  => $error,
                            'query' => $this->query,
                        )
                    );
                    $this->Error->log($error);
                }

                if ($this->mode[self::TRIGGER_EXIT_ON_ERROR]) {
                    //exit script
                    exit;
                }

                return false;
            }

        }
        return true;
    }

    public function free_result()
    {
        $result = array_pop($this->result);
        //return mysql_free_result($result);
    }

    /**
     * Execute query and return single value
     *
     * @param string $query
     * @return mixed
     */
    public function value($query = '')
    {
        if ($query instanceof SQL_Query) {
            $query = $query->__toString();
        }
        if (strlen($query)) {
            $args = func_get_args();
            call_user_func_array(array(&$this, 'query'), $args);
        }
        $data = $this->fetch_array(end($this->result));
        $this->free_result();
        return $data[0];
    }

    /**
     * Fetch result
     *
     * @param string $field
     * @return array
     */
    public function fetch($field = null)
    {
        $data = $this->fetch_assoc(end($this->result));
        if ($data === false) {
            $this->free_result();
        }
        if (isset($data[$field])) {
            return $data[$field];
        } else {
            return $data;
        }
    }

    public function fetch_all()
    {
        $rows = array();
        while ($row = $this->fetch()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Turn cache on
     *
     */
    public function is_cache_on()
    {
        return $this->mode[self::TRIGGER_USE_CACHE];
    }

    /**
     * Turn cache on
     *
     */
    public function cache_on()
    {
        $this->mode[self::TRIGGER_USE_CACHE] = 1;
    }

    /**
     * Turn cache off
     *
     */
    public function cache_off()
    {
        $this->mode[self::TRIGGER_USE_CACHE] = 0;
    }

    public function report($show_queries = false)
    {
        $report = '';

        $length = 20;
        $max_length = 120;
        $max_line_length = 0;
        $total = 0;
        $lines = array();
        $count = 0;
        foreach ($this->history as $log) {
            $total += $log['time'];
            if ($log['source'] == 'database') {
                $log['time'] = round($log['time'], 5);
                $count ++;
            } else {
                $log['time'] = '-';
            }
            $line = "{$log['query']}::: {$log['time']} sec.";
            $lines[] = $line;
            if ($max_line_length < strlen($line)) {
                $max_line_length = strlen($line);
            }
        }
        $max_length = min($max_length, $max_line_length);

        foreach ($lines as &$line) {
            if (strlen($line) > $max_length) {
                $line = wordwrap($line, $max_length - strlen("\n  "), "\n  ");
                $last = substr(strrchr($line, "\n"), 1);
                $line = str_replace(':::', str_pad(': ', $max_length - strlen($last) + $length + strlen("::\n"), '.'), $line);
            } else {
                $line = str_replace(':::', str_pad(': ', $max_length - strlen($line) + $length + strlen("::\n"), '.'), $line);
            }
        }

        if ($show_queries) {
            $report = implode("\n", $lines);
        }
        $report .= str_pad("\n", $max_length + $length, '=');
        $total = round($total, 5);
        $total = "Total: $count queries took $total sec.";
        $report .= str_replace(':', str_pad(': ', $max_length - strlen($total) + $length + strlen("\n"), '.'), "\n" . $total);
        return $report;
    }
    
    public function getQueries() {
        $count = 0;
        foreach ($this->history as $log) {
            if ($log['source'] == 'database') {
                $count ++;
            }
        }
        return $count;
    }
    
    public function clearHistory()
    {
        $this->history = array();
    }
}
?>