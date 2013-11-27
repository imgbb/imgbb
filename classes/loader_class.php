<?php
/**
 * Created by IntelliJ IDEA.
 * User: Admin
 * Date: 11/11/13
 * Time: 3:31 AM
 * To change this template use File | Settings | File Templates.
 */

/**
 * Class ibbLoader
 *
 * Skeleton of undeveloped class, needs a lot more work and thinking
 */
class ibbLoader {

	private static $classes;

	public static function loadStaticClass( $key )
	{

	}


	/**
	 * the fuck am i doing
	 *
	 * @param $app
	 * @param $item
	 *
	 * @return string
	 */
	public static function returnItemWithPath ( $app, $item )
	{
		$path = IBB_ROOT_PATH . '/app/' . $app;

		switch ( $item ) {
			case 'sql_file':
				if ( is_file( $path . '/sql.php' ) )
				{
					require_once $path . '/sql.php';
					return $app.'_sql_funcs';
				}
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Loads a class from $classes, or creates new instance
	 *
	 * @param $class
	 */
	public static function loadDynamicClass( $key )
	{
		if ( !isset( self::$classes[$key]) )
		{
			switch ( $key )
			{
				case 'furl_class';
					require_once IBB_ROOT_PATH  . '/classes/furl_class.php';
			}
		}
		else
		{
			return self::$classes[$key];
		}
	}

	/**
	 * Load the sql.php file.
	 *
	 * @param 	$app		string
	 * @param 	$function	string
	 */
	public static function loadForeignSQL( $app, $function, $arg )
	{
		return self::returnItemWithPath( $app, 'sql_file' );
	}
}