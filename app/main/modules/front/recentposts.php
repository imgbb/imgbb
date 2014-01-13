<?php
/**
 * Class main_front_recentposts
 *
 * @BASIC
 * @FINAL
 */
class main_front_recentposts
{
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
			FROM 		ibb_posts
			LEFT JOIN 	ibb_boards
			ON 			boardid = ibb_boards.id
			WHERE 		DELETED = 0
			AND			ibb_boards.type = 0
			ORDER BY 	`timestamp` DESC
			LIMIT 		10');

		/* Set CSS */
		$this->core->output->addCSS('recentposts');

		$this->core->output->vars['posts'] = new post($this->db->results['posts']);

		/* Patch up timestamps, maybe should turn this into a function? Dunno */
		foreach ($this->core->output->vars['posts'] as $key => $post)
		{
			$ref =& $this->core->output->vars['posts']->posts[$key];
			$ref['display_name']		=	($ref['display_tripcode'] == '' && $ref['display_name'] == '') ? 'Anonymous' : $ref['display_name'];
			$ref['message']	=	strlen($ref['message']) > 500 ? substr($ref['message'], 0, 500) . ' <b>&hellip;</b>' : $ref['message'];

			# Just so that it will make sense.
			if ($ref['parentid'] == 0)
			{
				$ref['parentid'] = $ref['id'];
			}

			/* This entire algorithm is absolutely horrible. Not necessarily in the way it was designed, but its mere
				presence. Just because if anybody messes with font sizes, weight etc, it's completely incompatible. I
				need to consult my front-end designers on ways to do this via CSS. */

			#If we have more than 18 characters total, then we need to do some cleanup.
			if (($strlen = strlen($ref['display_name'] . $ref['display_tripcode'] . $ref['user_rank_display_name'])) > 18)
			{
				#Make sure there's a ## rank name, because that's the buggy thing about it all.
				if (isset($ref['user_rank_display_name']))
				{
					#Since the font-weight determines how much room we truly have, distinguish it. 17/20 are
					#our magic numbers.
					if ($ref['display_name'] != '')
					{
						#Calculate how much characters we have left for the display name. If less than 0, return 0.
						$spare = $this->unsigned(17 - strlen($ref['display_name'] . $ref['display_tripcode']));
					}
					else
					{
						#Calculate how much characters we have left for the display name. If less than 0, return 0.
						$spare = $this->unsigned(20 - strlen($ref['display_name'] . $ref['display_tripcode']));
					}

					#Do we have any spare characters?
					$ref['user_rank_display_name'] = substr($ref['user_rank_display_name'], 0, $spare);

					#We don't.
					if ($spare > 0)
					{
						$ref['user_rank_display_name'] .= '&hellip;';
					}

				}
			}
		}
	}

	/**
	 * Surely there's a function for this, cba searching for it ATM
	 */
	public function unsigned( $num )
	{
		return ($num >= 0) ? $num : 0;
	}
}