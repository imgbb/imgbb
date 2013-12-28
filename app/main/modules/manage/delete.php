<?php
/**
 * Class main_manage_delete
 */
class main_manage_delete
{
	private $core;
	private $db;
	private $user;
	private $output;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->core = ibbCore::getInstance();
		$this->db	= $this->core->DB();
		$this->user	= $this->core->user();
	}

	public function init()
	{
		$this->output = 'Post deletion page.';
	}
}