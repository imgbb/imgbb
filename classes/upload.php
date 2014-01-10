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
	public $filename_original;

	/**
	 * @return $this
	 */
	public function init()
	{
		$this->src_width = 0;
		$this->src_height = 0;
		$this->thumb_width = 0;
		$this->thumb_height = 0;
		$this->filetype = "''";
		$this->filename = "''";
		$this->filename_original = "''";

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
//			error_log($matches[0]);
			new Exception('(Notice-level error exception): File type is not allowed.');
		}

		$this->filename = time() . substr(microtime(), 2, 2);

		$this->dir 		= IBB_ROOT_PATH . '/files/';
		$this->file 	= IBB_ROOT_PATH . '/files/src/' . $this->filename;
		$this->thumb 	= IBB_ROOT_PATH . '/files/thumb/' . $this->filename . 's.' . $this->filetype;

//		throw new Exception($this->file);
//		throw new Exception($this->filename);

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

//		print_r($_FILES);
//		throw new Exception();
//		throw new Exception($_FILES['file']);

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

		$this->filetype				= '"'.$this->filetype.'"';
		$this->filename_original 	= '"'.$_FILES['file']['name'].'"';


//		throw new Exception($this->src_height);



//		if (!is_file($_FILES['file']['tmp_name']) || !is_readable($_FILES['file']['tmp_name']))
//		{
//			exit('There was an error during the transfer period. (ERROR: CORRUPTED (IF/IS))');
//		}
//		else
//		{
//			if ($this->file_type == '.jpg' || $this->file_type == '.gif' || $this->file_type == '.png') {
//				if (!@getimagesize($_FILES['file']['tmp_name'])) {
//					exit('There was an error during the transfer period. (ERROR: CORRUPTED (GIS))');
//				}
//			}
//		}



		//createThumbnail($file);

//		return array('"'.$this->filename.'"', '"'.$this->file_type.'"', $this->imgHeight, $this->imgWidth, $this->thumb_height, $this->thumb_width, '"'.$_FILES['file']['name'].'"');
//		return $this;

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
}