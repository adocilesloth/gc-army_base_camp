<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\globalconflict\abc\acp\main_module',
			'title'		=> 'ACP_ABC_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_ABC',
					'auth'	=> 'ext_globalconflict/abc && acl_a_board',
					'cat'	=> array('ACP_ABC_TITLE')
				),
			),
		);
	}
}
