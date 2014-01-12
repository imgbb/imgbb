<?php
/**
 * Created by IntelliJ IDEA.
 * User: Admin
 * Date: 11/4/13
 * Time: 6:58 PM
 * To change this template use File | Settings | File Templates.
 *
 * @BASIC
 * @INCOMPLETE
 */

class boards_boardpage_view implements appCore {

	/**
	 * @var ibbDBCore
	 */
	public $db;
	/**
	 * @var ibbCore
	 */
	public $core;

	/**
	 * @var User
	 */
	public $user;

	/**
	 * @var array
	 */
	public $board_info;

	public $upload;

	/**
	 * I'm still not sure how to handle the post data.
	 * Should I put them in the properties?
	 * That sounds like the most logical thing... but this class
	 * is not designed purely around the idea of post insertion...
	 *
	 * @var int
	 */
	private $sticky = 0;

	/**
	 * Still not sure about this...
	 *
	 * @var bool
	 */
	private $raw = FALSE;

	private $locked = 0;

	private $id;


	/**
	 *
	 */
	public function __construct()
	{
		$this->core 		= 	ibbCore::getInstance();
		$this->db			=	$this->core->DB();
//		$this->request 		=	$this->core->request();
		$this->user			=	$this->core->user();
		$this->board_info 	= $this->db->queryDirect('
			SELECT			ibb_boards.id 				AS board_id,
							ibb_boards.name 			AS `board_name`,
							title,
							ibb_boards.category 		AS board_category,
							ibb_board_categories.id 	AS category_id,
							ibb_board_categories.name 	AS category_name
			FROM			ibb_boards
			LEFT JOIN		ibb_board_categories
			ON				ibb_board_categories.id = ibb_boards.category
			WHERE			ibb_boards.name 					= "'.$this->core->request('action').'"
		')[0];
		$this->user_data 	=	$this->core->data;
		$this->upload		=	$this->core->upload();
	}

	/**
	 * @throws Exception
	 */
	public function init()
	{
//		echo $this->db->queryDirect('(SELECT MAX(A.id) FROM ibb_posts A WHERE A.boardid='.$this->board_info['board_id'].')')[0]['MAX(A.id)'] + 1;
//		if ( $_GET['rebuildfilesizes'] )
//		{
//			$this->db->query('filesizes', '
//				SELECT	id, boardid, file, file_type, file_size
//				FROM	ibb_posts
//			');
//			foreach ($this->db->results['filesizes'] as $file)
//			{
//				$filepath = IBB_ROOT_PATH . '/files/src/'.$file['file'].'.'.$file['file_type'];
//				if (is_file($filepath))
//				{
//					$filesize = filesize($filepath);
//					$this->db->execute('
//						UPDATE	ibb_posts
//						SET		file_size_raw	= '.$filesize.'
//						WHERE	boardid			= '.$file['boardid'].'
//						AND		id				= '.$file['id'].'
//					');
//				}
//			}
//		}

		$this->core->output->vars['boardinfo'] = $this->board_info;
//		$tstart = microtime(true);
//		if ( $this->user->is_staff )
//		{
//			if ( isset($_POST['displaystatus']) )
//			{
//				print_r($this->user->getData());
//			}
//		}
//		print_r($this->user->getUserId());
		// This needs to be in the registry, access should be granted
		// via permissions in database fetched before module loads
		if (!in_array($this->board_info['board_id'], $this->user->getPermissions('boards')[$this->board_info['category_id']]))
		{
			throw new exception('Permission denied exception');
		}

		$this->core->output->vars['postbox'] = TRUE;

		/* Set page title */
		$this->core->output->setTitle($this->board_info['title']);

		$this->core->output->addCSS( 'boards' );

		$this->core->output->addCSS( 'postform' );


		// a temp
		require_once IBB_ROOT_PATH . '/classes/boards/post.php';

		if (!$this->core->request('subaction'))
		{
//			$this->db->query('queries', '
//			SELECT	*
//			FROM	ibb_posts
//			WHERE	boardid		= ' . $this->board_info['board_id'] . '
//			AND		parentid	= 0
//			AND		deleted		= 0
//
//			');

			$this->db->query('parents', '
				SELECT		*
				FROM		ibb_posts
				WHERE		boardid		=	' . $this->board_info['board_id'] . '
				AND			parentid	=	0
				AND			deleted		=	0
				ORDER BY	stickied DESC, bumped DESC
				LIMIT		10
			');

			#Got to be a better way, even though it's temporary...
			$piece = '';

			foreach ($this->db->results['parents'] as $parent)
			{
				// kill me
				if ($piece != '')
					$piece .= ' OR ';
				else
					$piece .= '(';
				$piece .= '( parentid = ' . $parent['id'] . ' AND id >= ' . $parent['latest_preview'] . ')';
			}
			#Make sure the OR is enclsoed in brackets
			$piece .= ')';

//			echo $piece;

			if ($this->db->results['parents'])
			{
				$this->db->query('replies', '
				SELECT		*
				FROM		ibb_posts
				WHERE		boardid		=	' . $this->board_info['board_id'] . '
				AND			deleted		=	0
				AND
					' . $piece . '
				ORDER BY	timestamp	ASC
				LIMIT 30
				');
//				$this->db->results['replies'] = array_reverse($this->db->results['replies']);

				if (isset($this->db->results['replies']))
				{
					foreach ($this->db->results['replies'] as &$post)
					{
						// Not sure why this is needed
//				$thread = array_reverse($thread);
//				foreach ($thread as &$post)
//				{
						$post['display_name']		=	($post['display_tripcode'] == '' && $post['display_name'] == '') ? 'Anonymous' : $post['display_name'];
						$post['timestamp'] 	=	date('jS \of F, Y g:i a', $post['timestamp']);
						$post['message']	=	preg_replace('#\[i\](.*)?\[/i\]#', '<i>\1</i>', $post['message']);
						$post['message']	=	preg_replace('#\[b\](.*)?\[/b\]#', '<font style="font-weight:bold;">\1</font>', $post['message']);
//				}
					}
				}

			}


//			foreach ($this->db->results['parents'] as $parent)
//			{
//				$this->db->queryInLoop('replies', $parent['id'], '
//					SELECT		*
//					FROM		ibb_posts
//					WHERE 		boardid 	=	' . $this->board_info['board_id'] . '
//					AND			parentid	=	' . $parent['id'] . '
//					AND			deleted		=	0
//					ORDER BY	`bumped` DESC
//					LIMIT 		1
//					');
//			}
			/*
			 * thinking to self, enough spamming on skype, todo delete this before commit
			 * ok, on insert op is updated with latest_post
			 * select retrieves first 3 posts from latest_post_id to parent_id that share parent
			 *
			 * x < y & x <= z & xy = ay
			 *
			 * translation
			 *
			 * just grab all the posts newer than the third id that share the op, update on every insert to
			 * the thread. This processing power tradeoff would definitely be worth it
			 *
			 * IT MIGHT JUST WORK
			 * */

//			$this->db->query('parents', '
//			SELECT		*
//			FROM		ibb_posts
//			WHERE 		boardid 	=	' . $this->board_info['board_id'] . '
//			AND 		parentid	=	0
//			AND			deleted		=	0
//			AND			stickied	=	0
//			ORDER BY	`bumped` DESC
//			LIMIT 		' . (10 - count($this->db->results['stickies']))
//			);
//			foreach ($this->db->results['parents'] as $parent)
//			{
//				$this->db->queryInLoop('replies', $parent['id'], '
//					SELECT		*
//					FROM		ibb_posts
//					WHERE 		boardid 	=	' . $this->board_info['board_id'] . '
//					AND			parentid	=	' . $parent['id'] . '
//					AND			deleted		=	0
//					ORDER BY	`bumped` DESC
//					LIMIT 		3'
//				);
//			}

//			$this->db->results['parents'] = array_merge($this->db->results['stickies'], $this->db->results['parents']);
//			foreach ( $this->db->results['parents'] as $parent )
//			{
//				$this->db->queryInLoop('repliescount', $parent['id'], '
//					SELECT		COUNT(*)
//					FROM 		ibb_posts
//					WHERE		boardid			=	' . $this->board_info['board_id'] . '
//					AND			parentid		=	' . $parent['id']
//				);
//			}




			/* Add the macro  */
			$this->core->output->addMacro('board', 'boards.xhtml');
//			print_r($this->db->results['replies']);

			////////////////////////////j/////
			//// Prepare dynamic variables
			/////////////////////////////////
//test
			/***** Temporary parsing, all parsing will be moved to a parsing object */
			foreach ($this->db->results['parents'] as &$post)
			{
				$post['timestamp'] 	=	date('D, M jS, Y g:i a', $post['timestamp']);
				$post['display_name']		=	($post['display_tripcode'] == '' && $post['display_name'] == '') ? 'Anonymous' : $post['display_name'];
//				$post['message']	=	stripslashes($post['message']);
				$post['message']	=	preg_replace('#\[i\](.*)?\[/i\]#', '<i>\1</i>', $post['message']);
				$post['message']	=	preg_replace('#\[b\](.*)?\[/b\]#', '<font style="font-weight:bold;">\1</font>', $post['message']);
//				$post['message']	=	strlen($post['message']) > 500 ? substr($post['message'], 0, 500) . ' <b>&hellip;</b>' : $post['message'];
			}

			/**** */

			/* Load SQLs into the vars */
//			foreach ( $this->db->results as $queryk => $query )
//			{
//				$this->core->output->vars[$queryk] = $query;
//			}

			// b temp
			if (isset($this->db->results['parents']))
			{
				$this->core->output->vars['parents'] 	= new post($this->db->results['parents']);
			}
//			foreach ($this->core->output->vars['parents'] as $parent)
//			{
			if (isset($this->db->results['replies']))
			{
				$this->core->output->vars['replies'] = new post($this->db->results['replies']);
			}
//			}

		} else
		{
//			$this->viewSingleThread( $this->board_info['board_id'], $this->core->request('subaction'));
			$this->db->query('posts', '
			SELECT 	*
			FROM	ibb_posts
			WHERE	boardid		= 	'.$this->board_info['board_id'].'
			AND 	id			= 	'.$this->core->request('subaction').'
			AND		deleted		=	0
			UNION
			SELECT	*
			FROM	ibb_posts
			WHERE	boardid		=	'.$this->board_info['board_id'].'
			AND 	parentid	=	'.$this->core->request('subaction').'
			AND		deleted		=	0
		');

			/* Load SQLs into the vars */
//			foreach ( $this->db->results as $queryk => $query )
//			{
//				$this->core->output->vars[$queryk] = $query;
//			}

			foreach ($this->db->results['posts'] as &$post)
			{
				$post['timestamp'] 		=	date('D, M jS, Y g:i a', $post['timestamp']);
				$post['display_name']	=	($post['display_tripcode'] == '' && $post['display_name'] == '') ? 'Anonymous' : $post['display_name'];
			}

			// c temp
			$this->core->output->vars['replies']	= new post($this->db->results['posts']);

			// d temp
			$this->core->output->vars['inthread']	= TRUE;

			// TODO IMGBB deal with this
			$this->core->output->vars['parents'] 	= new post(array($this->db->results['posts'][0]));


			$this->core->output->addMacro('board', 'boards.xhtml');
		}

//		print_r($this->core->output->vars['replies']['36460509']);

//		echo '<textarea rows=600 cols=180>';
//		print_r($this->core->output->vars['replies']);
//		echo '</textarea>';
//		foreach ($this->core->output->vars[')
//		$this->core->output->vars['replies']	= new post($this->core->output->vars['replies']);
		// e temp
//		foreach ($this->core->output->vars['replies'] as $key => &$reply)
//		{
//			$reply = new post($this->core->output->vars['replies'][$key]);
//		}
//		foreach ($this->core->output->vars['parents'] as $k1 => $parent)
		{
//			echo 'i am the parent (key: ' . $k1 . ')';
//			print_r($this->core->output->vars['replies'][$parent['id']]);
//			echo '<hr />';

//			foreach ($this->core->output->vars['replies'][$parent['id']] as $k2 => $parentreplies)
//			{
//				echo 'i am the object(key ' . $k2 . ' parentkey: '. $parent['id'] .')';
//				$this->core->output->vars['replies'][$parent['id']] = new post($this->core->output->vars['replies'][$parent['id']]);
//			}
		}
//		print_r($this->core->output->vars['replies']['36460004']);
//		$exthread 	= new post($this->core->output->vars['replies']['36460004']);
//		$ex2		= new post($this->core->output->vars['parents']);
//		foreach ($exthread as $post)
//		{
//			print_r($post->posts);
//		}
//		$this->core->output->vars['replies'] = new post
//		print_r($this->core->output->vars['parents']);
//		echo '<hr /><hr /><hr /><hr />';
//		echo '<textarea>';
//		print_r($this->core->output->vars['replies']);
//		echo '</textarea>';
//		echo $this->core->output->vars['parents']->current()[0][0]['name'];

//		var_dump($this->db->results['parents'][0]['message']);
	}

	public function process()
	{

		if (!in_array($this->board_info['board_id'], $this->user->getPermissions('boards')[$this->board_info['category_id']]))
		{
			throw new Exception('permission denied exceptiofdsf');
		}

		$this->db->query('latest_preview','
			SELECT		id
			FROM		ibb_posts
			WHERE		boardid		=	' . $this->board_info['board_id'] . '
			AND			parentid	=	' . intval($_POST['subaction']) . '
			ORDER BY	`timestamp` DESC
			LIMIT 2
			');
		if ( $_POST['subaction'] != 0 )
		{
			$this->db->query('parent_data','
				SELECT		locked
				FROM		ibb_posts
				WHERE		boardid		=	' . $this->board_info['board_id'] . '
				AND			id			=	' . intval($_POST['subaction']) . '
				ORDER BY	timestamp	DESC
				LIMIT 1
			', SINGLE_RESULT_QUERY);
		}

		// so gimmicky, pls rewrite TODO IMGBB
		// I've already forgotten, what is this gimmick meant to even do? #killme
		if (empty($this->db->results['latest_preview']))
			$this->db->results['latest_preview'][0]['id'] = 0;
		else
			$this->db->results['latest_preview'] = array_reverse($this->db->results['latest_preview']);

		if ( $this->user->registered )
		{
			if ( $this->user->names[$_POST['name']]['rank'])
			{
				$this->user->rank = $this->user->getData()['rank'];
			}

			if ( $this->user->is_staff )
			{
				if ( isset ( $_POST['displaystatus'] ) )
				{
					$this->user->rank = $this->user->getData()['user_rank'];
				}

				if ( isset( $_POST['sticky'] ) )
				{
					if ( $this->core->request('subaction') == 0)
					{
						$this->sticky = 1;
					}
					else
					{
						$this->db->execute('UPDATE ibb_posts SET stickied=1 WHERE id='.$this->core->request('subaction').' AND parentid=0');
					}
				}

				if ( isset( $_POST['lock'] ) )
				{
					if ($this->core->request('subaction') == 0)
					{
						$this->locked = 1;
					}
					else
					{
						$this->db->execute('UPDATE ibb_posts SET locked=1 WHERE id='.$this->core->request('subaction').' AND parentid=0');
					}
				}

				if ( isset( $_POST['rawhtml'] ) )
				{
					$this->raw = TRUE;
				}
			}

			if ( array_key_exists($_POST['name'], $this->user->names) )
			{
				$this->user->display_name = $this->user->names[$_POST['name']]['display_name'];
				$this->user->display_trip = $this->user->names[$_POST['name']]['display_trip'];

				if ( $this->user->names[$_POST['name']]['rank'] > 0 )
				{
					$this->user->rank = $this->user->names[$_POST['name']]['rank'];
				}
			}
		}
		else
		{
			list($this->user->display_name, $this->user->display_trip) = $this->calculateNameAndTripcode($_POST['postername']);
		}

		if ($this->db->results['parent_data']['locked'] && $this->user->rank == 0)
		{
			throw new Exception('(Notice-level exception error via AJAX): Thread is locked.');
		}

		if ( $_POST['body'] )
		{
			htmlentities($_POST['body']);
		}

		if ( isset($_POST['subject']) )
		{
			htmlentities($_POST['subject']);
		}

		//csrf


//		$filename = 0;
//		$filetype = '""';
//		$img_height = 0;
//		$img_width = 0;
//		$thumb_height = 0;
//		$thumb_width = 0;
//		$file_original = '""';
		if ($_FILES['file'])
		{
//			throw new exception(print_r($_FILES));
//			 use ibbloader to require the upload class...
//			list($filename, $filetype, $img_height, $img_width, $thumb_height, $thumb_width, $file_original) = $this->core->upload()->DoFile( intval($_POST['subaction'] ));
			$this->core->upload()->DoFile( intval($_POST['subaction']) );
		}

		#tmp
		if ($_POST['body'] == '' && $this->upload->filename == 0)
			throw new Exception('file or msg pls exception');
		else
		{
			if (!$this->raw)
			{
				$_POST['body'] = IBBText::postParser($_POST['body']);
			}
			else
			{
				$_POST['body'] = IBBText::escapeQuotes($_POST['body']);
			}
//			$_POST['subject'] = IBBText::postParser($_POST['subject']);
		}

		# Very temporary! Can't leave this here, too risky
//		$this->db->query('id', 'SELECT MAX(id) AS id FROM ibb_posts WHERE boardid='.$this->board_info['board_id'], SINGLE_RESULT_QUERY);

//		print_r($this->upload->filename);

		#tmp TODO IMGBB
		$this->id = $this->db->queryDirect('SELECT MAX(id) FROM ibb_posts WHERE boardid='.$this->board_info['board_id'])[0]['MAX(id)'] + 1;

		#tmp
		$abc = 0;

		if ($this->db->prepare('
			INSERT INTO		ibb_posts
			(boardid, id, parentid, userid, latest_preview, message, display_name, display_tripcode
			,subject, email, timestamp, bumped, rank, file, file_type, image_w, image_h, thumb_h, thumb_w
			,file_original, file_size, file_size_raw, stickied, locked)
			VALUES
			(?, ?, ?, ?, ?, ?, ?, ?
			,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
			,?, ?, ?, ?, ?)
		'))
		{
			# this... will be very... very... hard... to touch.
			$this->db->qobject->bind_param(
				'iiiiisssssiiiisiiiisssii',

				 $this->board_info['board_id'], $this->id, $_POST['subaction'], $this->user->getUserId(), $abc, $_POST['body']
				,$this->user->display_name, $this->user->display_trip, $_POST['subject'], $_POST['email'], time(), time()
				,$this->user->rank, $this->upload->filename, $this->upload->filetype, $this->upload->src_width
				,$this->upload->src_height, $this->upload->thumb_height, $this->upload->thumb_width, $this->upload->filename_original
				,$this->upload->file_size, $this->upload->file_size_raw, $this->sticky, $this->locked
			);
		}

//		$this->db->buildSafeQuery(array(
//				'type'		=> array('INSERT' => 'ibb_posts'),
//				'columns'	=> array('boardid'
//									,'id'
//									,'parentid'
//									,'userid'
//									,'latest_preview'
//									,'message'
//									,'display_name'
//									,'display_tripcode'
//									,'subject'
//									,'email'
//									,'timestamp'
//									,'bumped'
//									,'rank'
//									,'file'
//									,'file_type'
//									,'image_w'
//									,'image_h'
//									,'thumb_h'
//									,'thumb_w'
//									,'file_original'
//									,'file_size'
//									,'file_size_raw'
//									,'stickied'
//				),
//				'values'	=> array($this->board_info['board_id']
//									,'(SELECT MAX(A.id) FROM ibb_posts A WHERE A.boardid='.$this->board_info['board_id'].') +1'
//									,intval($_POST['subaction'])
//									,$this->user->getUserId()
//									,0
//									,'"' .$_POST['body']. '"'
//									,'"' .$this->user->display_name. '"'
//									,'"' .$this->user->display_trip. '"'
//									,'"' .$_POST['subject']. '"'
//									,'"'.$_POST['email'].'"'
//									,time()
//									,time()
//									,intval($this->user->rank)
//									,$this->upload->filename
//									,$this->upload->filetype
//									,$this->upload->src_width
//									,$this->upload->src_height
//									,$this->upload->thumb_height
//									,$this->upload->thumb_width
//									,$this->upload->filename_original
//									,$this->upload->file_size
//									,$this->upload->file_size_raw
//									,$this->sticky
//				)
//			)
//		);

//		$this->id = $this->db->queryDirect('(SELECT MAX(A.id) FROM ibb_posts A WHERE A.boardid='.$this->board_info['board_id'].')')[0]['MAX(A.id'] + 1;

		try
		{
			$this->db->qobject->execute();
		}
		catch (Exception $e)
		{
			throw $e;
		}

		if ($this->db->results['latest_preview'][0]['id'] == 0)
		{
			$this->db->execute('
			UPDATE ibb_posts
			SET		 latest_preview	= 	'.$this->db->instance()->insert_id.'
					,bumped			=	'.time().'
			WHERE 	boardid 		= 	'.$this->board_info['board_id'].'
			AND		id				=	'.intval($_POST['subaction']).'
		');
		}
		else
		{
			$this->db->execute('
			UPDATE	ibb_posts
			SET		 latest_preview	=	'.$this->db->results['latest_preview'][0]['id'].'
					,replycount		=	replycount+1
					,bumped			=	'.time().'
			WHERE	boardid			=	'.$this->board_info['board_id'].'
			AND		id				=	'.intval($_POST['subaction']).'
			');
		}

		$this->core->hotfixAddRequest('action', 'q');
	}

	/**
	 * Thanks, Kusabaa X and Mithent. TODO ALPHA full rewrite
	 *
	 * @param $post_name
	 *
	 * @return array
	 */
	function calculateNameAndTripcode($post_name) {

		if(preg_match("/(#|!)(.*)/", $post_name, $regs)){
			$cap = $regs[2];

			/* TruthL: I don't have a drop of experience in multibyte functions. Will have to look into it */
			if (function_exists('mb_convert_encoding')) {
				$recoded_cap = mb_convert_encoding($cap, 'SJIS', 'UTF-8');
				if ($recoded_cap != '') {
					$cap = $recoded_cap;
				}
			}

			if (strpos($post_name, '#') === false) {
				$cap_delimiter = '!';
			} elseif (strpos($post_name, '!') === false) {
				$cap_delimiter = '#';
			} else {
				$cap_delimiter = (strpos($post_name, '#') < strpos($post_name, '!')) ? '#' : '!';
			}

			if (preg_match("/(.*)(" . $cap_delimiter . ")(.*)/", $cap, $regs_secure)) {
				$cap = $regs_secure[1];
				$cap_secure = $regs_secure[3];
				$is_secure_trip = true;
			} else {
				$is_secure_trip = false;
			}

			$tripcode = '';
			if ($cap != '') {
				/* From Futabally */
				$cap = strtr($cap, "&amp;", "&");
				$cap = strtr($cap, "&#44;", ", ");
				$salt = substr($cap."H.", 1, 2);
				$salt = preg_replace("/[^\.-z]/", ".", $salt);
				$salt = strtr($salt, ":;<=>?@[\\]^_`", "ABCDEFGabcdef");
				$tripcode = substr(crypt($cap, $salt), -10);
			}

			if ($is_secure_trip) {
				if ($cap != '') {
					$tripcode .= '!';
				}

				$secure_tripcode = md5($cap_secure . IBB_REPLACE_THIS);
				if (function_exists('base64_encode')) {
					$secure_tripcode = base64_encode($secure_tripcode);
				}
				if (function_exists('str_rot13')) {
					$secure_tripcode = str_rot13($secure_tripcode);
				}

				$secure_tripcode = substr($secure_tripcode, 2, 10);

				$tripcode .= '!' . $secure_tripcode;
			}

			$name = preg_replace("/(" . $cap_delimiter . ")(.*)/", "", $post_name);


			return array($name, $tripcode);
		}

		return array($post_name, '');
	}

	/**
	 * should probably put this back in init()
	 *
	 * nvm, later, one thing at a time
	 *
	 * @param      $board
	 * @param      $thread
	 * @param null $user
	 */
//	public function viewSingleThread( $board, $thread, $user = NULL)
//	{
//		$this->db->query('posts', '
//			SELECT 	*
//			FROM	ibb_posts
//			WHERE	boardid		= 	'.$board.'
//			AND 	id			= 	'.$thread.'
//			AND		deleted		=	0
//			UNION
//			SELECT	*
//			FROM	ibb_posts
//			WHERE	boardid		=	'.$board.'
//			AND 	parentid	=	'.$thread.'
//			AND		deleted		=	0
//		');
//
//		/* Load SQLs into the vars */
//		foreach ( $this->db->results as $queryk => $query )
//		{
//			$this->core->output->vars[$queryk] = $query;
//		}
//
//		foreach ($this->core->output->vars['posts'] as &$post)
//		{
//			$post['timestamp'] 		=	date('D, M jS, Y g:i a', $post['timestamp']);
//			$post['display_name']	=	($post['display_tripcode'] == '' && $post['display_name'] == '') ? 'Anonymous' : $post['display_name'];
//			$post['message']		=	preg_replace('#\[i\](.*)?\[/i\]#', '<i>\1</i>', $post['message']);
//			$post['message']		=	preg_replace('#\[b\](.*)?\[/b\]#', '<font style="font-weight:bold;">\1</font>', $post['message']);
//		}
//
//		 c temp
//		$this->core->output->vars['replies']	= new post($this->core->output->vars['posts']);
//
//		 d temp
//		$this->core->output->vars['inthread']	= TRUE;
//
//		 TODO IMGBB deal with this
//		$this->core->output->vars['parents'] 	= array($this->core->output->vars['replies']->posts[0]);
//
//
//		$this->core->output->addMacro('board', 'boards.xhtml');
//	}
//
}