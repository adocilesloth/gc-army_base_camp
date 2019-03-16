<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\acp;

class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang('acp/common');
		$this->tpl_name = 'abc_apc_body';
		$this->page_title = $user->lang('ACP_ABC_TITLE');
		add_form_key('globalconflict/abc');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('globalconflict/abc'))
			{
				trigger_error('FORM_INVALID');
			}

			$config->set('start_perm_groups', $this->sql_abc_clean($request->variable('start_perm_groups', 'ADMINISTRATORS,', true)));
			/*Campaign Settings*/
			$config->set('campaign_state', $this->sql_abc_clean($request->variable('apc_campaign_state', '0')));
			$config->set('campaign_name', $this->sql_abc_clean($request->variable('apc_campaign_name', '', true)));
			$config->set('campaign_divisions', $this->sql_abc_clean($request->variable('apc_campaign_divisions', 'Infantry,Armour,Air', true)));
			/*Army 1 Settings*/
			$config->set('army1_name', $this->sql_abc_clean($request->variable('apc_army1_name', '', true)));
			$config->set('army1_colour', $this->sql_abc_clean($request->variable('apc_army1_colour', '084CA1')));
			$config->set('army1_general', $this->sql_abc_clean($request->variable('apc_army1_general', '', true)));
			$config->set('army1_password', $this->sql_abc_clean($request->variable('apc_army1_password', '', true)));
			/*Army B Settings*/
			$config->set('armyb_name', $this->sql_abc_clean($request->variable('apc_armyb_name', '', true)));
			$config->set('armyb_colour', $this->sql_abc_clean($request->variable('apc_armyb_colour', 'ED1C24')));
			$config->set('armyb_general', $this->sql_abc_clean($request->variable('apc_armyb_general', '', true)));
			$config->set('armyb_password', $this->sql_abc_clean($request->variable('apc_armyb_password', '', true)));
			/*TA Settings*/
			$config->set('ta_name', $this->sql_abc_clean($request->variable('apc_ta_name', 'Tournament Administrators', true)));
			$config->set('ta_colour', $this->sql_abc_clean($request->variable('apc_ta_colour', '0099FF')));
			$config->set('ta_general', $this->sql_abc_clean($request->variable('apc_ta_general', '', true)));
			$config->set('ta_password', $this->sql_abc_clean($request->variable('apc_ta_password', '', true)));

			trigger_error($user->lang('ACP_ABC_SETTING_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'APC_ABC_START_PERM'	=> $this->sql_abc_unclean($config['start_perm_groups']),
			'APC_ABC_STATE'			=> $this->sql_abc_unclean($config['campaign_state']),
			'APC_ABC_START_NAME'	=> $this->sql_abc_unclean($config['campaign_name']),
			'APC_ABC_START_DIV'		=> $this->sql_abc_unclean($config['campaign_divisions']),
			'APC_ABC_START_ARMY1'	=> $this->sql_abc_unclean($config['army1_name']),
			'APC_ABC_START_COL1'	=> $this->sql_abc_unclean($config['army1_colour']),
			'APC_ABC_START_GEN1'	=> $this->sql_abc_unclean($config['army1_general']),
			'APC_ABC_START_PW1'		=> $this->sql_abc_unclean($config['army1_password']),
			'APC_ABC_START_ARMYB'	=> $this->sql_abc_unclean($config['armyb_name']),
			'APC_ABC_START_COLB'	=> $this->sql_abc_unclean($config['armyb_colour']),
			'APC_ABC_START_GENB'	=> $this->sql_abc_unclean($config['armyb_general']),
			'APC_ABC_START_PWB'		=> $this->sql_abc_unclean($config['armyb_password']),
			'APC_ABC_START_TA'		=> $this->sql_abc_unclean($config['ta_name']),
			'APC_ABC_START_COLTA'	=> $this->sql_abc_unclean($config['ta_colour']),
			'APC_ABC_START_GENTA'	=> $this->sql_abc_unclean($config['ta_general']),
			'APC_ABC_START_PWTA'	=> $this->sql_abc_unclean($config['ta_password']),
			'U_ACTION'				=> $this->u_action,
		));
	}
	
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
}
