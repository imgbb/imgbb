<?php
/**
 * Class user_manage_logout
 */
class user_manage_logout
{
	public $core;
	public $db;

	public function init()
	{
		session_destroy();
		header('Location: http://www.imgbb.net/imgbb/index.php?/q/');
	}
}