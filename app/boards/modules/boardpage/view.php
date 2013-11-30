<?php
/**
 * Created by IntelliJ IDEA.
 * User: Admin
 * Date: 11/4/13
 * Time: 6:58 PM
 * To change this template use File | Settings | File Templates.
 *
 * @BASIC
 * @INCOMPLETE
 */

class boards_boardpage_view {

	/**
	 * @var ibbDBCore
	 */
	public $db;
	/**
	 * @var ibbCore
	 */
	public $core;


	/**
	 *
	 */
	public function __construct()
	{
		$this->core 	= 	ibbCore::getInstance();
		$this->db		=	$this->core->DB();
		$this->request 	=	$this->core->request();
	}

	/**
	 * @return PHPTAL
	 * @throws Exception
	 */
	public function init()
	{
		$this->db->singleResultQuery('boardinfo', '
			SELECT			ibb_boards.id 					AS board_id,
							ibb_boards.name 			AS `board_name`,
							title,
							ibb_boards.category 		AS board_category,
							ibb_board_categories.id 	AS category_id,
							ibb_board_categories.name 	AS category_name
			FROM			ibb_boards
			LEFT JOIN		ibb_board_categories
			ON				ibb_board_categories.id = ibb_boards.category
			WHERE			ibb_boards.name 					= "'.$this->core->request('action').'"
		');
		$this->db->query('boards', '
			SELECT 		 ibb_boards.id 	AS board_id
						,ibb_boards.name AS board_name
						,ibb_boards.title AS board_title
						,ibb_boards.category AS board_category
						,ibb_board_categories.id AS category_id
						,ibb_board_categories.name AS category_name
			FROM 	  	ibb_boards
			LEFT JOIN 	ibb_board_categories
			ON 		  	ibb_boards.category = ibb_board_categories.id
			WHERE 		ibb_boards.category = ibb_board_categories.id');

		/* Load API... jesus I need a better/dynamic way to do this, TODO */
		ClassHandler::loadAPI('boards');

		/* Set page title */
		$this->core->output->setTitle($this->db->results['boardinfo']['title']);

		/* Use the API and stuff. */
		$this->core->output->vars['boardsections'] = Boards_API::returnBoardCategories($this->db->results['boards']);

		if (!$this->core->request('subaction'))
		{
			/* Kusaba X level of efficiency. Consulting. */
			$this->db->query('stickies', '
			SELECT		id
						,boardid
						,parentid
						,name
						,tripcode
						,email
						,subject
						,message
						,file
						,file_server
						,file_type
						,file_original
						,file_size
						,image_w
						,image_h
						,timestamp
						,stickied
						,locked
						,is_deleted
			FROM		pcposts
			WHERE		boardid		=	' . $this->db->results['boardinfo']['board_id'] . '
			AND			parentid	=	0
			AND			is_deleted	=	0
			AND			stickied	=	1
			ORDER BY	`bumped` DESC');

			foreach ($this->db->results['stickies'] as $sticky)
			{
				$this->db->queryInLoop('replies', $sticky['id'], '
					SELECT		*
					FROM		pcposts
					WHERE 		boardid 	=	' . $this->db->results['boardinfo']['board_id'] . '
					AND			parentid	=	' . $sticky['id'] . '
					AND			is_deleted	=	0
					ORDER BY	`bumped` DESC
					LIMIT 		1');
			}


			$this->db->query('parents', '
			SELECT		*
			FROM		pcposts
			WHERE 		boardid 	=	' . $this->db->results['boardinfo']['board_id'] . '
			AND 		parentid	=	0
			AND			is_deleted	=	0
			AND			stickied	=	0
			ORDER BY	`bumped` DESC
			LIMIT 		' . (10 - count($this->db->results['stickies'])));
			foreach ($this->db->results['parents'] as $parent)
			{
				$this->db->queryInLoop('replies', $parent['id'], '
					SELECT		*
					FROM		pcposts
					WHERE 		boardid 	=	' . $this->db->results['boardinfo']['board_id'] . '
					AND			parentid	=	' . $parent['id'] . '
					AND			is_deleted	=	0
					ORDER BY	`bumped` DESC
					LIMIT 		3');
			}

			$this->db->results['parents'] = array_merge($this->db->results['stickies'], $this->db->results['parents']);
			foreach ( $this->db->results['parents'] as $parent )
			{
				$this->db->queryInLoop('repliescount', $parent['id'], '
				SELECT		COUNT(*)
				FROM 		pcposts
				WHERE		boardid		=	' . $this->db->results['boardinfo']['board_id'] . '
				AND			parentid			=	' . $parent['id']);
			}




			/* Add the macro  */
			$this->core->output->addMacro('board', 'boards.xhtml');
//			print_r($this->db->results['replies']);

			/////////////////////////////////
			//// Prepare dynamic variables
			/////////////////////////////////

			/* Load SQLs into the vars */
			foreach ( $this->db->results as $queryk => $query )
			{
				$this->core->output->vars[$queryk] = $query;
			}


		} else
		{
			$this->viewSingleThread( $this->core->request('action'), $this->core->request('subaction'));
		}
	}

	/**
	 * @param      $board
	 * @param      $thread
	 * @param null $user
	 */
	public function viewSingleThread( $board, $thread, $user = NULL)
	{
		$this->db->query('posts', '
			SELECT 	*
			FROM	pcposts
			WHERE	boardid		= 	(SELECT id FROM pcboards WHERE name = "'.$board.'")
			AND 	id			= 	'.$thread.'
			AND		is_deleted	=	0
			UNION
			SELECT	*
			FROM	pcposts
			WHERE	boardid		=	(SELECT id FROM pcboards WHERE name = "'.$board.'")
			AND 	parentid	=	'.$thread.'
			AND		is_deleted	=	0
		');

		/* Load SQLs into the vars */
		foreach ( $this->db->results as $queryk => $query )
		{
			$this->core->output->vars[$queryk] = $query;
		}

		$this->core->output->addMacro('thread', 'boards.xhtml');
	}
}