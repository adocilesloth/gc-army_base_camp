<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_finish
{
	/* @var \phpbb\config\config */
	protected $config;
	
	/* @var \phpbb\template\template */
	protected $template;
	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/** @var string */
	protected $root_path;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\db\driver\driver_interface $db,
		$root_path)
	{
		$this->config = $config;
		$this->template = $template;
		$this->db = $db;
		$this->root_path = $root_path;
	}
	
	public function finish_page()
	{
		$this->template->assign_vars(array(
			'ABC_FINISH_ARCH'		=> 'The Archives',
			'ABC_FINISH_H_ARCH'		=> 'Uncategorized Archives',
			'ABC_FINISH_ARCH_G'		=> 'Archive / Historian,',
		));
		return;
	}
	
	public function finish_campaign()
	{
		include $this->root_path . 'includes/functions_user.php';
		/*Delete User Groups*/
		$armies = array('army1_', 'armyb_');
		$groups = array('', ' Officers', ' HC', ' General');
		
		$sql_group_name = '';
		foreach($armies as $army)
		{
			foreach($groups as $group)
			{
				$sql_group_name .= "group_name = '".$this->config[$army.'name'].$group."' OR ";
			}
		}
		$sql_group_name .= "group_name = '".$this->config['ta_name']."'";
		$sql = "SELECT group_id FROM ". GROUPS_TABLE ." WHERE ".$sql_group_name;
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		if(!$rowset)
		{
			$this->template->assign_var('ACP_FINISHED_DONE', false);
			$this->template->assign_var('ACP_START', false);
			return;
		}
		
		for($i=0; $i<count($rowset); $i++)
		{
			group_delete($rowset[$i]['group_id']);
		}
		
		/*Remove TA User Group from ABC Admins*/
		$group_name = $this->config['ta_name'];
		$abc_admins_string = $this->config['start_perm_groups'];
		$abc_admins = explode(",", $abc_admins_string);
		$abc_admins_string = '';
		foreach($abc_admins as $admins)
		{
			if($admins != $group_name && $admins != '')
			{
				$abc_admins_string .= $admins.',';
			}
		}
		$this->config->set('start_perm_groups', $abc_admins_string);
		
		/*Reset Campaign Settings*/
		$this->config->set('campaign_state', '0');
		$this->config->set('campaign_name', '');
		$this->config->set('campaign_divisions', 'Infantry,Armour,Air');
		$this->config->set('campaign_archive', 'The Archives');
		$this->config->set('campaign_hidden_archive', 'Uncategorized Archives');
		/*Army 1 Settings*/
		$this->config->set('army1_name', '');
		$this->config->set('army1_colour', '084CA1');
		$this->config->set('army1_general', '');
		$this->config->set('army1_password', '');
		/*Army B Settings*/
		$this->config->set('armyb_name', '');
		$this->config->set('armyb_colour', 'ED1C24');
		$this->config->set('armyb_general', '');
		$this->config->set('armyb_password', '');
		/*TA Settings*/
		$this->config->set('ta_name', 'Tournament Administrators');
		$this->config->set('ta_colour', '0099FF');
		$this->config->set('ta_general', '');
		$this->config->set('ta_password', '');
		
		$this->template->assign_var('ACP_FINISHED_DONE', true);
		$this->template->assign_var('ACP_START', true);
		return;
	}
}