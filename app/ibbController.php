<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 9/15/13
 * Time: 7:05 AM
 * To change this template use File | Settings | File Templates.
 *
 * All logic files are right now inside this php file to reduce require_once clutter
 * as we're in BASIC stage, the object loaders do not exist yet
 */

/**
 * Class ibbController
 *
 * TODO Needs a LOT more work and a lot of other code needs to be moved here
 */
class ibbController {

	/**
	 * @var ibbController
	 */
	public static 	$instance;

	/**
	 * @var $handles 	ibbDBCore
	 * @var $settings	array
	 * @var $request	array
	 * @var $tpl		idkyet
	 */
	public static	$handles;
	public			$settings;
	public static 	$request;
	public			$tpl;

	/**
	 * @var $core	ibbCore
	 */
	public			$core;
	public static	$fURL;

	/**
	 * Singleton
	 *
	 * @return ibbController
	 */
	public static function getInstance()
	{
		if ( !self::$instance )
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * boot
	 */
	public static function run()
	{
		$me = new ibbController;
		$me->init();
		$me->handleRequest();
	}

	/**
	 * initialize
	 */
	public function init()
	{
		$this->core = ibbCore::getInstance();
		$this->core->run();
	}

	/**
	 * This is missing so much, but I suppose that's why it's BASIC
	 *
	 * @throws Exception
	 */
	public function handleRequest()
	{

		try {
			ClassHandler::Execute( $this->core->request('app'));
		} catch (Exception $e) {
			throw new Exception($e);
		}

	}
}

/**
 * Class ibbCore
 */
class ibbCore {

	private static	$instance;

	private static	$initiated;

	private static	$handles;

	private static	$fURL;

	public static	$request;

	public static 	$fURLArray;

	// temporarily static until settings configuration are made @ IMGBB state
	public static	$settings = array(
		'base_url' => 'http://www.imgbb.net/imgbb'
	);

	/**
	 * @var output
	 */
	public		 	$output;


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

	/**
	 * @return bool
	 */
	public static function run()
	{
		if ( self::$initiated === TRUE )
		{
			return FALSE;
		}

		/* Handles */
			// init DB
			self::$handles['db'] = ibbDBCore::getInstance();

			//init output
			self::getInstance()->output = output::getInstance();

		/* First check if it's a fURL */
		self::verifyfURL();

		/* Calculate path: where do we need to go according to these GETs? */
		self::determinePath();



		/* Populate Settings */


		//TODO IMGBB create global app controller && revise class into singleton
		/* Build for us the member */
		//TODO BASIC create user object
//		$memberData = Member::init(self::$instance);

		/* Does the user have access? */
		//TODO BASIC create user permissions
//		$memberData->checkPrivilege(self::$request);

		/* OK, let's pass this to the class they wanted. */
		//TODO BASIC create proper application loader
	}

	// TODO IMGBB request handler
	public function determinePath()
	{
		try
		{
			// Does the application physically exist?
			if ( !$handle = is_dir( "app/" . $_GET['app'] ) )
			{
				throw new Exception( "Could not find application " . $_GET['app'] );
			}

		} catch (Exception $e)
		{
			// Throw default output
			throw new Exception ($e);
		}

		//TODO IMGBB rework
		if ( self::$fURL )
		{
			foreach ( self::$fURLArray as $key => $value )
			{
				self::$request[$key] = self::DB()->instance()->real_escape_string( $value );
			}
		}
		else if ( isset($_GET['app'] ) )
		{
			foreach ($_GET as $key => $value)
			{
				self::$request[$key] = self::DB()->instance()->real_escape_string( $value );
			}
		}
		else
		{
			self::$request['app'] = 'main';
		}
	}

	/**
	 * Check for fURL
	 */
	public function verifyfURL()
	{
		/* Only one key since there aren't any ampersands in a fURL, so let's just autofetch it with key() */
		if (preg_match('#/(.*)?/(\d*)?#', key( $_REQUEST )))
		{
			/* Here we make sure to tell the rest of the code that the user used fURL */
			self::$fURL = TRUE;

			/* Now it's going to send it to a function that loops over it until it finds the correct array. */
			basicFriendlyURL::findpath( key( $_REQUEST ) );
		}
	}

	/**
	 * TODO ALPHA use this function to send the appropriate DB handle
	 * Fetch instance
	 *
	 * @return ibbDBCore
	 */
	public function DB()
	{
		return self::$handles['db'];
	}

	/**
	 * Fetch request
	 *
	 * @param string $key
	 * @return string
	 */
	public function request($key = NULL)
	{
		if (!$key)
			return self::$request;
		elseif (self::$request[$key])
			return self::$request[$key];
		else
			return false;
	}

	/**
	 * Get settings
	 *
	 * @param string|array $key
	 * @return array
	 */
	public function settings( $key = NULL )
	{
		if ( !$key )
			return self::$settings;
		else
			return self::$settings[$key];
	}

	/**
	 * temp debug hotfix
	 *
	 * @param $key
	 * @param $val
	 */
	public function hotfixAddRequest ( $key, $val )
	{
		self::$request[$key] = $val;
	}
}

/**
 * Class ClassHandler
 *
 * class handle, in its very primitive form
 * TODO use ion cannon and obliterate this mess and create handler in the ibbCore instead, maybe create ibbCommand?
 */
class ClassHandler {

	public 			$request;

	/**
	 * @var ibbController
	 */
	public static	$core;

	/**
	 * It's not REALLY execute, it WAS execute earlier in development though, now it also verifies
	 * ...
	 * hence need for rewrite
	 *
	 * @param string $app
	 * @throws Exception
	 */
	public static function Execute( $app )
	{
		$MODULE = '';
		$AREA = '';

		self::$core 	= 	ibbCore::getInstance();

		if ( $app == 'user' )
		{
			throw new Exception( "The application '$app' has been disabled on this installation." );
		}

		if ( !include_once IBB_ROOT_PATH . "/app/$app/modules/defaultMod.php" )
		{
			throw new Exception( 'Could not find default module!' );
		}

		if ( !include_once IBB_ROOT_PATH . "/app/$app/modules/$MODULE/defaultArea.php" )
		{
			throw new exception( 'Could not find default area!' );
		}

		if ( !include_once IBB_ROOT_PATH . "/app/$app/modules/$MODULE/$AREA.php" )
		{
			throw new Exception( 'Failed to load class!' );
		}

		// ew
		self::$core->hotfixAddRequest('mod', $MODULE);
		self::$core->hotfixAddRequest('area', $AREA);

		// safe from nasty attempts to load something the user SHOULDNT and that isn't protected
		// this line just looks dumb tbh, how to make it look more... professional?
		$bootup = $app . '_' . $MODULE . '_' . $AREA;

		/** Boot up an instance of our app */
		$handle = new $bootup;

		/* Set path... */
		self::$core->output->setWorkingTemplate( IBB_ROOT_PATH . '/app/'.$app.'/tpl/', 'default.xhtml');

		/* Initialize */
		// it's complaining that init wasn't found, I guess that's why I need an abstract class...
		$handle->init();

		/* Go go, power ranger */
		// temp name ofc...
		self::$core->output->goGoPowerRanger();

	}

	/**
	 * Load class
	 *
	 * @param $class
	 */
	public static function loadAPI($class)
	{
		if (!is_file(IBB_ROOT_PATH . "/app/$class/api.php"))
			throw new Exception('Application API not found ' . IBB_ROOT_PATH . "/app/$class/api.php");

		require_once IBB_ROOT_PATH . "/app/$class/api.php";
	}
}

/**
 * Class output
 *
 * Kinda happy with this, it needs more work sure, but it's sexy
 */
class output {

	private static $instance;

	/**
	 * @var string Title
	 */
	public	$title;

	/**
	 * @var string Path to template folder
	 */
	public	$path;

	/**
	 * @var string Exact file name
	 */
	public	$file;

	/**
	 * @var array All CSS files to be added to the header
	 */
	public $css = array();

	/**
	 * @var $tpl mixed I forgot what this is for?
	 */
	private $tpl;

	/**
	 * @var array PHPTAL variables
	 */
	public $vars = array();

	/**
	 * Don't think there's a need for this?
	 *
	 * @var array
	 */
	private $staticvars = array();

	/**
	 * @var ibbCore
	 */
	private $core;

	/**
	 * Macros to load
	 *
	 * @var array
	 */
	private $macro;

	/**
	 * Use default meta attributes?
	 *
	 * @var bool
	 */
	public $head = TRUE;

	/**
	 * Use the menu?
	 *
	 * @var bool
	 */
	public $menu = TRUE;

	/**
	 * Use the footer?
	 *
	 * @var bool
	 */
	public $footer = TRUE;

	/**
	 * Where's the starting XHTML file?
	 *
	 * @var string
	 */
	private $workingpath;

	/**
	 * Slots to use
	 *
	 * @var array
	 */
	private	$slots;

	/**
	 * Singleton
	 *
	 * @return output
	 */
	public static function getInstance()
	{
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	/**
	 *
	 */
	public function __construct()
	{
		$this->core = 	ibbCore::getInstance();
		$this->db 	=	$this->core->DB();
	}

	/**
	 * @param $title string
	 */
	public function setTitle( $title )
	{
		$this->title = $title;
	}

	/**
	 * @param $path string
	 */
	public function setPath( $path )
	{
		$this->path = $path;
	}

	/**
	 * @param $file string
	 */
	public function setFile( $file )
	{
		$this->file = $file;
	}

	/**
	 * Add a macro
	 *
	 * @param 	string	$macro
	 * @param 	string 	$template_src
	 */
	public function addMacro( $macro, $template_src = 'default.xhtml' )
	{
		$this->macro = array('name'	=> 	$macro,
							 'src' 	=> 	is_file($this->path.$template_src) ? $this->path.$template_src : $template_src);
	}

	/**
	 * Add as lot
	 *
	 * @param $slot string
	 * @param $macro string
	 */
	public function addSlot( $slot, $macro )
	{
		$this->slots[] = array (	'name'	=>	$slot,
									'src'	=>	$macro );
	}

	/**
	 * Set the working template
	 *
	 * @param $path string
	 * @param $file string
	 */
	public function setWorkingTemplate( $path, $file )
	{
		$this->setPath( $path );
		$this->setFile( $file );
		$this->workingpath = $path . $file;
	}

	/**
	 * Could use some more work? Seems... weird
	 *
	 * @param $val string add CSS file
	 */
	public function addCSS($filename)
	{
		$this->vars['imgbb']['css'][] = $filename;
		$this->css = TRUE;
	}

	/**
	 * Mostly used to take stuff from app Main
	 *
	 * @param $filename
	 * @param $app
	 */
	public function addXAppCSS($filename, $app)
	{
		$this->vars['imgbb']['css'][] = array(
											'filename' 	=> $filename,
											'app'		=> $app
		);
	}

	/**
	 * I NEVER EVEN WATCHED POWER RANGERS
	 *
	 * PHPTAL Execute
	 */
	public function goGoPowerRanger()
	{
		/* Initialize PHPTAL */
		$this->tpl = new PHPTAL( $this->workingpath );

		/* Set up head	*/
		if ($this->menu)
		{
			$this->addXAppCSS('menu.css', 'main');
		}

		/* Set up static variables. */
		$this->vars['imgbb']['base_url'] 		= $this->core->settings('base_url');
		$this->vars['imgbb']['this_app']		= $this->core->request('app');
		$this->vars['imgbb']['macro']			= $this->macro;
		$this->vars['imgbb']['slots']			= $this->slots;
		$this->vars['imgbb']['tplpath']			= $this->path;
		$this->vars['imgbb']['title']			= $this->title;
		$this->vars['imgbb']['highlight']		= array ( 	'app' 		=> $this->core->request('app'),
													  		'mod' 		=> $this->core->request('mod'),
													  		'area' 		=> $this->core->request('area'),
													  		'action' 	=> $this->core->request('action')
											  	);
		$this->vars['imgbb']['IBB_TEMPLATES_PATH']	= IBB_TEMPLATES_PATH;
		$this->vars['imgbb']['menubar']		= $this->menu;


		/* Give PHPTAL our variables */
		foreach ( $this->vars as $key => $value )
		{
			$this->tpl->$key = $value;
		}

		/* Set up booleans */
		//TODO IMGBB set up our own template engine
//		if ( $this->head )
//		{
//			$this-
//		}

		/* All fired up, baby, OUR IMAGEBOARD IS WORKING */
		$this->tpl->echoExecute();

	}

}

/**
 * Class Member
 *
 * dead class, needs a lot of rethinking
 */
class Member {

	private 		$core;
	private 		$db;
	private static 	$instance;
	private			$data;
	private 		$permissions;

	/**
	 * @return Member
	 */
	public function init()
	{
//		$this->db->query('permissions', '')
	}

	/**
	 * Constructor
	 */
	public function __construct(ibbCore $ibbc)
	{
		$this->core = $ibbc;
		$this->db	= $this->core->DB();
	}

	/**
	 * @param $req		Object
	 */
	public function checkPrivilege($req)
	{
		$this->db->query('privs', 'SELECT ');

		if (is_array($this->db->results))
			return true;
		else
			return false;
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
	public	$results = FALSE;

	/**
	 * @var array
	 */
	public 	$query;

	/**
	 * @var ibbDBCore
	 */
	public static	$instance;

	/**
	 * @var mysqli
	 */
	public static	$dbinstance;

	/**
	 * @var
	 */
	private $data;

	/**
	 * @return ibbDBCore
	 */
	public static function getInstance()
	{
		if ( !self::$instance )
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * @return mysqli
	 */
	public static function instance()
	{
		if ( !self::$dbinstance )
			self::$dbinstance = new mysqli('localhost', 'root', '', 'zildjohn01');

		if ( self::$dbinstance->connect_errno) {
			echo "Failed to connect to MySQL: (" . self::$dbinstance->connect_errno . ") " . self::$dbinstance->connect_error;
		}

		return self::$dbinstance;
	}

	/**
	 * @param	$query	array
	 *
	 * @throws	Exception
	 */
	public function execute( $query )
	{
//		if (!is_array($query)) {
//			throw new Exception('DB Query doesn\'t follow format');
//		}
		$this->instance()->real_query( $query );
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
		$this->results[$qname] = $this->instance()->use_result()->fetch_all(MYSQL_ASSOC);
	}

	/**
	 * temp
	 *
	 * @param $qname
	 * @param $query
	 */
	public function queryInLoop( $qname, $parent, $query )
	{
		$this->execute($query);
		$this->results[$qname][$parent][] = $this->instance()->use_result()->fetch_all(MYSQL_ASSOC);
	}

	/**
	 * This is used for queries against the database that fetch only one row. This function does not include
	 * an array filled with each row, so you can access the row's columns directly without needing to add
	 * annoying [0]s every time.
	 *
	 * @param $qname
	 * @param $query
	 */
	public function singleResultQuery ( $qname, $query )
	{
		$this->execute( $query );
		// dat [0]... there must be some function or constant that can be passed as an option to stop double array...
		$this->results[$qname] = $this->instance()->use_result()->fetch_all(MYSQL_ASSOC)[0];
	}

	/**
	 * returns mysqli->host_info
	 *
	 * @return string
	 */
	public function getHostInfo()
	{
		return $this->instance()->host_info;
	}

	public function close()
	{
		$this->instance()->close();
	}

}