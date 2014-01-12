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
//	private $post_index;
	private $row_index;
	public  $posts = array();
	public	$name;
	public $tripcode;
	public $message;
	public $date;
	//i am going to hell
	public $is_anon;
	public $getname;
	public $verify_name;
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
//		echo 'construct<br />';
		$this->posts = $posts;

		$this->db->query('ranks','
			SELECT	*
			FROM	ibb_user_ranks
		');

		// This is becoming a trend. I do not like it. Has appeared in the user bootuser too
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

//	function __toString()
//	{
//		return 'object';
//	}

	function rewind()
	{
//		echo 'rewind<br />';
//		echo $this->row_index;
		$this->row_index	= 0;
	}

	/*
	If name exists
		use name
	if tripcode exists and name exists
		use name
	if tripcode does not exist and name exists
		use name
	if name does not exist and tripcode does exist
		return true
	if name does not exist and tripcode does not exist
		return false


	*/

	/**
	 * Iterator current()
	 *
	 * @return array
	 */
	function current()
	{
//		echo 'current<br />';
//		echo $this->row_index;
//		$this->name			= ($this->hasName($this->row_index)) ? $this->posts[$this->row_index]['name'] : FALSE;
//		$this->tripcode		= ($this->hasTrip($this->row_index)) ? $this->posts[$this->row_index]['tripcode'] : FALSE;
//		$this->message		= ($this->posts[$this->row_index]['message'] 	== '') ? FALSE : $this->posts[$this->row_index]['message'];
//		$this->date			= ($this->posts[$this->row_index]['timestamp'] 	== '') ? FALSE : $this->posts[$this->row_index]['timestamp'];
//		echo 'is_anon: ';
//		var_dump($this->is_anon);
//		echo '<br />getname: ';
//		var_dump($this->getname);
//		echo '<br />verifyname: ';
//		var_dump($this->verify_name);
//		echo '<br />nameandtrip: ';
//		var_dump($this->nameandtrip);
//		echo '<br />name: ';
//		var_dump($this->name);
//		echo '<br />tripcode: ';
//		var_dump($this->tripcode);
//		echo '<br />hasname: ';
//		var_dump($this->hasName($this->row_index));
//		echo '<br />hastrip: ';
//		var_dump($this->hasTrip($this->row_index));

		return $this->posts[$this->row_index];
	}

	function key()
	{
//		echo 'key<br />';
//		echo $this->row_index;
		return $this->row_index;
	}

	function next()
	{
//		echo 'next';
//		echo $this->row_index;
		++$this->row_index;
//		$this->name 		= $this->posts[$this->row_index]['name'];
//		$this->tripcode		= $this->posts[$this->row_index]['tripcode'];
//		$this->message		= $this->posts[$this->row_index]['message'];
//		$this->date			= $this->posts[$this->row_index]['timestamp'];
	}

	/**
	 * Iterator valid()
	 *
	 * @return bool
	 */
	function valid()
	{
//		echo 'validate<br />';
//		echo $this->row_index;
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

	/**
	 * @param $index int
	 *
	 * @return bool
	 */
	public function hasName( $index )
	{
//		var_dump($this->posts);
		if ( $this->posts[$index]['name'] == '')
			return false;
		else
			return true;

	}

//	public function hasTrip( $index )
//	{
//		if ( $this->posts[$index]['tripcode'] == '' )
//			return false;
//		else
//			return true;
//	}
//
//	function getName()
//	{
//		 is anon
//		if (!$this->name && !$this->tripcode)
//		{
//			echo '1st';
//			return FALSE;
//		}
//
//		 has name, no tripcode
//		if ($this->name && !$this->tripcode)
//		{
//			echo '2nd';
//			return $this->name;
//		}

		//trip but no name
//		if ($this->tripcode && !$this->name)
//		{
//			echo '3rd';
//			return TRUE;
//		}
//
//		name and trip
//		if ($this->tripcode && $this->name)
//		{
//			echo '4th';
//			return $this->name;
//		}

//	}
}