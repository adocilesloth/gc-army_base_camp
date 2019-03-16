<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

function sql_abc_clean($string)
{
	//Escape punctuation
	$string = preg_quote($string);
	$string = str_replace("~", "\\~", $string);
	$string = str_replace("`", "\\`", $string);
	$string = str_replace("@", "\\@", $string);
	$string = str_replace("#", "\\#", $string);
	$string = str_replace("%", "\\%", $string);
	$string = str_replace("&", "\\&", $string);
	$string = str_replace(";", "\\;", $string);
	$string = str_replace("'", "\\'", $string);
	$string = str_replace('"', '\\"', $string);
	//Escape SQL functions
	$string = preg_replace("/select/i", "\$0\\", $string);
	$string = preg_replace("/drop/i", "\$0\\", $string);
	$string = preg_replace("/insert/i", "\$0\\", $string);
	$string = preg_replace("/update/i", "\$0\\", $string);
	$string = preg_replace("/join/i", "\$0\\", $string);
	return $string;
}

function sql_abc_unclean($string)
{
	$string = str_replace("\\", "", $string);
	return $string;
}