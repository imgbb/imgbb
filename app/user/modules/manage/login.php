<?php
/**
 * Created by IntelliJ IDEA.
 * User: Admin
 * Date: 12/7/13
 * Time: 8:50 PM
 * To change this template use File | Settings | File Templates.
 */
class user_manage_login
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
		$this->core	= ibbCore::getInstance();
		$this->db	= $this->core->db();
	}

	public function init()
	{
		if (isset($_SESSION['user_name']))
		{
			throw new Exception('already logged in, should redirect to own profile...');
		}

		else
		{
			$this->core->output->addCSS('login');

			$this->core->output->addMacro( 'login' );
		}

	}

	public function process()
	{
		$results = $this->db->queryBoolean('
			SELECT	id
			FROM	ibb_users
			WHERE	`username` = "'.$this->core->request('username').'"
			AND		`password` = "'.$this->core->request('password').'"
		');

		if (!$results)
			throw new Exception('standard exception, login incorrect.');

		setcookie('username', $this->core->request('username'), time()+60*60*24*30);
		$_SESSION['user_name'] 		= $this->core->request('username');
		$_SESSION['user_password'] 	= $this->core->request('password');
		$_SESSION['user_id']		= $results['id'];

	}
}