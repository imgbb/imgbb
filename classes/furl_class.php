<?php
/**
 * Class BasicFriendlyURL
 *
 * Retarded version of the friendly URL engine.
 */
class BasicFriendlyURL {

	private static $Templates = array(

		'#/front/(\d+)-#' 	=> array( 'app' 	=>	'main',
									  'mod'		=>	'view',
									  'area'	=>	'categories',
									  'action'	=>	'$1'
		),
		'#/(\w+)?/(\d+)?#'	=> array( 'app'		=>	'boards',
									  'mod'		=>	'boardpage',
									  'area'	=>	'view',
									  'action'	=>	'1'
		)
	);

	/**
	 * Here we try to match the friendly-stylized URL to any existing template.
	 *
	 * @param $request string The request URL.
	 */
	public static function findpath($request)
	{
		foreach (self::$Templates as $regex => $array)
		{
			if (preg_match($regex, $request, $matches))
			{
				$array['action'] = $matches[1];
				if (isset($matches[2]))
				{
					$array['subaction'] = $matches[2];
				}
				ibbCore::$fURLArray = $array;
				break;
			}
		}
	}
}

