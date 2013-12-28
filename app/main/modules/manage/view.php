<?php
/**
 * Class main_manage_view
 */
class main_manage_view
{
	private $core;
	private $db;
	private $user;

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
		$this->core->output->setPage('manage', 'manage.xhtml');
	}
}