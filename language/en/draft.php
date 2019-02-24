<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	//ABC Draft Page
	'ABC_DRAFT_DRAFT' 	=> 'Player Draft',
	'ABC_DRAFT_EXPLAIN'	=> 'This is where you would sign up for the draft. If a draft existed. Or ABC worked...',
));