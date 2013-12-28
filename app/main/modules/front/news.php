<?php
/**
 * Created by IntelliJ IDEA.
 * User: Admin
 * Date: 10/27/13
 * Time: 2:00 PM
 * To change this template use File | Settings | File Templates.
 */
/**
 * Class main_front_news
 *
 * @BASIC
 * @FINAL
 */
class main_front_news {

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
		$this->db->query('news', '
			SELECT		 ibb_user_ranks.`id` AS `staff_rank_id`
						,ibb_user_ranks.`display_name` AS `staff_display_name`
						,ibb_users.`group_id` AS `user_staff_level`
						,ibb_users.`id` AS `user_id`
						,ibb_users.`display_name`
						,ibb_users.`display_trip`
						,ibb_front_news.`user`
						,`header`
						,`timestamp`
						,`entry`
			FROM		ibb_front_news
			LEFT JOIN	ibb_users
			ON			ibb_users.`id` = ibb_front_news.`user`
			LEFT JOIN	ibb_user_ranks
			ON			ibb_user_ranks.`id` = ibb_users.`group_id`
		');
		$this->db->query('front_categories', '
			SELECT		*
			FROM		ibb_front_categories');

		/* Set CSS */
		$this->core->output->addCSS( 'front' );

		$this->core->output->vars['news'] 				= $this->db->results['news'];
		$this->core->output->vars['front_categories'] 	= $this->db->results['front_categories'];



		// TODO need a rewrite by alpha to allow customization of default page
		if ($this->core->request('action'))
		{
			if ($this->core->output->vars['front_categories'][($this->core->request('action') - 1)])
			{
				if ($this->core->output->vars['front_categories'][($this->core->request('action') - 1)]['type'] == 2)
				{
					$this->news();
				}
				else
				{
					$this->regular_front_entries();
				}
			}
			else
			{
				// COME ON WTF
				echo '<br />';
				echo $this->core->request( 'action' );
				echo '<br />';
				throw new Exception( 'Could not find requested category!' );
			}
		}
		else
			$this->news();

		$this->core->output->addMacro('front', 'home.xhtml');

		$this->core->output->vars['front_data'] = $this->db->results['front_data'];
	}

	public function news()
	{
		$this->db->query('front_data', '
			SELECT		 ibb_user_ranks.`id` AS `staff_rank_id`
						,ibb_user_ranks.`display_name` AS `staff_display_name`
						,ibb_user_ranks.`display_stylization` AS `staff_display_stylization`
						,ibb_front_news.`user`
						,ibb_front_news.display_name
						,ibb_front_news.display_trip
						,`header`
						,`timestamp`
						,`entry`
						,`file`
			FROM		ibb_front_news
			LEFT JOIN	ibb_user_ranks
			ON			ibb_user_ranks.`id` = ibb_front_news.`rank_id`
		');

		foreach ( $this->db->results['front_data'] as &$entry )
		{
			if ( $entry['timestamp'] )
				$entry['timestamp'] = date( 'jS \of F, Y g:i A', $entry['timestamp'] );
		}
	}

	public function regular_front_entries()
	{
		$this->db->query('front_data', '
			SELECT		*
			FROM		ibb_front_entries
			WHERE		`category` = ' . $this->core->request('action')
		);
	}
}