<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 9/15/13
 * Time: 7:05 AM
 * To change this template use File | Settings | File Templates.
 *
 * Core of imgBB
 */

class ibbCore {
	/**
	 * @var ibbDBCore
	 */
	public			$DB;
    public	 		$settings;
    public			$tpl;
	public		 	$request;
	public static 	$instance;

	//TODO ALPHA revise to use singleton, phase out magic method __construct()
	/**
	 * initialize properties
	 */
	public function __construct()
    {
		$this->DB = new ibbDBCore;
		$this->DB->init();
		//TODO IMGBB create settings object
//		$this->settings = new ibbSettings;
		//TODO IMGBB create template handler
//		$this->tpl = new ibbTemplateHandler;
		if ( !self::$instance )
		{
			self::$instance = $this;
		}
	}

	/**
	 * Singleton
	 *
	 * @return ibbCore
	 */
	public static function getInstance()
	{
		if ( !self::$instance )
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function run()
	{
        /* Calculate path: where do we need to go according to these GETs? */
        $this->determinePath();

		//TODO IMGBB create global app controller && revise class into singleton
		/* Build for us the member */
		//TODO BASIC create user object
//		$memberData = Member::init();

		/* Does the user have access? */
		//TODO BASIC create user permissions
//		$memberData->checkPrivilege($member, self::$request);

		/* OK, let's pass this to the class they wanted. */
		//TODO BASIC create proper application loader
		try {
			ClassHandler::loadClass($this->request);
		} catch (Exception $e) {
			throw new Exception($e);
		}

	}

		// TODO IMGBB request handler
	public function determinePath()
	{
		$app = $_GET['app'] ? $_GET['app'] : 'main';

		try {
			$handle = opendir("app/$app");
			if (!$handle)
				throw new Exception( "Could not find $app" );

			closedir($handle);
		} catch (Exception $e) {
			throw new Exception ($e);
		}

		//TODO BASIC add other important info to $request
		$this->request = $app;

	}
}

/**
 * Class ClassHandler
 *
 * class handle, in its very primitive form
 */
class ClassHandler {

	public 			$request;
	/**
	 * @var $method $_get value
	 * @var $area 	$_get value
	 * @var $action	$_get value
	 */
	public static 	$method;
	public static 	$area;
	public static 	$action;
	/**
	 * @var ibbCore
	 */
	public static	$core;

	/**
	 * @param $app Main
	 *
	 * @throws Exception
	 */
	public static function loadClass($app)
	{
		/* Let's sort it all nicely */
		self::$method = $_GET['mod'];
		self::$area = $_GET['area'];
		self::$action = $_GET['action'];
		self::$core = ibbCore::getInstance();

		if ( !include_once IBB_ROOT_PATH . "/app/$app/$app.core.php" )
		{
			throw new Exception( 'Failed to load class!' );
		}

		/** Boot up an instance of our app */
		$handle = new $app;

		/* Fetch us the variables from the template */
//		echo 'class handler<br />';

		$tvars = $handle->templateVars();

		$tpl = new PHPTAL($tvars['path']);

		unset($tvars['path']);

		foreach ($tvars as $key => $var)
			$tpl->$key = $var;
		$tpl->echoExecute();

	}
}

/**
 * class ibbDBCore
 *
 * DB class, accessed through $DB through the IBB Core.
 */
class ibbDBCore /*implements ibbDBCoreInterface */ {

	/**
	 * Populated upon using query()
	 *
	 * @var array
	 */
	public	$results;

	/**
	 * @var array
	 */
	public 	$query;

	/**
	 * mysqli instance
	 *
	 * @var mysqli
	 */
	public $instance;

	/**
	 * @var
	 */
	private $data;

	public function init()
	{
		if ( !$this->instance )
			$this->instance = new mysqli('localhost', 'root', '', 'zildjohn01');

		if ($this->instance->connect_errno) {
			echo "Failed to connect to MySQL: (" . $this->instance->connect_errno . ") " . $this->instance->connect_error;
		}

		return $this->instance;
	}

	/**
	 * @param 	$query 	array
	 *
	 * @throws Exception
	 */
	public function execute( $query )
	{
//		if (!is_array($query)) {
//			throw new Exception('DB Query doesn\'t follow format');
//		}
		$this->instance->real_query( $query );
	}

	/**
	 * @param 	$qname 	string 	retrieval key
	 * @param 	$query 	array 	sql query
	 *
	 * @throws Exception
	 */
	public function query( $qname, $query )
	{
		$this->execute($query);
		$this->results[$qname] = $this->instance->use_result()->fetch_all(MYSQL_ASSOC);
	}

	/**
	 * returns mysqli->host_info
	 *
	 * @return string
	 */
	public function getHostInfo()
	{
		return $this->instance->host_info;
	}

}

/**
 * Class Boards
 *
 * Boards handle
 */
class Boards {
    /**
     * Contains all board names and their configurations.
     *
     * @var array
     */
    private $boards;

    /**
     * Contains all board categories.
     *
     * @var array
     */
    private $categories;

    /**
     * Returns an array with all board categories.
     *
     * This function returns an array, with normal numerical keys, which
     * contains all the SELECTed categories. This requires an argument containing
     * a query on the boards table, with a LEFT JOIN on the entire categories table.
     * This function exists merely so that only one query needs to be run.
     * It may still be deleted for its redundant nature.
     *
     * @param $results array
     * @return array
     */
    static function returnBoardCategories( $results )
    {
        $ret = array();

        foreach ( $results as $board )
        {
            if ( !in_array( $board['category'], $ret ))
                $ret[] = $board['category'];
        }

        return $ret;
    }

    static function generateBoardPage( $board, $page = NULL, $post = NULL )
    {
        global $tc_db;

        $boardresults = $tc_db->GetAll('SHOW tables');

        $tpl = TemplateHandler::init(IBB_TEMPLATES_PATH . '/board_index.xhtml');
        $tpl->board = 'lel';

//        try {
//            $tpl->echoExecute();
//        } catch (Exception $e) {
//            echo $e;
//        }
    }

}

/**
 * Class TemplateHandler
 *
 * awaiting construction
 */
class TemplateHandler {

    public static function init($path) {
        return new PHPTAL ( $path );
    }

    public static function executeTPL( $tpl )
    {
        if ( array_key_exists( $tpl, 'path' ))
        {
            $phptal = new PHPTAL( $tpl['path'] );
        }
    }
}