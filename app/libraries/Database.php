<?php
  /*
   * PDO Database Class
   * Use MysqliDb library by Josh Cam
   * Connect to database
   * Create prepared statements
   * Bind values
   * Return rows and results
   */
  class Database extends MysqliDb{
    /**
     * Summary of $host
     * @var string
     */
    private string $host   =   DB_HOST;
    /**
     * @var string
     */
    private string $user   =   DB_USER;
    /**
     * @var string
     */
    private string $pass   =   DB_PASS;
    /**
     * @var string
     */
    private string $dbname =   DB_NAME;

    /**
     * Static instance of self
     * @var ?MysqliDb
     */
    protected static ?MysqliDb $dbh = null;

      /**
       */
    public function __construct(){
      parent::__construct($this->host, $this->user, $this->pass, $this->dbname);
    }

    /**
     * Static instance of self
     * @return MysqliDb
     */
    public static function getDbh(): MysqliDb
    {
      if(is_null(self::$dbh)) {
         self::$dbh = new MysqliDb(DB_HOST, DB_USER, DB_PASS, DB_NAME, port: DB_PORT);
      }
      return self::$dbh;
    }
  }
