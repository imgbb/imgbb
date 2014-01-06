<?php
/**
 * Class main_front_recentposts
 *
 * @BASIC
 * @FINAL
 */
class main_front_recentposts {

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

	public function init()
	{
		$this->db->query('posts', '
			SELECT 		 ibb_posts.id
						,ibb_posts.boardid
						,ibb_posts.parentid
						,ibb_posts.display_name
						,ibb_posts.display_tripcode
						,ibb_posts.subject
						,ibb_posts.message
						,ibb_posts.file
						,ibb_posts.file_server
						,ibb_posts.file_type
						,ibb_posts.thumb_w
						,ibb_posts.thumb_h
						,ibb_posts.rank
						,ibb_posts.timestamp
						,ibb_boards.name AS board_name
						,ibb_boards.type
						,ibb_user_ranks.display_name AS rank_display_name
						,ibb_user_ranks.display_stylization AS rank_display_stylization
			FROM 		ibb_posts
			LEFT JOIN 	ibb_boards
			ON 			boardid = ibb_boards.id
			LEFT JOIN 	ibb_user_ranks
			ON			ibb_user_ranks.id = ibb_posts.rank
			WHERE 		DELETED = 0
			AND			ibb_boards.type = 0
			ORDER BY 	`timestamp` DESC
			LIMIT 		10');

		/* Set CSS */
		$this->core->output->addCSS('recentposts');

		$this->core->output->vars['posts'] = $this->db->results['posts'];

		/* Patch up timestamps, maybe should turn this into a function? Dunno */
		foreach ($this->core->output->vars['posts'] as &$post)
		{
//			$post['timestamp'] 	=	date('jS \of F, Y', $post['timestamp']);
			$post['message']	=	stripslashes($post['message']);
			$post['message']	=	preg_replace('#\[i\](.*)?\[/i\]#', '<i>\1</i>', $post['message']);
			$post['message']	=	strlen($post['message']) > 500 ? substr($post['message'], 0, 500) . ' <b>&hellip;</b>' : $post['message'];
			if ($post['parentid'] == 0)
			{
				$post['parentid'] = $post['id'];
			}
		}

	}
}