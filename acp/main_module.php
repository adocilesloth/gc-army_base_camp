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

			$config->set('start_perm_groups', $request->variable('start_perm_groups', 'ADMINISTRATORS,', true));
			/*Campaign Settings*/
			$config->set('campaign_state', $request->variable('apc_campaign_state', '0'));
			$config->set('campaign_name', $request->variable('apc_campaign_name', '', true));
			$config->set('campaign_divisions', $request->variable('apc_campaign_divisions', 'Infantry,Armour,Air', true));
			/*Army 1 Settings*/
			$config->set('army1_name', $request->variable('apc_army1_name', '', true));
			$config->set('army1_colour', $request->variable('apc_army1_colour', '084CA1'));
			$config->set('army1_general', $request->variable('apc_army1_general', '', true));
			$config->set('army1_password', $request->variable('apc_army1_password', '', true));
			/*Army B Settings*/
			$config->set('armyb_name', $request->variable('apc_armyb_name', '', true));
			$config->set('armyb_colour', $request->variable('apc_armyb_colour', 'ED1C24'));
			$config->set('armyb_general', $request->variable('apc_armyb_general', '', true));
			$config->set('armyb_password', $request->variable('apc_armyb_password', '', true));
			/*TA Settings*/
			$config->set('ta_name', $request->variable('apc_ta_name', 'Tournament Administrators', true));
			$config->set('ta_colour', $request->variable('apc_ta_colour', '0099FF'));
			$config->set('ta_general', $request->variable('apc_ta_general', '', true));
			$config->set('ta_password', $request->variable('apc_ta_password', '', true));

			trigger_error($user->lang('ACP_ABC_SETTING_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'APC_ABC_START_PERM'	=> $config['start_perm_groups'],
			'APC_ABC_STATE'			=> $config['campaign_state'],
			'APC_ABC_START_NAME'	=> $config['campaign_name'],
			'APC_ABC_START_DIV'		=> $config['campaign_divisions'],
			'APC_ABC_START_ARMY1'	=> $config['army1_name'],
			'APC_ABC_START_COL1'	=> $config['army1_colour'],
			'APC_ABC_START_GEN1'	=> $config['army1_general'],
			'APC_ABC_START_PW1'		=> $config['army1_password'],
			'APC_ABC_START_ARMYB'	=> $config['armyb_name'],
			'APC_ABC_START_COLB'	=> $config['armyb_colour'],
			'APC_ABC_START_GENB'	=> $config['armyb_general'],
			'APC_ABC_START_PWB'		=> $config['armyb_password'],
			'APC_ABC_START_TA'		=> $config['ta_name'],
			'APC_ABC_START_COLTA'	=> $config['ta_colour'],
			'APC_ABC_START_GENTA'	=> $config['ta_general'],
			'APC_ABC_START_PWTA'	=> $config['ta_password'],
			'U_ACTION'				=> $this->u_action,
		));
	}
}
