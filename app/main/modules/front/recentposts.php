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
			SELECT 		 pcposts.id
						,boardid
						,message
						,parentid
						,file
						,file_type
						,file_server
						,tripcode
						,pcposts.name
						,pcboards.name AS boardname
						,timestamp
						,posterauthority
						,ibb_user_ranks.display_name AS rank_display_name
			FROM 		pcposts
			LEFT JOIN 	pcboards
			ON 			boardid = pcboards.id
			LEFT JOIN 	ibb_user_ranks
			ON			ibb_user_ranks.id = posterauthority
			WHERE 		IS_DELETED != 1
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
		}

	}
}