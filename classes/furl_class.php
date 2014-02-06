<?php
/**
 * Class BasicFriendlyURL
 *
 * Retarded version of the friendly URL engine.
 *
 * TODO IMGBB rewrite
 */
class BasicFriendlyURL {

	private static $Templates = array(

		array(
			'app'		=> 'main',
			'mod'		=> 'front',
			'area'		=> 'view',
			'furl'		=> '#/front/(\d*)-#',
			'rfurl'		=> '/front/{$1}'
		),
		array(
			'app'		=> 'boards',
			'mod'		=> 'boardpage',
			'area'		=> 'view',
			'furl'		=> '#/(\w+)?/(\d+)?#',
			'rfurl'		=> '/{$1}/{$2}'
		),

//		'#/front/(\d+)-#' 	=> array( 'app' 		=>	'main',
//									  'mod'			=>	'front',
//									  'area'		=>	'view',
//									  'action'		=>	'$1'
//		),
//		'#/(\w+)?/(\d+)?#'	=> array( 'app'			=>	'boards',
//									  'mod'			=>	'boardpage',
//									  'area'		=>	'view',
//									  'action'		=>	'$1',
//		)
	);

	/**
	 * Here we try to match the friendly-stylized URL to any existing template.
	 *
	 * @param $request string The request URL.
	 */
	public static function translateFURL( $request )
	{
		foreach (self::$Templates as $array)
		{
			if (preg_match($array['furl'], $request, $matches))
			{
				$ret['action'] = $matches[1];
				if (isset($matches[2]))
				{
					$ret['subaction'] = $matches[2];
				}
				$ret['app'] = $array['app'];
				$ret['mod'] = $array['mod'];
				$ret['area'] = $array['area'];
				ibbCore::$fURLArray = $ret;
			}
		}
		return FALSE;
	}

	/**
	 *
	 *
	 * @param string $action
	 * @param string $subaction
	 * @param string $class
	 *
	 * @return string
	 */
	public static function toFURL( $action, $subaction, $class )
	{
		list($app, $mod, $area) = explode('_', $class);

		foreach (self::$Templates as $array)
		{
			if ( $array['app'] == $app && $array['mod'] == $mod && $array['area'] == $area )
			{
				return str_replace(array('{$1}', '{$2}'), array($action, $subaction), $array['rfurl']);
//				return preg_replace($array['rfurl'], $array['rfurl'], $array['furl']);
			}
		}
		return FALSE;
	}

}

