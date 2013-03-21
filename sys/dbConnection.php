<?php

class dbConnection {

    protected static     $instance;

    protected $type =       'mysql';
    protected $server =     '127.0.0.1';
    protected $user =       'root';
    protected $password =   'root';
    protected $database =   'db_tree';
    protected $table =      'comments';


/*
 * Method for get and initiate PDO instance
 *
 * @return PDO res
 */
    protected function __construct()
    {
        if(!isset(self::$instance))
        {

            try
            {
                
                self::$instance = new PDO($this->type.':host=' . "$this->server" . ';dbname='. $this->database,
                                                                                        $this->user, $this->password);

            }
            catch(PDOException $e)
            {
                echo 'Connection failed: ' . $e->getMessage();
            }
        }
        return self::$instance;
    }

/*
 * To avoid copies
 */
    private function __clone() {}

    private function __wakeup() {}


    public function trans_commit() {
        return self::$instance->commit();
    }

    public function trans_begin() {
        return self::$instance->beginTransaction();
    }

    public function trans_roll() {
        return self::$instance->rollBack();
    }

    public function exec($query)
    {
        return self::$instance->exec($query);
    }

    public function execute($query)
    {
        return self::$instance->execute($query);
    }

    public function query($query)
    {
        return self::$instance->query($query);
    }

    public function lastInsertId($seqname)
    {
        return self::$instance->lastInsertId($seqname);
    }

    public function prepare($statement)
    {
        return self::$instance->prepare($statement);
    }

    public function errorInfo()
    {
        return self::$instance->errorInfo();
    }

    public function errorCode()
    {
        return self::$instance->errorCode();
    }

}
