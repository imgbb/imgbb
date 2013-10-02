<?php

class Main {

	public function __construct()
	{
		$this->core = 	ibbCore::getInstance();
		$this->DB	=	$this->core->DB;
	}

	public function templateVars()
	{
		$this->DB->query('boards', '
			SELECT 		*
			FROM 	  	ibb_boards
			LEFT JOIN 	ibb_board_categories
			ON 		  	ibb_boards.category = ibb_board_categories.id');
		$this->DB->query('posts', '
			SELECT 		`pcposts`.`id`,
						`boardid`,
						`message_source`,
						`parentid`,
						`file`,
						`file_type`,
						`file_server`,
						`tripcode`,
						`pcposts`.`name`,
						`pcboards`.`name` AS `boardname`
			FROM 		`pcposts`
			LEFT JOIN 	`pcboards`
			ON 			`boardid` = `pcboards`.`id`
			WHERE 		`IS_DELETED`
							!=
							1
			ORDER BY 	`timestamp` DESC
			LIMIT 		10');

		$this->DB->instance->close();

		$VARS['path']				= dirname(__FILE__) . '/tpl/default.xhtml';
		$VARS['boards'] 	       = $this->DB->results['boards'];
		$VARS['boardsections']     = Boards::returnBoardCategories($VARS['boards']);
		$VARS['posts']             = $this->DB->results['posts'];

//		$query = mysqli_query($conn, 'SELECT * FROM ibb_boards');
//		$row = mysqli_fetch_assoc($query);
//		echo 'main core application<br />';
		return $VARS;
	}

	public function Go() {
		return 'go';
	}
}