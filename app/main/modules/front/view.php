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

		/* force enable modular system since settings do not exist and are not handled yet */

		require_once 'recentposts.php';
		require_once 'news.php';

		$rp = new main_front_recentposts;
		$rp->init();
		$news = new main_front_news;
		$news->init();

		/* end temp */

		/* Set page title */
		$this->core->output->setTitle( 'imgBB' );

	}
}