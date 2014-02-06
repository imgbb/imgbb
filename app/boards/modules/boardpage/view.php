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
		$this->user			=	$this->core->user();
		$this->board_info 	= $this->db->queryDirect('
			SELECT			ibb_boards.id 				AS board_id,
							ibb_boards.name 			AS `board_name`,
							ibb_boards.title,
							ibb_boards.threadperpage,
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

		/* Temporary permissions verification */
		if (!in_array($this->board_info['board_id'], $this->user->getPermissions('boards')[$this->board_info['category_id']]))
		{
			throw new exception('Permission denied exception');
		}

		/* Metadata */
		$this->core->output->setTitle($this->board_info['title']);
		$this->core->output->addCSS( 'boards' );
		$this->core->output->addCSS( 'postform' );
		$this->core->output->addCSS( 'quickmod' );
		$this->core->output->addJS( 'jquery' );
		$this->core->output->addJS( 'boards' );
		$this->core->output->vars['postbox'] = TRUE;
		$this->core->output->vars['boardinfo'] = $this->board_info;


		/* If we're not in a thread... */
		if (!$this->core->request('subaction'))
		{
			$this->db->query('parents', '
				SELECT		*
				FROM		ibb_posts
				WHERE		boardid		=	' . $this->board_info['board_id'] . '
				AND			parentid	=	0
				AND			deleted		=	0
				ORDER BY	stickied DESC, bumped DESC
				LIMIT		' . $this->board_info['threadperpage'] . '
			');

			#Got to be a better way, even though it's temporary...
			$piece = '';

			foreach ($this->db->results['parents'] as $parent)
			{
				# kill me
				if ($piece != '')
					$piece .= ' OR ';
				else
					$piece .= '(';
				$piece .= '( parentid = ' . $parent['id'] . ' AND id >= ' . $parent['latest_preview'] . ')';
			}
			#Make sure the OR is enclsoed in brackets
			$piece .= ')';

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
				LIMIT 45
				');

				if (isset($this->db->results['replies']))
				{
					foreach ($this->db->results['replies'] as &$post)
					{
						$post['display_name']		=	($post['display_tripcode'] == '' && $post['display_name'] == '') ? 'Anonymous' : $post['display_name'];
						$post['timestamp'] 	=	date('jS \of F, Y g:i a', $post['timestamp']);
						$post['message']	=	preg_replace('#\[i\](.*)?\[/i\]#', '<i>\1</i>', $post['message']);
						$post['message']	=	preg_replace('#\[b\](.*)?\[/b\]#', '<font style="font-weight:bold;">\1</font>', $post['message']);
					}
				}

			}

			/* Add the macro  */
			$this->core->output->addMacro('board', 'boards.xhtml');

			/* Temporary parsing, all parsing will be moved to a parsing object */
			foreach ($this->db->results['parents'] as &$post)
			{
				$post['timestamp'] 		=	date('D, M jS, Y g:i a', $post['timestamp']);
				$post['display_name']	=	($post['display_tripcode'] == '' && $post['display_name'] == '') ? 'Anonymous' : $post['display_name'];
			}

			# b temp
			if (isset($this->db->results['parents']))
			{
				$this->core->output->vars['parents'] 	= new post($this->db->results['parents']);
			}

			if (isset($this->db->results['replies']))
			{
				$this->core->output->vars['replies'] = new post($this->db->results['replies']);
			}

			/* Pagination data */
			$this->core->output->vars['totalpages'] = round($this->db->queryDirect('
				SELECT COUNT(*) FROM ibb_posts WHERE parentid = 0 AND deleted = 0 AND boardid = ' .
				$this->board_info['board_id'])[0]['COUNT(*)'] / $this->board_info['threadperpage']);

		}
		/* We're in a thread! */
		else
		{
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

			foreach ($this->db->results['posts'] as &$post)
			{
				$post['timestamp'] 		=	date('D, M jS, Y g:i a', $post['timestamp']);
				$post['display_name']	=	($post['display_tripcode'] == '' && $post['display_name'] == '') ? 'Anonymous' : $post['display_name'];
			}

			# c temp
			$this->core->output->vars['replies']	= new post($this->db->results['posts']);

			# d temp
			$this->core->output->vars['inthread']	= TRUE;

			# TODO IMGBB deal with this
			$this->core->output->vars['parents'] 	= new post(array($this->db->results['posts'][0]));


			$this->core->output->addMacro('board', 'boards.xhtml');
		}
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

		# so gimmicky, pls rewrite TODO IMGBB
		# I've already forgotten, what is this gimmick meant to even do? #killme
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
			else
			{
				list($this->user->display_name, $this->user->display_trip) = $this->calculateNameAndTripcode($_POST['postername']);
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

		#csrf


		if ($_FILES['file']['size'])
		{
			$this->core->upload()->DoFile( intval($_POST['subaction']) );
		}

		#tmp
		if ($_POST['body'] == '' && $this->upload->filename == 0)
		{
			throw new Exception('file or msg pls exception');
		}
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
		}

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

//		$this->core->hotfixAddRequest('action', 'q');
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
}