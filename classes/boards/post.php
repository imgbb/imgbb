<?php
/**
 * Created by IntelliJ IDEA.
 * User: Admin
 * Date: 12/1/13
 * Time: 12:32 PM
 * To change this template use File | Settings | File Templates.
 */

/**
 * Class parentObj
 *
 * temp name, basic testing grounds
 *
 * STILL BUILDING CLASS
 */
class post implements Iterator
{
	private $core;
	private $row_index;
	public  $posts = array();
	public $ranks;


	/**
	 * Constructor
	 *
	 * @param $posts array
	 */
	function __construct( $posts )
	{
		$this->core = ibbCore::getInstance();
		$this->db	= $this->core->DB();
		$this->posts = $posts;

		$this->db->query('ranks','
			SELECT	*
			FROM	ibb_user_ranks
		');

		# This is becoming a trend. I do not like it. Has appeared in the user bootuser too
		foreach ($this->db->results['ranks'] as $rank)
		{
			$this->ranks[$rank['id']] = $rank;
		}

		foreach ($this->posts as &$post)
		{
			if ($post['rank'] != 0)
			{
				$post['user_rank_display_name']			= $this->ranks[$post['rank']]['display_name'];
				$post['user_rank_display_stylization']	= $this->ranks[$post['rank']]['display_stylization'];
			}
		}
	}

	/**
	 * Iterator rewind()
	 */
	function rewind()
	{
		$this->row_index	= 0;
	}

	/**
	 * Iterator current()
	 *
	 * @return array
	 */
	function current()
	{

		return $this->posts[$this->row_index];
	}

	/**
	 * Iterator key()
	 *
	 * @return int
	 */
	function key()
	{
		return $this->row_index;
	}

	/**
	 * Iterator next()
	 */
	function next()
	{
		++$this->row_index;
	}

	/**
	 * Iterator valid()
	 *
	 * @return bool
	 */
	function valid()
	{
		return isset($this->posts[$this->row_index]);
	}

	/**
	 * Fetch original filename.
	 *
	 * @return string
	 */
	function file_original()
	{
		#why in the world is this -1 necessary...? is this requested AFTER incrementation?
		return
		(strlen($this->posts[$this->row_index - 1]['file_original']) > 50) ?
			substr($this->posts[$this->row_index - 1]['file_original'], 0, 50) . '&hellip;' :
			$this->posts[$this->row_index - 1]['file_original'];
	}
}