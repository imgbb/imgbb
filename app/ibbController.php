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
	 * @var appCore $handle
	 */
	public			$handle;

	/**
	 * @var $core	ibbCore
	 */
	public			$core;
	public static	$fURL;

	public $user_data;

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
		$this->core->output = output::getInstance();
	}

	/**
	 * This is missing so much, but I suppose that's why it's BASIC
	 *
	 * @throws Exception
	 */
	public function handleRequest()
	{
		$this->user_data = $this->core->user()->init();

		# temporarily putting this here while I figure out where to actually put this
		switch ($this->core->request('do'))
		{
			case 'process':
			{
				define('REQUEST_TYPE', 'process');
				break;
			}
			case 'ajax':
			{
				define('REQUEST_TYPE', 'ajax');
				break;
			}
			default:
				define('REQUEST_TYPE', 'init');
				break;
		}

		# same for this
		if (isset($_SESSION['user_name']))
		{
			$this->core->output->user = TRUE;
		}

		$MODULE = $this->core->request('mod');
		$AREA 	= $this->core->request('area');

		if ( !$MODULE )
		{
			if ( !include_once IBB_ROOT_PATH . "/app/" . $this->core->request('app'). "/modules/defaultMod.php" )
			{
				throw new Exception( 'Could not find default module!' );
			}
		}

		if ( !$AREA )
		{
			if ( !include_once IBB_ROOT_PATH . "/app/".$this->core->request('app')."/modules/".$MODULE."/defaultArea.php" )
			{
				throw new exception( 'Could not find default area!' );
			}
		}

		if ( !include_once IBB_ROOT_PATH . "/app/".$this->core->request('app')."/modules/$MODULE/$AREA.php" )
		{
			throw new Exception( 'Failed to load class!' );
		}

		# ew
		$this->core->hotfixAddRequest('mod', $MODULE);
		$this->core->hotfixAddRequest('area', $AREA);

		# safe from nasty attempts to load something the user SHOULDNT and that isn't protected
		# this line just looks dumb tbh, how to make it look more... professional?
		$bootup = $this->core->request('app') . '_' . $MODULE . '_' . $AREA;

		$this->handle = new $bootup;

		/* Set path... */
		$this->core->output->setMasterTemplate( IBB_ROOT_PATH . '/app/'.$this->core->request('app').'/tpl/', 'default.xhtml');

		if (REQUEST_TYPE == 'process')
		{
			$this->handle->process();

			if ($this->core->DB()->results['areadata']['onprocess_class'])
			{
				header('Location: ' . $this->core->settings('core_url')
									. BasicFriendlyURL::toFURL( $_POST['action'], $_POST['subaction'], $bootup ));
			}

		}
		else
		{
			$this->handle->init();
		}

		$this->core->output->goGoPowerRanger();

		# my dignity
		# please
		# don't look
		# want to keep my dignity
		# IT'S TEMPORARY BUT I NEED TO UPLOAD IT SORRY.

//		if (is_int(self::$core->request('action')))
//		{
//			self::$core->DB()->query('boardname', 'SELECT name FROM ibb_boards WHERE id='.self::$core->request('action'));
//			self::$core->hotfixAddRequest('action', self::$core->DB()->results['boardname'][0]['name']);
//		}
//
//		$subac = (self::$core->request('subaction') ? '&subaction='.self::$core->request('subaction') : '');
//
//		$url = 'app='.$app.'&mod='.$module.'&area='.$area.'&action='.self::$core->request('action') .
//			$subac;
//
//		# class to turn a url into a furl here
//		if (self::$core->DB()->results['areadata']['onprocess_class'] == 'boards_boardpage_view')
//		{
//			return 'index.php?/'.self::$core->request('action').'/'.
//			(self::$core->request('subaction') ? self::$core->request('subaction') : '');
//		}
//		elseif (self::$core->DB()->results['areadata']['onprocess_class'] == 'main_front_view')
//		{
//			return 'index.php';
//		}
//		# temp
//		return 'index.php?'.$url;
//	}
//
//
//		try {
//			ClassHandler::Execute( $this->core->request('app'), REQUEST_TYPE);
//		} catch (Exception $e) {
//			throw new Exception($e);
//		}

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

	public static	$User;

	public 			$data;

	public static	$queryc;

	# temporarily static until settings configuration are made @ IMGBB state
	public static	$settings = array(
		'base_url' 			=> 'http://www.imgbb.net/imgbb',
		'furls_rewrite' 	=> TRUE,
		'core_url'			=> 'http://www.imgbb.net/imgbb',
		'core_url_tr' => 'http://www.imgbb.net/imgbb/'
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
	 * @return void
	 */
	public static function run()
	{
		if ( self::$initiated === TRUE )
		{
			exit('initiated.');
		}

		/* Handles */
			/* init DB */
			self::$handles['db'] 		= ibbDBCore::getDBType();
			#temp
			self::$handles['upload'] 	= (new upload)->init();

		/* First check if it's a fURL */
		self::verifyfURL();

		/* Calculate path: where do we need to go according to these GETs? */
		self::determinePath();

		self::fetchModularData();





		/* Populate Settings */


		# TODO IMGBB create global app controller && revise class into singleton
		/* Build for us the member */
		/* Do we want this here, or in the Controller? */
		/* Not sure how or where to construct it */
		self::$User = new User;

		/* Does the user have access? */
		# TODO IMGBB create permissions check

		/* OK, let's pass this to the class they wanted. */
		# TODO IMGBB create proper application loader
	}

	# TODO IMGBB request handler
	# please don't look at this horrifying function, it's temporary
	# please
	public static function determinePath()
	{
		# please don't look, all temp, pls, my dignity is at stake, don't look

		# TODO IMGBB rework
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
		if ( isset($_POST) )
		{
			foreach ($_POST as $key => $value)
			{
				self::$request[$key] = self::DB()->instance()->real_escape_string( $value );
			}
		}

		# lord forgive me
		# it's BASIC, it's just basic
		# gotta stay calm
		# it's just BASIC
		# not final product
		# objective is to get it running
		# not to make it good
		# objective is to get it running for front-end
		# not to make a good back-end necessarily
		# that's imgbb level
		# oh adonai help me
		if (!isset(self::$request['app']))
			self::$request['app'] = 'main';
		if (!isset(self::$request['mod']))
			self::$request['mod'] = 'front';
		if (!isset(self::$request['area']))
			self::$request['area'] = 'view';

		try
		{
			#Does the application physically exist?
			if ( !$handle = is_dir( "app/" . self::$request['app'] . '/' ) )
			{
				throw new Exception( "Could not find application " . $_GET['app'] );
			}

		} catch (Exception $e)
		{
			#Throw default output
			throw new Exception ($e);
		}
	}

	/**
	 * Check for fURL
	 */
	public static function verifyfURL()
	{
		/* Only one key since there aren't any ampersands in a fURL, so let's just autofetch it with key() */
		if (preg_match('#/(.*)?/(\d*)?#', key( $_REQUEST )))
		{
			/* Here we make sure to tell the rest of the code that the user used fURL */
			self::$fURL = TRUE;

			/* Now it's going to send it to a function that loops over it until it finds the correct array. */
			BasicFriendlyURL::translateFURL( key( $_REQUEST ) );
		}
	}

	public static function fetchModularData()
	{
		self::DB()->query('appdata', '
			SELECT	*
			FROM	ibb_applications
			WHERE	name = "' . self::$request['app'] . '"
		', SINGLE_RESULT_QUERY);
		self::DB()->query('moddata', '
			SELECT	*
			FROM	ibb_modules
			WHERE	name = "' . self::$request['mod'] . '"
			AND		parentapp = '. self::DB()->results['appdata']['id'] .'
		', SINGLE_RESULT_QUERY);
		self::DB()->query('areadata', '
			SELECT	*
			FROM	ibb_areas
			WHERE	name = "' . self::$request['area'] . '"
			AND		parentmodule = '. self::DB()->results['moddata']['id'] .'
		', SINGLE_RESULT_QUERY);
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
		elseif (isset(self::$request[$key]))
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


	/**
	 * @return User
	 */
	public function user()
	{
		return self::$User;
	}

	/**
	 * @return upload
	 */
	public function upload()
	{
		return self::$handles['upload'];
	}
}

///**
// * Class ClassHandler
// *
// * class handle, in its very primitive form
// * TO/DO use ion cannon and obliterate this mess and create handler in the ibbCore instead, maybe create ibbCommand?
// *
// * TO/DO ENOUGH FIXING, WRITE THE NEW CLASS, THE MORE YOU BANDAGE IT THE HARDER IT WILL BE TO CREATE
// */
//class ClassHandler {
//
//	public 			$request;
//
//	/**
//	 * @var ibbCore
//	 */
//	public static	$core;
//
//	/**
//	 * @var appCore
//	 */
//	public static	$handle;
//
//	/**
//	 * It's not REALLY execute, it WAS execute earlier in development though, now it also verifies
//	 * ...
//	 * hence need for rewrite
//	 *
//	 * @param string $app
//	 * @param string $request_type
//	 *
//	 * @throws Exception
//	 */
//	public static function Execute( $app, $request_type )
//	{
//		#################################################
//		#################################################
//		##### PLEASE DON'T LOOK AT THIS ABOMINATION #####
//		#################################################
//		#################################################
//		self::$core = ibbCore::getInstance();
//
//		$MODULE = self::$core->request('mod');
//		$AREA = self::$core->request('area');
//
//		if ( !$MODULE )
//		{
//			if ( !include_once IBB_ROOT_PATH . "/app/$app/modules/defaultMod.php" )
//			{
//				throw new Exception( 'Could not find default module!' );
//			}
//		}
//
//		if ( !$AREA )
//		{
//			if ( !include_once IBB_ROOT_PATH . "/app/$app/modules/$MODULE/defaultArea.php" )
//			{
//				throw new exception( 'Could not find default area!' );
//			}
//		}
//
//		if ( !include_once IBB_ROOT_PATH . "/app/$app/modules/$MODULE/$AREA.php" )
//		{
//			throw new Exception( 'Failed to load class!' );
//		}
//
//		# ew
//		self::$core->hotfixAddRequest('mod', $MODULE);
//		self::$core->hotfixAddRequest('area', $AREA);
//
//		# safe from nasty attempts to load something the user SHOULDNT and that isn't protected
//		# this line just looks dumb tbh, how to make it look more... professional?
//		$bootup = $app . '_' . $MODULE . '_' . $AREA;
//
//		self::$handle = new $bootup;
//
//		/* Set path... */
//		self::$core->output->setMasterTemplate( IBB_ROOT_PATH . '/app/'.$app.'/tpl/', 'default.xhtml');
//
//		if ($request_type == 'process' || $request_type == 'ajax')
//		{
//			self::$handle->process();
//			if (self::$core->DB()->results['areadata']['onprocess_class'])
//			{
//				header('Location: ' . self::EvolutionizedExecution( self::$core->DB()->results['areadata']['onprocess_class'], 'init' ));
//			}
//
//		}
//		else
//		{
//			self::$handle->init();
//		}
//
//		self::$core->output->goGoPowerRanger();
//
//	}
//
//	/**
//	 * @param $class
//	 *
//	 * @return string
//	 */
//	public static function EvolutionizedExecution( $class )
//	{
//		list($app, $module, $area) = explode('_', $class);
//
//		# my dignity
//		# please
//		# don't look
//		# want to keep my dignity
//		# IT'S TEMPORARY BUT I NEED TO UPLOAD IT SORRY.
//
//		if (is_int(self::$core->request('action')))
//		{
//			self::$core->DB()->query('boardname', 'SELECT name FROM ibb_boards WHERE id='.self::$core->request('action'));
//			self::$core->hotfixAddRequest('action', self::$core->DB()->results['boardname'][0]['name']);
//		}
//
//		$subac = (self::$core->request('subaction') ? '&subaction='.self::$core->request('subaction') : '');
//
//		$url = 'app='.$app.'&mod='.$module.'&area='.$area.'&action='.self::$core->request('action') .
//			$subac;
//
//		# class to turn a url into a furl here
//		if (self::$core->DB()->results['areadata']['onprocess_class'] == 'boards_boardpage_view')
//		{
//			return 'index.php?/'.self::$core->request('action').'/'.
//				(self::$core->request('subaction') ? self::$core->request('subaction') : '');
//		}
//		elseif (self::$core->DB()->results['areadata']['onprocess_class'] == 'main_front_view')
//		{
//			return 'index.php';
//		}
//		# temp
//		return 'index.php?'.$url;
//	}
//
//	/**
//	 * Load class
//	 *
//	 * @param $class
//	 *
//	 * @throws Exception
//	 */
//	public static function loadAPI($class)
//	{
//		if (!is_file(IBB_ROOT_PATH . "/app/$class/api.php"))
//			throw new Exception('Application API not found ' . IBB_ROOT_PATH . "/app/$class/api.php");
//
//		require_once IBB_ROOT_PATH . "/app/$class/api.php";
//	}
//}

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
	 * @var bool
	 */
	public $user = FALSE;

	/**
	 * @var array
	 */
	public $page;

	public $js;


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
	 * Constructor
	 */
	public function __construct()
	{
		$this->core = 	ibbCore::getInstance();
		$this->db 	=	$this->core->DB();
	}

	/**
	 * Set the page's <title>title</title>.
	 *
	 * @param $title string
	 */
	public function setTitle( $title )
	{
		$this->title = $title;
	}

	/**
	 * Which folder are we looking in for the templates?
	 *
	 * @param $path string
	 */
	public function setPath( $path )
	{
		$this->path = $path;
	}

	/**
	 * What is the master template's name?
	 *
	 * @param $file string
	 */
	public function setFile( $file )
	{
		$this->file = $file;
	}

	/**
	 * Add a slave macro
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
	 * Set the master macro
	 *
	 * @param string $macro
	 * @param string $template_src
	 */
	public function setPage( $macro, $template_src)
	{
		$this->page = array('name' 	=> $macro,
							'src'	=> is_file($this->path.$template_src) ? $this->path.$template_src : $template_src);
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
	 * Set the master template
	 *
	 * @param $path string
	 * @param $file string
	 */
	public function setMasterTemplate( $path, $file )
	{
		$this->setPath( $path );
		$this->setFile( $file );
		$this->workingpath = $path . $file;
	}

	/**
	 * Could use some more work? Seems... weird
	 *
	 * Add CSS to the head. It will look for it in the /css/ directory
	 * within the Master Template's directory.
	 *
	 * @param string $filename add CSS file
	 */
	public function addCSS($filename)
	{
		$this->vars['imgbb']['css'][] = $filename;
		$this->css = TRUE;
	}

	/**
	 * tmp TODO IMGBB
	 *
	 * @param $filename
	 */
	public function addJS($filename)
	{
		$this->vars['imgbb']['js'][] = $filename;
		$this->js = TRUE;
	}

	/**
	 * Mostly used to take stuff from app Main
	 *
	 * Add CSS to the head. It will look for it in the /css/ directory
	 * within the designated $app's Master Template directory.
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

		/* Set up menu	*/
		if ($this->menu)
		{
			$this->addXAppCSS('menu.css', 'main');

			$this->db->query('boards', '
			SELECT 		 ibb_boards.id		AS board_id
						,ibb_boards.name	AS board_name
						,ibb_boards.title	AS board_title
						,ibb_boards.type	AS board_type
						,ibb_boards.category AS board_category
						,ibb_board_categories.id AS category_id
						,ibb_board_categories.name AS category_name
			FROM 	  	ibb_boards
			LEFT JOIN 	ibb_board_categories
			ON 		  	ibb_boards.category = ibb_board_categories.id
			WHERE 		ibb_boards.category = ibb_board_categories.id
			');
			$this->vars['boards'] = $this->db->results['boards'];

			#Clearing some notices
			$this->vars['boardsections'] = array();

			foreach ( $this->db->results['boards'] as $board )
			{
				if ( !in_array( $board['category_name'], $this->vars['boardsections'] ) )
				{
					$this->core->output->vars['boardsections'][$board['category_id']] = $board['category_name'];
				}
			}

		}

		if ( $this->footer )
		{
			$this->addXAppCSS('footer.css', 'main');
		}

		/* Set up static variables. */
		$this->vars['imgbb']['base_url'] 	= $this->core->settings('base_url');
		$this->vars['imgbb']['core_url']	= $this->core->settings('core_url');
		$this->vars['imgbb']['core_url_tr'] = $this->core->settings('core_url_tr');
		$this->vars['imgbb']['this_app']	= $this->core->request('app');
		$this->vars['imgbb']['page']		= $this->page;
		$this->vars['imgbb']['macro']		= $this->macro;
		$this->vars['imgbb']['slots']		= $this->slots;
		$this->vars['imgbb']['tplpath']		= $this->path;
		$this->vars['imgbb']['title']		= $this->title;
		$this->vars['imgbb']['highlight']	= array ( 	'app' 		=> $this->core->request('app'),
												  		'mod' 		=> $this->core->request('mod'),
												  		'area' 		=> $this->core->request('area'),
												  		'action' 	=> $this->core->request('action'),
														'subaction'	=> $this->core->request('subaction')
											 );
		$this->vars['imgbb']['IBB_TEMPLATES_PATH']	= IBB_TEMPLATES_PATH;
		$this->vars['imgbb']['menubar']		= $this->menu;
		$this->vars['imgbb']['footer']		= $this->footer;
		$this->vars['imgbb']['user']		= $this->core->user();


		/* Give PHPTAL our variables */
		foreach ( $this->vars as $key => $value )
		{
			$this->tpl->$key = $value;
		}

		/* Set up booleans */
		# TODO IMGBB set up our own template engine

		# temp
		/* Set configuration */
		$this->tpl->setPhpCodeDestination(IBB_LIB_PATH . '/PHPTAL-' . preg_replace('#_#', '.', PHPTAL_VERSION) . '/cache');

		/* All fired up, baby, OUR IMAGEBOARD IS WORKING */
		$this->tpl->echoExecute();

	}

}

/**
 * Class User
 *
 * dead class, needs a lot of rethinking
 */
class User {

	protected 			$core;
	protected 			$db;
	protected static 	$instance;
	protected			$user_data;
	public	 			$permissions;
	public				$registered = FALSE;
	public				$display_name;
	public				$display_trip;
	public				$names;
	public				$rank = 0;
	public				$is_staff = 0;

	/**
	 * @return User
	 */
	public static function getInstance()
	{
		if (!self::$instance)
			return self::$instance = new self;
		else
			return self::$instance;
	}

	/**
	 * documentation later, still not sure about this
	 *
	 * todo completely rewrite the BASIC algorithm
	 *
	 * @param bool $id
	 *
	 * @return array
	 */
	public function init()
	{
			if ( isset($_SESSION['user_id']) )
			{
				$this->registered = TRUE;

				$this->user_data = $this->db->queryDirect('
					SELECT	 ibb_users.group_id					 as		user_group_id
							,ibb_users.rank_id					 as		user_rank
							,ibb_users.salt				  		 as		user_salt
							,ibb_users.password				 	 as		user_password
							,ibb_users.display_trip 			 as		user_display_trip
							,ibb_users.display_name 			 as		user_display_name
							,ibb_users.username					 as		user_username
							,ibb_users.id						 as		user_id
							,ibb_user_groups.name				 as		user_group_name
							,ibb_user_groups.id				 	 as		user_group_id
							,ibb_user_groups.is_staff			 as		user_group_is_staff
							,ibb_user_ranks.name				 as 	user_rank_name
							,ibb_user_ranks.display_name 		 as 	user_rank_display_name
							,ibb_user_ranks.display_stylization  as 	user_rank_display_stylization
					FROM	ibb_users
					LEFT JOIN ibb_user_groups 	ON (ibb_user_groups.id = ibb_users.group_id)
					LEFT JOIN ibb_user_ranks	ON (ibb_user_ranks.id = ibb_users.rank)
					WHERE	ibb_users.id = ' . $_SESSION['user_id'])[0];


				if ( $this->user_data )
				{
					$this->boot();
					return $this->user_data;
				}
				else
				{
					error_log('CLASS USER->init() DEBUG: $_SESSION[\'user_id\') ('.$_SESSION['user_id'].') found but no result in user data(?)');
					return false;
				}
			}
			else
			{
				$this->user_data = $this->db->queryDirect('
					SELECT	 ibb_user_groups.id			as	user_group_id
							,ibb_user_groups.name		as	user_group_name
							,ibb_user_groups.is_staff	as	is_staff
					FROM	ibb_user_groups
					WHERE	ibb_user_groups.id = 5')[0];

				$this->boot();
				return false;
			}
	}

	public function boot()
	{
		$this->db->query('permissions', '
			SELECT	*
			FROM	ibb_user_board_permissions
			WHERE	groupid = ' . $this->getData()['user_group_id'] . '
		');

		if ( $this->registered )
		{
			$this->db->query('names', '
			SELECT		*
			FROM		ibb_names
			WHERE		userid	= ' . $_SESSION['user_id'] . '
			UNION
			SELECT		 null
						,id
						,display_name
						,display_trip
						,null
			FROM		ibb_users
			WHERE		id		= ' . $_SESSION['user_id']
			);

			/* Make each name accessible by its id from the database. Make sure to access $this->names */
			/* and NOT the query result, because the query results has arbitrary keys to each name */
			foreach ($this->db->results['names'] as $name)
			{
				# 0 == primary name
				if ($name === end($this->db->results['names']))
				{
					$name['id'] = 0;
					$this->names[0] = $name;
				}
				else
				{
					$this->names[$name['id']] = $name;
				}
			}

			ksort($this->names);

			$this->display_name = $this->getData()['user_display_name'];
			$this->display_trip = $this->getData()['user_display_trip'];
			$this->is_staff		= $this->getData()['user_group_is_staff'];

		}
		else
		{
			$this->display_name	= 'Anonymous';
			$this->display_trip	= '';
		}

		foreach ($this->db->results['permissions'] as $board)
		{
			/* Set each individual boardid as an independent value with the category id as the parent key */
			/* e.g. $this->permissions['boards'][1][3] = 1 is ['boards']['Support'][3]['Questions & Answers'] */
			$this->permissions['boards'][$board['categoryid']][] = $board['boardid'];
		}

		if ($this->user_data['user_group_is_staff'] == 1)
		{
			$this->bootStaff();
		}
	}


	public function bootStaff()
	{
		$this->is_staff = TRUE;
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		#Need to add some more stuff here
		return $this->user_data;
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		if ($this->registered)
			return $this->getData()['user_id'];
		else
			return 0;
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getPermissions($key)
	{
		return $this->permissions[$key];
	}

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->core = ibbCore::getInstance();
		$this->db	= $this->core->db();
	}

	/**
	 * @return bool
	 */
	public function checkPrivilege( )
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
abstract class ibbDBCore /*implements ibbDBCoreInterface */ {

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
	 * @var mysqli_stmt|mysqli_result qobject The query results object
	 */
	public $qobject;

	/**
	 * @var string $query_error The error, if any.
	 */
	public $query_error = FALSE;

	/**
	 * @return db_mysql
	 */
	public static function getDBType()
	{
		return new db_mysql;
	}

	/**
	 * @return mysqli
	 */
	abstract public function instance();

	/**
	 * @param	$query	string
	 * @param	$type	null
	 * @param	$qname	null
	 *
	 * @throws	Exception
	 */
	abstract public function execute( $query, $type = NULL, $qname = NULL );

	/**
	 * @param $query string
	 *
	 * @return mysqli_stmt
	 */
	abstract public function prepare( $query );

	/**
	 * @param string $type MYSQLI_ASSOC,... etc
	 *
	 * @return mixed
	 */
	abstract public function returnAll( $type );

	/**
	 * @return mixed
	 */
	abstract public function storeResult();


	/**
	 * $qtype can contain a number of constants.<br />
	 *
	 * <b>SINGLE_RESULT_QUERY</b> - Your query is the same, except it will automatically load the [0] index into the query.
	 * That is to say, you will use $results[$column] instead of $results[0][$column]. Useful for queries that, ahem,
	 * have a SINGLE RESULT<br />
	 *
	 * <b>DIRECT_QUERY</b> - The results will return the data instead of latching it into $results.
	 *
	 * @param      $qname
	 * @param      $query
	 * @param null $qtype
	 *
	 * @throws Exception
	 *
	 * @return array [optional] results
	 */
	public function query( $qname, $query, $qtype = NULL)
	{
		$success = $this->execute($query);

		if ( $success )
		{
			switch ( $qtype )
			{
				case 1:
				{
					$this->results[$qname] = $this->returnAll(MYSQLI_ASSOC)[0];
					try
					{
						if (!$this->results[$qname])
						{
							throw new Exception( $qname );
						}
					}
					catch (Exception $e)
					{
						throw new Exception($e->getMessage());
					}
					break;
				}
				case 2:
				{
					return $this->returnAll(MYSQLI_ASSOC);
				}
				case 3:
				{
					$store_me_away = func_get_arg(3);
					$this->results[$qname][$store_me_away] = $this->returnAll(MYSQLI_ASSOC);
					break;
				}
				default:
				{
					$this->results[$qname] = $this->returnAll(MYSQLI_ASSOC);
					break;
				}
			}
		}
		else
		{
			$this->qobject = $this->storeResult();

			if ($this->qobject->num_rows == 0)
			{
				print_r($query);
				echo '<br /><br />';
				throw new Exception( 'No results bruddah, Wizard, you need to make an error page for me to build a noresults exception' );
			}
			else
			{
				throw new Exception($this->query_error . '\n Query failed! ' . $query );
			}

		}
	}

	/**
	 * @param $query
	 *
	 * @return array
	 */
	public function queryDirect( $query )
	{
		$this->execute($query);
		return $this->returnAll(MYSQLI_ASSOC);
	}

	/**
	 * temp
	 *
	 * @param $qname
	 * @param $parent
	 * @param $query
	 *
	 * @throws Exception
	 */
	public function queryInLoop( $qname, $parent, $query )
	{
		$success = $this->execute($query);

		if ( $success )
		{
			$this->results[$qname][$parent] = $this->returnAll(MYSQLI_ASSOC);
		}
		else
		{
			throw new Exception(' Query failed! ' . $query);
		}
	}

	/**
	 * <br />
	 * @deprecated
	 * <br />
	 * This is used for queries against the database that fetch only one row. This function does not include
	 * an array filled with each row, so you can access the row's columns directly without needing to add
	 * annoying [0]s every time.
	 *
	 *
	 * @param $qname
	 * @param $query
	 *
	 * @throws Exception
	 */
	public function singleResultQuery ( $qname, $query )
	{
		try {
			$this->execute( $query );
			$results = $this->storeResult();
			if ($results->num_rows == 0)
				throw new Exception('Query returned no results, boards needs to catch this ' . $query);
			# dat [0]... there must be some function or constant that can be passed as an option to stop double array...
			$this->results[$qname] = $results->fetch_all(MYSQLI_ASSOC)[0];
		}
		catch (exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * @param $query
	 *
	 * @return bool
	 */
	public function queryBoolean ( $query )
	{
		$this->execute ( $query );
		$results = $this->storeResult();
		if ($results->num_rows == 0)
		{
			return false;
		}
		else
		{
			return $this->returnAll(MYSQLI_ASSOC)[0];
		}

	}

	/**
	 * @deprecated
	 *
	 * @throws Exception
	 */
	public function commit()
	{
		error_log($this->query);
		try
		{
			$this->execute( $this->query );
		}
		catch (Exception $e)
		{
			throw $e;
		}
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

/**
 * Class db_mysql
 */
class db_mysql extends ibbDBCore
{
	/**
	 * @return mysqli
	 */
	public function instance()
	{
		if ( !self::$dbinstance )
			self::$dbinstance = new mysqli('localhost', 'root', '', 'zildjohn01');

		if ( self::$dbinstance->connect_errno) {
			echo "Failed to connect to MySQL: (" . self::$dbinstance->connect_errno . ") " . self::$dbinstance->connect_error;
		}

		return self::$dbinstance;
	}

	/**
	 * @param string $query
	 *
	 * @return mysqli_stmt
	 *
	 * @throws Exception
	 */
	public function prepare( $query )
	{
		if ($this->qobject = $this->instance()->prepare( $query ))
		{
			return TRUE;
		}
		else
		{
			throw new Exception('MySQL error ('.$this->instance()->errno.') '.$this->instance()->error);
		}
	}


	/**
	 * @param string $query
	 * @param null  $type
	 * @param null  $qname
	 *
	 * @return bool|void
	 * @throws Exception
	 */
	public function execute( $query, $type = NULL, $qname = NULL )
	{
		ibbCore::$queryc[] = $query;
		$success = $this->instance()->real_query( $query );

		if ($success)
		{
			$this->qobject = $this->instance()->use_result();
			return true;
		}
		else
		{
			$this->query_error = $this->instance()->error;
			throw new Exception($this->query_error . 'Full query: ' . $query);
		}
	}

	/**
	 * @param int|string $type MYSQLI_ASSOC, MYSQLI_NUM, or MYSQLI_BOTH
	 *
	 * @return mixed|void
	 */
	public function returnAll( $type = MYSQLI_ASSOC )
	{
		return $this->qobject->fetch_all($type);
	}

	/**
	 * @return mysqli_result
	 */
	public function storeResult()
	{
		return $this->instance()->store_result();
	}
}
/**
 * Dunno where to put this stuff
 *
 * Class TempStuff
 */
class TempStuff
{
	/**
	 * @param string $app
	 * @param string $module
	 * @param string $area
	 */
	public function sendMeAway( $app = '', $module = '', $area = '')
	{

	}
}