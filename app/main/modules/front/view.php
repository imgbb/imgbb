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
		$this->db->query('boards', '
			SELECT 		 ibb_boards.id 	AS 				board_id
						,ibb_boards.name AS 			board_name
						,ibb_boards.title AS 			board_title
						,ibb_boards.category AS 		board_category
						,ibb_boards.type AS 			board_type
						,ibb_board_categories.id AS 	category_id
						,ibb_board_categories.name AS 	category_name
			FROM 	  	ibb_boards
			LEFT JOIN 	ibb_board_categories
			ON 		  	ibb_boards.category 			= ibb_board_categories.id
			WHERE 		ibb_boards.category 			= ibb_board_categories.id');

		/* begin temp */

		// force enable modular system since settings do not exist and are not handled yet

		require_once 'recentposts.php';
		require_once 'news.php';

		$rp = new main_front_recentposts;
		$rp->init();
		$news = new main_front_news;
		$news->init();

		/* end temp */

		// todo get rid of this asap
		ClassHandler::loadAPI('boards');

		// todo this too, put it all somewhere easily accessible... maybe through the intface?
		/* Set menu CSS */
		$this->core->output->addCSS( 'menu' );
		/* Set page title */
		$this->core->output->setTitle( 'imgBB' );

		///////////////////////////////
		//	Prepare dynamic variables
		//////////////////////////////

		// useless now that BASIC is modular
		$this->core->output->vars['boards'] = $this->db->results['boards'];

		/* Let's recycle that function */
		$this->core->output->vars['boardsections'] = Boards_API::returnBoardCategories($this->core->output->vars['boards']);
	}
}