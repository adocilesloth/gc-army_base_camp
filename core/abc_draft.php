<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_draft
{
	/* @var \phpbb\config\config */
	protected $config;
	
	/* @var \phpbb\template\template */
	protected $template;
	
	/* @var \phpbb\user */
	protected $user;
	
	/** @var request_interface */
	protected $request;
	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/** @var string */
	protected $root_path;
	
	/* @var \globalconflict\abc\core\permissions */
	protected $permissions;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\request\request $request,
		\phpbb\db\driver\driver_interface $db,
		$root_path,
		\globalconflict\abc\core\permissions $permissions)
	{
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
		$this->db = $db;
		$this->root_path = $root_path;
		$this->permissions	= $permissions;
	}
	
	public function draft_page()
	{
		$campaign_divisions = $this->config['campaign_divisions'];
		$camp_div = explode(",", $campaign_divisions);
		$draft_select = '';
		$selected = $camp_div[0];
		foreach($camp_div as $c_div)
		{
			$c_div_selected = ($c_div == $selected) ? ' selected="selected"' : '';
			$draft_select .= '<option value="'.$c_div.'"'.$c_div_selected.'>'.$c_div.'</option>';
		}
		
		$this->template->assign_vars(array(
			'ABC_DRAFT_SELECT'	=> $draft_select,
			'ACP_DRAFT_FUNNY'	=> false,
		));
		return;
	}
	
	public function join_draft()
	{
		/*If somene tries to be funny*/
		$password = $this->request->variable('draft_pw', '', true);
		if($password == "it" or $password == "it below")
		{
			$this->template->assign_var('ABC_DRAFT_FUNNY', true);
		}
		else
		{
			$default = false;
			$group_name = '';
			$colour = '000000';
			/*If a correct army password is entered*/
			if($password == $this->config['army1_password'] or
				$password == $this->config['armyb_password'] or
				$password == $this->config['ta_password'])
			{
				$army = '';
				if($password == $this->config['ta_password'])
				{
					$army = 'ta_';
					$default = true;
				}
				elseif($password == $this->config['army1_password'])
				{
					$army = 'army1_';
					$default = true;
				}
				else
				{
					$army = 'armyb_';
					$default = true;
				}
			
				$group_name = $this->config[$army.'name'];
				$colour = $this->config[$army.'colour'];
				
				/*Add user to the requested group*/
				$sql = "SELECT group_id FROM ". GROUPS_TABLE ." WHERE group_name = '".$group_name."'";
				$result = $this->db->sql_query($sql);
				$group_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$group_id)
				{
					$group_name = $this->user->lang['ABC_DRAFT_ERR_GID'];
				}
				
				include $this->root_path . 'includes/functions_user.php';
				$user_id = $this->user->data['user_id'];
				group_user_add($group_id, $user_id_ary = array($user_id,));
				group_user_attributes('default', $group_id, $user_id_ary = array($user_id,));
			}
			/*If joining a division draft*/
			else
			{
				$user_id = $this->user->data['user_id'];
				$username = $this->user->data['username'];
				$division = $this->request->variable('draft_division', '');
				$availability = $this->request->variable('draft_avail', '');
				$notes = $this->request->variable('draft_notes', '');
				
				$sql = "INSERT INTO abc_draft (user_id, username, division, availability, notes) VALUES ($user_id, '$username', '$division', '$availability', '$notes')";
				$result = $this->db->sql_query($sql);
				$this->db->sql_freeresult($result);
				
				$group_name = $division.' Player Draft';
			}
			
			$this->template->assign_vars(array(
				'ABC_DRAFT_ARMY_JOIN'	=> $group_name,
				'ABC_DRAFT_ARMY_COLOUR'	=> $colour,
				'ABC_DRAFT_UID'			=> $user_id,
			));
		}
		return;
	}
	
	public function leave_draft()
	{
		$user_id = $this->user->data['user_id'];
		$sql = "DELETE FROM abc_draft WHERE user_id = $user_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		$this->template->assign_var('ABC_DRAFT_LEFT', true);
		return;
	}
	
	public function draft_list()
	{
		$campaign_divisions = $this->config['campaign_divisions'];
		$camp_div = explode(",", $campaign_divisions);
		$army1 = $this->config['army1_name'];
		$armyb = $this->config['armyb_name'];
		$ta_army = $this->config['ta_name'];
		$is_ta = $this->permissions->whitelist(array($ta_army,));
		
		$draft_list = '';
		foreach($camp_div as $c_div)
		{
			$draft_list .= "<h2>".$c_div."</h2>";
			
			$sql = "SELECT username, availability, notes FROM abc_draft WHERE division = '$c_div'";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			if(!$rowset)
			{
				$draft_list .= 'NONE';
			}
			else
			{
				for($i=0; $i<count($rowset); $i++)
				{
					$draft_list .= "<p>";
					$draft_list .= $this->user->lang['ABC_DRAFT_LIST_NAME']." <b>".$rowset[$i]['username']."</b><br>";
					$draft_list .= $this->user->lang['ABC_DRAFT_AVAIL']." ".$rowset[$i]['availability']."<br>";
					$draft_list .= $this->user->lang['ABC_DRAFT_NOTES']." ".$rowset[$i]['notes'];
					if($is_ta)
					{
						$draft_list .= "<br>";
						$draft_list .= $this->user->lang['ABC_DRAFT_LIST_ARMY']." ";
						$username = $rowset[$i]['username'];
						$username = str_replace(" ", "_", $username);
						$draft_list .= "<select name=\"$username\" id=\"$username\">";
						$draft_list .= "<option value=\" \" selected=\"selected\"> </option>";
						$draft_list .= "<option value=\"$army1\">$army1</option>";
						$draft_list .= "<option value=\"$armyb\">$armyb</option>";
						$draft_list .= "<option value=\"$ta_army\">$ta_army</option>";
						$draft_list .= "</select>";
					}
					$draft_list .= "</p>";
				}
				$draft_list .= "<br>";
			}
			
			if($is_ta)
			{
				$run_draft_text = $this->user->lang['ABC_DRAFT_LIST_RUN'];
				$draft_list .= "<fieldset class=\"submit-buttons\">";
				$draft_list .= "<input type=\"submit\" name=\"run_draft\" id=\"run_draft\" value=\"$run_draft_text\" class=\"button1\"/>";
				$draft_list .= "</fieldset>";
			}
		}
		
		$this->template->assign_vars(array(
			'ABC_COMPLETE_DRAFT_LIST'	=> $draft_list,
		));
		return;
	}
	
	public function run_draft()
	{
		/*Get all players in draft*/
		$sql = "SELECT user_id, username FROM abc_draft";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		/*If there are names in the draft list*/
		if($rowset)
		{
			/*Get army names*/
			$army1 = $this->config['army1_name'];
			$armyb = $this->config['armyb_name'];
			$ta_army = $this->config['ta_name'];
			$armies = array($army1, $armyb, $ta_army);
			
			/*Get army group ids*/
			$group_ids = [];
			$sql = "SELECT group_id, group_name FROM ". GROUPS_TABLE ." WHERE group_name = '$army1' OR group_name = '$armyb' OR group_name = '$ta_army'";
			$result = $this->db->sql_query($sql);
			$group_rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			if(count($group_rowset) != 3)
			{
				$this->template->assign_vars(array(
					'ABC_COMPLETE_DRAFT_LIST'	=> $this->user->lang['ABC_DRAFT_ERR_WRONGNUM'],
				));
				return;
			}
			for($i=0; $i<3; $i++)
			{
				if($group_rowset[$i]['group_name'] == $army1)
				{
					$group_ids[$army1] = $group_rowset[$i]['group_id'];
				}
				elseif($group_rowset[$i]['group_name'] == $armyb)
				{
					$group_ids[$armyb] = $group_rowset[$i]['group_id'];
				}
				elseif($group_rowset[$i]['group_name'] == $ta_army)
				{
					$group_ids[$ta_army] = $group_rowset[$i]['group_id'];
				}
			}
			
			$drafted_to = [];
			$drafted_to[$army1] = [];
			$drafted_to[$armyb] = [];
			$drafted_to[$ta_army] = [];
			
			/*See if any players have been assigned an army*/
			for($i=0; $i<count($rowset); $i++)
			{
				$username = $rowset[$i]['username'];
				$username = str_replace(" ", "_", $username);
				$selected = $this->request->variable($username, '');
				
				foreach($armies as $army)
				{
					/*If assigned an army, add user to army array*/
					if($selected == $army)
					{
						$drafted_to[$army] = $rowset[$i]['user_id'];
						$sql = "DELETE FROM abc_draft WHERE user_id = ".$rowset[$i]['user_id'];
						$result = $this->db->sql_query($sql);
						$this->db->sql_freeresult($result);
						break;
					}
				}
			}
			
			/*Assign to army*/
			include $this->root_path . 'includes/functions_user.php';
			foreach($armies as $army)
			{
				if($drafted_to[$army])
				{
					group_user_add($group_ids[$army], $user_id_ary = $drafted_to[$army]);
					group_user_attributes('default', $group_ids[$army], $user_id_ary = $drafted_to[$army]);
				}
			}
		}
		
		/*Repopulate draft list*/
		$this->draft_list();
		return;
	}
}