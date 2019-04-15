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
	
	/* @var \phpbb\user */
	protected $user;
	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/** @var string */
	protected $root_path;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		$root_path)
	{
		$this->config		= $config;
		$this->template		= $template;
		$this->user			= $user;
		$this->db			= $db;
		$this->root_path	= $root_path;
	}
	
	public function finish_page()
	{
		$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= "<h2>".$this->user->lang['ABC_FINISH']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_FINISH_WARN']."</p><br>";
					
		$abc_content .= "<dl><dt><label for=\"campaign_archive\">".$this->user->lang['ABC_FINISH_ARCH']."</label><br>";
		$abc_content .= "<span>".$this->user->lang['ABC_FINISH_ARCH_EXPL']."</span></dt>";
		$abc_content .= "<dd><input type=\"text\" name=\"campaign_archive\" value=\"The Archives\" /></dd>";
		$abc_content .= "</dl><dl>";
		$abc_content .= "<dt><label for=\"campaign_hidden_archive\">".$this->user->lang['ABC_FINISH_H_ARCH']."</label><br>";
		$abc_content .= "<span>".$this->user->lang['ABC_FINISH_H_ARCH_EXPL']."</span></dt>";
		$abc_content .= "<dd><input type=\"text\" name=\"campaign_hidden_archive\" value=\"Uncategorized Archives\" /></dd>";
		$abc_content .= "</dl><dl>";
		$abc_content .= "<dt><label for=\"archivist\">".$this->user->lang['ABC_FINISH_ARCH_G']."</label><br>";
		$abc_content .= "<span>".$this->user->lang['ABC_FINISH_ARCH_G_EXPL']."</span></dt>";
		$abc_content .= "<dd><input type=\"text\" name=\"archivist\" value=\"Archive / Historian,\" /></dd></dl>";
					
		$abc_content .= "<fieldset class=\"submit-buttons\">";
		$abc_content .= "<input type=\"submit\" name=\"finish_submit\" id=\"finish_submit\" value=\"".$this->user->lang['ABC_FINISH']."\" class=\"button1\"/>";
		$abc_content .= "</fieldset></fieldset>";
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	
	public function finish_campaign()
	{
		/*DO NOT DELETE RANKS!!! Deleting ranks breaks stuff and makes it a PITA to fix*/
		
		$sql = "SELECT army_id FROM abc_armies WHERE campaign_id = (SELECT MAX(campaign_id) FROM abc_armies)";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		$army_query = "";
		for($i=0; $i<count($rowset); $i++)
		{
			$army_query .= " army_id = ";
			$army_query .= $rowset[$i]['army_id'];
			$army_query .= " OR";
		}
		
		/*Return users to rank before campaign*/
		$sql = "SELECT user_id, user_soldierid FROM abc_users WHERE".$army_query;
		$sql = substr($sql, 0, strlen($sql)-3);
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(!$rowset)
		{
			$abc_content = "<h2>".$this->user->lang['ABC_FINISH']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_FINISH_FAILED']."</p>";
			$abc_content .= "<p>users</p>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		for($i=0; $i<count($rowset); $i++)
		{
			$user_id = $rowset[$i]['user_id'];
			$rank_id = $rowset[$i]['user_soldierid'];
			$sql = "UPDATE phpbb_users SET user_rank = $rank_id WHERE user_id = $user_id";
			$result = $this->db->sql_query($sql);
			$this->db->sql_freeresult($result);
		}
		
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
			$abc_content = "<h2>".$this->user->lang['ABC_FINISH']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_FINISH_FAILED']."</p>";
			$abc_content .= "<p>group_id</p>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
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
		$this->config->set('army1_tag', '');
		$this->config->set('army1_colour', '084CA1');
		$this->config->set('army1_general', '');
		$this->config->set('army1_password', '');
		/*Army B Settings*/
		$this->config->set('armyb_name', '');
		$this->config->set('armyb_tag', '');
		$this->config->set('armyb_colour', 'ED1C24');
		$this->config->set('armyb_general', '');
		$this->config->set('armyb_password', '');
		/*TA Settings*/
		$this->config->set('ta_name', 'Tournament Administrators');
		$this->config->set('ta_tag', 'TA');
		$this->config->set('ta_colour', '0099FF');
		$this->config->set('ta_general', '');
		$this->config->set('ta_password', '');
		
		
		//$failed_reason = $this->template->
		
		$abc_content = "<h2>".$this->user->lang['ABC_FINISH']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_FINISH_DONE']."</p>";
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
}