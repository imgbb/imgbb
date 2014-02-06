<?php
/**
 * This class is entirely temporary. I really don't like it.
 *
 * Class IBBText
 *
 */
class IBBText
{
	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * @param string		$body
	 *
	 * @return string
	 */
	public static function PostParser($body)
	{
		#ew
		$body = trim($body);
		$body = htmlspecialchars($body, ENT_QUOTES);
		$body = self::CreateLink($body);
		$body = self::BBCode($body);
		$body = nl2br($body, TRUE);
		$body = self::BuildPostLink($body);
		return $body;
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function escapeQuotes( $text )
	{
		$text = str_replace("'", "\\'", $text);
//		$text = str_replace('"', '\\"', $text);

		return $text;
	}

	/**
	 * @param $text
	 *
	 * @return string
	 */
	public static function BuildPostLink($text)
	{
		return preg_replace('#>>(\d)*#', '<a href="http://www.imgbb.net/index.php?/q/', $text);
	}

	/**
	 * @param string $text
	 */
	public static function buildPostLink_callback($text)
	{

	}

	/**
	 * Turn http:// URLs from plaintext to <a> elements.
	 *
	 * @param $text
	 *
	 * @return mixed
	 */
	public static function CreateLink($text)
	{
		$text =  preg_replace('#(?:http(s)?://)(w*?\.)?([\da-z-\.]+\.[a-z\.]{2,6})*(/[a-z/-_]*)#', '<a href=\"http\1://\2\3\4\">http\1://\2\3\4</a>', $text);
		return $text;
	}

	/**
	 * temp
	 *
	 * @param $text
	 *
	 * @return mixed
	 */
	public static function BBCode( $text )
	{
		$bbcodes = array(	'[b]',	'[/b]',
							'[i]',	'[/i]',
							'[u]',	'[/u]',
							'[s]',	'[/s]',
							'[aa]',	'[/aa]'
		);
		$html = array(	'<span style=\"font-weight:bold;\">', 								'</span>',
						'<span style=\"font-style:italic;\">',								'</span>',
						'<span style=\"border-bottom:1px solid\">', 						'</span>',
						'<span style=\"text-decoration:line-through;\">', 					'</span>',
						'<div style=\"font-family: Mona,\'MS PGothic\' !important;\">', 	'</div>'

		);
		$text = str_replace($bbcodes, $html, $text);
		return $text;
	}
}