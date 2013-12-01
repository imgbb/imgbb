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
	public  $posts = array();
	private $post_index;
	private $row_index;
	public	$name;
	public $tripcode;
	public $message;
	public $date;


	/**
	 * Constructor
	 *
	 * @param $parents array
	 */
	function __construct( $posts )
	{
//		echo 'construct<br />';
		$this->posts = $posts;
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

	function current()
	{
//		echo 'current<br />';
//		echo $this->row_index;
		$this->name 	= ($this->hasName($this->row_index)) ? $this->posts[$this->row_index]['name'] : FALSE;
		$this->tripcode	= ($this->hasTrip($this->row_index)) ? $this->posts[$this->row_index]['tripcode'] : FALSE;
		$this->message	= ($this->posts[$this->row_index]['message'] 	== '') ? FALSE : $this->posts[$this->row_index]['message'];
		$this->date		= ($this->posts[$this->row_index]['timestamp'] 	== '') ? FALSE : $this->posts[$this->row_index]['timestamp'];
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

	function valid()
	{
//		echo 'validate<br />';
//		echo $this->row_index;
		return isset($this->posts[$this->row_index]);
	}

	/**
	 * TODO fix this -1 thing
	 *
	 * @return bool
	 */
	public function hasName( $index )
	{
//		var_dump($this->posts);
		if ( $this->posts[$index]['name'] == '' && $this->posts[$index]['tripcode'] == '' )
			return false;
		else
			return true;

	}

	public function hasTrip( $index )
	{
		if ( $this->posts[$index]['tripcode'] == '' )
			return false;
		else
			return true;
	}
}