<?php
/**
 * Class main_manage_move
 */
class main_manage_move
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
		$this->core->output->setPage('move', 'move.xhtml');
		$this->core->output->addCSS('move');
	}

	public function process()
	{

	}
}