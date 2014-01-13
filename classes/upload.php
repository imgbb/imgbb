<?php
/**
 * Class upload
 */
class upload
{
	private $dir;
	private $file;
	private $thumb;
	private $image_dimensions;
	private $maxw;
	private $maxh;

	public $src_width;
	public $src_height;
	public $thumb_width;
	public $thumb_height;
	public $filetype;
	public $filename;
	public $file_size;
	public $file_size_raw;
	public $filename_original;

	/**
	 * @return $this
	 */
	public function init()
	{
		$this->src_width 			= 0;
		$this->src_height 			= 0;
		$this->thumb_width 			= 0;
		$this->thumb_height 		= 0;
		$this->file_size_raw 		= 0;

		return $this;
	}

	/**
	 *
	 * Never done this before, just testing out the logic
	 */
	public function DoFile( $parent )
	{
		if (preg_match('#.*(jpg|gif|png)#', $_FILES['file']['name'], $matches))
		{
			$this->filetype = $matches[1];
		}
		else
		{
			new Exception('(Notice-level error exception): File type is not allowed.');
		}

		$this->filename 		= time() . substr(microtime(), 2, 2);
		$this->file_size_raw 	= filesize($_FILES['file']['tmp_name']);
		$this->file_size 		= $this->formatBytes($this->file_size_raw);

		$this->dir 				= IBB_ROOT_PATH . '/files/';
		$this->file 			= IBB_ROOT_PATH . '/files/src/' . $this->filename;
		$this->thumb 			= IBB_ROOT_PATH . '/files/thumb/' . $this->filename . 's.' . $this->filetype;

		switch ($_FILES['file']['error'])
		{
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_INI_SIZE:
			{
				throw new Exception('(Warning-level error exception) Server\'s maximum file upload limit was exceeded.
					Contact your Server Administrator if this is unintended.');
			}
			case UPLOAD_ERR_FORM_SIZE:
			{
				throw new Exception('(Notice-level error exception via AJAX) Maximum file size limit reached.');
			}
			case UPLOAD_ERR_PARTIAL:
			{
				error_log('UPLOAD_ERR_PARTIAL(IBBCODE:404) was called during upload procedure. Debugging information:');
				throw new Exception('(Fatal-level error exception) There was an error during the upload procedure. ERROR CODE: 404');
			}
			case UPLOAD_ERR_NO_FILE:
			{
				error_log('UPLOAD_ERR_NO_FILE(IBBCODE:405) was called during upload procedure. Debugging information:');
				throw new Exception('(Fatal-level error exception) We could not find the file. ERROR CODE: 405');
			}
			case UPLOAD_ERR_NO_TMP_DIR:
			{
				error_log('UPLOAD_ERR_NO_TMP_DIR(IBBCODE:406) was called during upload procedure. Debugging information:\n
					The tmp directory cannot be used. Please contact your host for advice!');
				throw new Exception('(Fatal-level error exception) We cannot locate the temp directory. ERROR CODE: 406');
				break;
			}
			case UPLOAD_ERR_CANT_WRITE:
			{
				error_log('UPLOAD_ERR_CANT_WRITE(IBBCODE:407) was called during upload procedure. Debugging information:\n
					We can\'t write to disk, probably the temp folder. This is a very unusual error, please contact your host for advice!');
				throw new Exception('(Fatal-level error exception) There was an error during upload procedure. ERROR CODE: 407');
				break;
			}
			case UPLOAD_ERR_EXTENSION:
			{
				error_log('UPLOAD_ERR_EXTENSION(IBBCODE:408) was called during upload procedure. Debugging information:\n
					The file upload was unexpectedly halted by a PHP extension, but we have no way to ascertain which one. Please contact your host for advice!
				');
				throw new Exception('(Fatal-level error exception) There was an error during upload procedure. ERROR CODE: 408');
			}
		}

		move_uploaded_file($_FILES['file']['tmp_name'], $this->file . '.' . $this->filetype);

		$this->image_dimensions = getimagesize($this->file . '.' . $this->filetype);
		$this->src_width = $this->image_dimensions[0];
		$this->src_height = $this->image_dimensions[1];

		if ($parent === 0)
		{
			$this->maxw = 200;
			$this->maxh = 600;
		}
		else
		{
			$this->maxw = 125;
			$this->maxh = 125;
		}

		list($this->thumb_width, $this->thumb_height) = $this->scaleSize($this->src_width, $this->src_height, $this->maxw, $this->maxh);

		$this->filename_original 	= $_FILES['file']['name'];

		#create thumbnail here
	}

	/**
	 * @param $file
	 */
	public function createThumbnail( $file )
	{

	}

	/**
	 * @param $width
	 * @param $height
	 * @param $max_w
	 * @param $max_h
	 *
	 * @return array
	 */
	function scaleSize($width, $height, $max_w, $max_h) {
		if ($width <= $max_w && $height <= $max_h)
			return array($width, $height);
		if ($width / $height > $max_w / $max_h)
			$ratio = $width / $max_w;
		else
			$ratio = $height / $max_h;
		return array(intval(ceil($width / $ratio)), intval(ceil($height / $ratio)));
	}

	/**
	 * Thanks, php.net
	 *
	 * @param string	$bytes
	 * @param int		$precision
	 *
	 * @return string
	 */
	function formatBytes($bytes, $precision = 2) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		 $bytes /= pow(1024, $pow);
//		$bytes /= (1 << (10 * $pow));

		return round($bytes, $precision) . ' ' . $units[$pow];
	}
}