<?php
/**
 * Class boards_posting_post
 *
 * pure action, tmp
 */
class boards_posting_post
{
	/**
	 * @var ibbCore
	 */
	public $core;

	/**
	 * @var ibbDBCore
	 */
	public $db;

	public function __construct()
	{
		$this->core = ibbCore::getInstance();
		$this->db	= $this->core->db();
	}

	public function init()
	{
		$success = $this->db->execute('
			INSERT INTO 	ibb_posts
			(
			 boardid
			 ,display_name
			 ,email
			 ,subject
			 ,message
			 ,password
			 ,timestamp)
			 VALUES
			(
			 (SELECT id FROM ibb_boards where name="' . $_POST['board'] . '")
			 ,"'.$_POST['poster_name'].'"
			 ,"'.$_POST['email'].'"
			 ,"'.$_POST['subject'].'"
			 ,"'.$_POST['body'].'"
			 ,"'.$_POST['password'].'"
			 ,'.time()

			.')');

		if ($success)
			header('Location: http://www.imgbb.net/imgbb/index.php?/'.$_POST['board'].'/');
		else
			echo 'nope';
	}
}