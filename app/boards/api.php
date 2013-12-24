<?php
/**
 * Created by IntelliJ IDEA.
 * User: Admin
 * Date: 10/11/13
 * Time: 3:31 AM
 * To change this template use File | Settings | File Templates.
 */
class Boards_API {

	/**
	 * //10/15/2013
	 * Returns an array with all board categories.
	 *
	 * This function returns an array, with normal numerical keys, which
	 * contains all the SELECTed categories. This requires an argument containing
	 * a query on the boards table, with a LEFT JOIN on the entire categories table.
	 * This function exists merely so that only one query needs to be run.
	 * It may still be deleted for its redundant nature.
	 *
	 * //todo IMGBB 11/27/2013 this doesn't belong in boards anyway, kill this
	 *
	 * @param	$results	array
	 * @return	array
	 */
	static function returnBoardCategories( $results )
	{
		$ret = array();

		foreach ( $results as $board )
		{
			if ( !in_array( $board['category_name'], $ret ) )
				$ret[$board['category_id']] = $board['category_name'];
		}

		return $ret;
	}


	/**
	 * recursive in_array, http://php.net/in_array
	 *
	 * //todo BETA replace
	 *
	 * @param $needle
	 * @param $haystack
	 *
	 * @return bool
	 */
	static function in_arrayr($needle, $haystack) {
		echo "searching $needle in\n\n";
		var_dump($haystack);
		echo "\n\n";
		foreach ($haystack as $value) {
			echo "beginning foreach for:\n";
			var_dump($value);

			if ($needle == $value)
			{
				return true;
			}
			elseif (is_array($value))
			{
				return self::in_arrayr($needle, $value);
			}
		}
		return false;
	}
}