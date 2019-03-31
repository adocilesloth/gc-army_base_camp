<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\migrations;

class release_0_5_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$perm = $this->config['start_perm_groups'];
		$stat = $this->config['campaign_state'];
		$divs = $this->config['campaign_divisions'];
		$a1cl = $this->config['army1_colour'];
		$abcl = $this->config['armyb_colour'];
		$tanm = $this->config['ta_name'];
		$tatg = $this->config['ta_tag'];
		$tacl = $this->config['ta_colour'];
		
		if($perm && $divs && $a1cl && $abcl && $tanm && $tatg && $tacl)
		{
			return true;
		}
		
		return false;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\alpha2');
	}

	public function update_data()
	{
		return array(		
			array('config.add', array('start_perm_groups', 'ADMINISTRATORS,', true)),
			/*Campaign Settings*/
			array('config.add', array('campaign_state', '0')),
			array('config.add', array('campaign_name', '', true)),
			array('config.add', array('campaign_divisions', 'Infantry,Armour,Air', true)),
			/*Army 1 Settings*/
			array('config.add', array('army1_name', '', true)),
			array('config.add', array('army1_tag', '', true)),
			array('config.add', array('army1_colour', '084CA1')),
			array('config.add', array('army1_general', '', true)),
			array('config.add', array('army1_password', '', true)),
			/*Army B Settings*/
			array('config.add', array('armyb_name', '', true)),
			array('config.add', array('armyb_tag', '', true)),
			array('config.add', array('armyb_colour', 'ED1C24')),
			array('config.add', array('armyb_general', '', true)),
			array('config.add', array('armyb_password', '', true)),
			/*TA Settings*/
			array('config.add', array('ta_name', 'Tournament Administrators', true)),
			array('config.add', array('ta_tag', 'TA', true)),
			array('config.add', array('ta_colour', '0099FF')),
			array('config.add', array('ta_general', '', true)),
			array('config.add', array('ta_password', '', true)),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_ABC_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_ABC_TITLE',
				array(
					'module_basename'	=> '\globalconflict\abc\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
