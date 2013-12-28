<?php
/**
 * Class main_front_view
 *
 * @BASIC
 * @INCOMPLETE
 */
class main_front_view {

	/**
	 * @var ibbDBCore
	 */
	public $db;

	/**
	 * @var ibbCore
	 */
	public $core;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->core 	= 	ibbCore::getInstance();
		$this->db		=	$this->core->DB();
	}

	/**
	 * @throws Exception
	 */
	public function init()
	{

		$this->core->output->setPage('home', 'home.xhtml');
		/* begin temp */

		// force enable modular system since settings do not exist and are not handled yet

		require_once 'recentposts.php';
		require_once 'news.php';

		$rp = new main_front_recentposts;
		$rp->init();
		$news = new main_front_news;
		$news->init();

		/* end temp */

		// todo this too, put it all somewhere easily accessible... maybe through the intface?

		/* Set page title */
		$this->core->output->setTitle( 'imgBB' );

		///////////////////////////////
		//	Prepare dynamic variables
		//////////////////////////////

		// useless now that BASIC is modular
		$this->core->output->vars['boards'] = $this->db->results['boards'];
	}
}