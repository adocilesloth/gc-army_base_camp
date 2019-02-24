<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_army
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
		$this->config		= $config;
		$this->template		= $template;
		$this->user			= $user;
		$this->request		= $request;
		$this->db			= $db;
		$this->root_path	= $root_path;
		$this->permissions	= $permissions;
	}
	
	public function army_list()
	{
		$complete_army_list = '';
		/*Get user army*/
		$army = '';
		$army_name = '';
		$armies = array('army1', 'armyb', 'ta');
		foreach($armies as $armee)
		{
			if($this->permissions->whitelist(array($this->config[$armee.'_name'],)))
			{
				$army = $armee;
				$army_name = $this->config[$armee.'_name'];
				break;
			}
		}
		if($army == 'army1' or $army == 'armyb')
		{
			$selector = '';
			
			/*General*/
			/*We need to General user id but can get General name from config more easily*/
			$sql = "SELECT ugt.user_id from ".USER_GROUP_TABLE." AS ugt
						RIGHT JOIN ".GROUPS_TABLE." AS gt ON gt.group_id = ugt.group_id
						WHERE gt.group_name = '".$army_name." General'";
			$result = $this->db->sql_query($sql);
			$general_user_id = $this->db->sql_fetchfield('user_id');
			$this->db->sql_freeresult($result);
			
			if($general_user_id)
			{
				if(!is_array($general_user_id))
				{
					$general_user_id = array($general_user_id,);
				}
			}
			else
			{
				$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', $this->user->lang['ABC_ARMY_ERR_GEN_UID']);
				return;
			}
			
			$hc_army_list = "<h2>".$this->user->lang['ABC_ARMY_GENERAL']."</h2>";
			$hc_army_list .= "<p>".$this->config[$army.'_general']."</p>";
			$hc_army_list .= "<br>";
			
			/*HC*/
			$high_rank = $this->permissions->whitelist(array($army_name." General",));
			$hc_army_list .= "<h2>".$this->user->lang['ABC_ARMY_HC']."</h2>";
			
			$sql = "SELECT ugt.user_id from ".USER_GROUP_TABLE." AS ugt
						RIGHT JOIN ".GROUPS_TABLE." AS gt ON gt.group_id = ugt.group_id
						WHERE gt.group_name = '".$army_name." HC'";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			$HC_user_id = [];
			if($rowset)
			{
				for($i=0; $i<count($rowset); $i++)
				{
					$HC_user_id[] = $rowset[$i]['user_id'];
				}
				$HC_user_id = array_diff($HC_user_id, $general_user_id);
				
				if(count($HC_user_id) > 0)
				{
					$sql_user_id = '';
					foreach($HC_user_id as $user_id)
					{
						$sql_user_id .= "user_id = $user_id OR ";
					}
					$sql_user_id = substr($sql_user_id, 0, strlen($sql_user_id)-3);
					
					$sql = "SELECT username FROM ".USERS_TABLE." WHERE $sql_user_id";
					$result = $this->db->sql_query($sql);
					$username_rowset = $this->db->sql_fetchrowset();
					$this->db->sql_freeresult($result);
					if(!$username_rowset)
					{
						$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', $this->user->lang['ABC_ARMY_ERR_HC_UN']."<br>$sql");
						return;
					}
					
					for($i=0; $i<count($username_rowset); $i++)
					{
						$username = $username_rowset[$i]['username'];
						$hc_army_list .= "<p>$username";
						if($high_rank)
						{
							$hc_army_list .= " <input type=\"checkbox\" id=\"$username\" name=\"$username\">";
						}
						$hc_army_list .= "</p>";
					}
				}
			}
			$hc_army_list .= "<br>";
			
			/*Officer*/
			$high_rank = $this->permissions->whitelist(array($army_name." HC",));
			if($high_rank && $selector == '')
			{
				$selector .= "<select name=\"army_mote\" id=\"army_mote\">";
				$selector .= "<option value=\"none\" selected=\"selected\"> </option>";
				$selector .= "<option value=\"set_HC\">".$this->user->lang['ABC_ARMY_SET_HC']."</option>";
				$selector .= "<option value=\"set_officer\">".$this->user->lang['ABC_ARMY_SET_OFFICER']."</option>";
				$selector .= "<option value=\"set_squaddie\">".$this->user->lang['ABC_ARMY_SET_SQUADDIE']."</option>";
				$selector .= "</select><br>";
				$selector .= "<input type=\"submit\" name=\"army_set\" id=\"army_set\" value=\"".$this->user->lang['ABC_ARMY_SET']."\" class=\"button1\"/>";
			}
			$officer_army_list = "<h2>".$this->user->lang['ABC_ARMY_OFFICER']."</h2>";
			
			$sql = "SELECT ugt.user_id from ".USER_GROUP_TABLE." AS ugt
						RIGHT JOIN ".GROUPS_TABLE." AS gt ON gt.group_id = ugt.group_id
						WHERE gt.group_name = '".$army_name." Officers'";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			$officer_user_id = [];
			if($rowset)
			{
				for($i=0; $i<count($rowset); $i++)
				{
					$officer_user_id[] = $rowset[$i]['user_id'];
				}
				$officer_user_id = array_diff($officer_user_id, $HC_user_id, $general_user_id);
				
				if(count($officer_user_id) > 0)
				{
					$sql_user_id = '';
					foreach($officer_user_id as $user_id)
					{
						$sql_user_id .= "user_id = $user_id OR ";
					}
					$sql_user_id = substr($sql_user_id, 0, strlen($sql_user_id)-3);
					
					$sql = "SELECT username FROM ".USERS_TABLE." WHERE $sql_user_id";
					$result = $this->db->sql_query($sql);
					$username_rowset = $this->db->sql_fetchrowset();
					$this->db->sql_freeresult($result);
					if(!$username_rowset)
					{
						$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', $this->user->lang['ABC_ARMY_ERR_OFF_UN']);
						return;
					}
					
					for($i=0; $i<count($username_rowset); $i++)
					{
						$username = $username_rowset[$i]['username'];
						$officer_army_list .= "<p>$username";
						if($high_rank)
						{
							$officer_army_list .= " <input type=\"checkbox\" id=\"$username\" name=\"$username\">";
						}
						$officer_army_list .= "</p>";
					}
				}
			}
			$officer_army_list .= "<br>";
			
			/*Squaddie*/
			$high_rank = $this->permissions->whitelist(array($army_name." Officers",));
			if($high_rank && $selector == '')
			{
				$selector .= "<select name=\"army_mote\" id=\"army_mote\">";
				$selector .= "<option value=\"none\" selected=\"selected\"> </option>";
				$selector .= "<option value=\"set_officer\">".$this->user->lang['ABC_ARMY_SET_OFFICER']."</option>";
				$selector .= "<option value=\"set_squaddie\">".$this->user->lang['ABC_ARMY_SET_SQUADDIE']."</option>";
				$selector .= "</select><br>";
				$selector .= "<input type=\"submit\" name=\"army_set\" id=\"army_set\" value=\"".$this->user->lang['ABC_ARMY_SET']."\" class=\"button1\"/>";
			}
			$squaddie_army_list = "<h2>".$this->user->lang['ABC_ARMY_SQUADDIE']."</h2>";
			
			$sql = "SELECT ugt.user_id from ".USER_GROUP_TABLE." AS ugt
						RIGHT JOIN ".GROUPS_TABLE." AS gt ON gt.group_id = ugt.group_id
						WHERE gt.group_name = '".$army_name."'";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			$squaddie_user_id = [];
			if($rowset)
			{
				for($i=0; $i<count($rowset); $i++)
				{
					$squaddie_user_id[] = $rowset[$i]['user_id'];
				}
				$squaddie_user_id = array_diff($squaddie_user_id, $officer_user_id, $HC_user_id, $general_user_id);
				
				if(count($squaddie_user_id) > 0)
				{
					$sql_user_id = '';
					foreach($squaddie_user_id as $user_id)
					{
						$sql_user_id .= "user_id = $user_id OR ";
					}
					$sql_user_id = substr($sql_user_id, 0, strlen($sql_user_id)-3);
					
					$sql = "SELECT username FROM ".USERS_TABLE." WHERE $sql_user_id";
					$result = $this->db->sql_query($sql);
					$username_rowset = $this->db->sql_fetchrowset();
					$this->db->sql_freeresult($result);
					if(!$username_rowset)
					{
						$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', $this->user->lang['ABC_ARMY_ERR_SQD_UN']);
						return;
					}
					
					for($i=0; $i<count($username_rowset); $i++)
					{
						$username = $username_rowset[$i]['username'];
						$squaddie_army_list .= "<p>$username";
						if($high_rank)
						{
							$squaddie_army_list .= " <input type=\"checkbox\" id=\"$username\" name=\"$username\">";
						}
						$squaddie_army_list .= "</p>";
					}
				}
			}
			$squaddie_army_list .= "<br>";
			$complete_army_list = $hc_army_list.$officer_army_list.$squaddie_army_list.$selector;
		}
		elseif($army == 'ta')
		{
			/*TA*/
			$complete_army_list .= "<h2>$army_name</h2>";
			
			$sql = "SELECT ut.username FROM ".USERS_TABLE." AS ut 
						INNER JOIN ".USER_GROUP_TABLE." AS ugt ON ugt.user_id = ut.user_id
						RIGHT JOIN ".GROUPS_TABLE." AS gt ON gt.group_id = ugt.group_id
						WHERE gt.group_name = '$army_name'";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			for($i=0; $i<count($rowset); $i++)
			{
				$username = $rowset[$i]['username'];
				$complete_army_list .= "<p>$username</p>";
			}
		}
		
		$this->template->assign_vars(array(
			'ABC_ARMY_NAME'				=> $army_name,
			'ABC_COMPLETE_ARMY_LIST'	=> $complete_army_list,
		));
		return;
	}
	
	public function set_group()
	{
		/*Get user army*/
		$army = '';
		$army_name = '';
		$armies = array('army1', 'armyb', 'ta');
		foreach($armies as $armee)
		{
			if($this->permissions->whitelist(array($this->config[$armee.'_name'],)))
			{
				$army = $armee;
				$army_name = $this->config[$armee.'_name'];
				break;
			}
		}
		
		$set_to = $this->request->variable('army_mote', '');
		if($set_to == '')
		{
			$this->template->assign_vars(array(
				'ABC_ARMY_NAME'				=> $army_name,
				'ABC_COMPLETE_ARMY_LIST'	=> $this->user->lang['ABC_ARMY_ERR_SET_GROUP'],
			));
			return;
		}
		
		/**Promote/demote users**/
		if( ($army == 'army1' || $army == 'armyb') && $set_to != 'none')
		{
			/*Get user_id and username of army members*/
			$sql = "SELECT ut.user_id, ut.username FROM ".USERS_TABLE." AS ut 
						INNER JOIN ".USER_GROUP_TABLE." AS ugt ON ugt.user_id = ut.user_id
						RIGHT JOIN ".GROUPS_TABLE." AS gt ON gt.group_id = ugt.group_id
						WHERE gt.group_name = '$army_name'";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			if(!$rowset)
			{
				$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', $this->user->lang['ABC_ARMY_ERR_SQL_JOIN']);
				return;
			}
			
			$user_id = [];
			for($i=0; $i<count($rowset); $i++)
			{
				$username = str_replace(" ", "_", $rowset[$i]['username']);
				$user_selected = $this->request->variable($username, false);
				if($user_selected)
				{
					$user_id[] = $rowset[$i]['user_id'];
				}
			}
			
			$user_id = array_unique($user_id);
			if(count($user_id) > 0)
			{
				/*Get Group ids*/
				$sql = "SELECT group_id, group_name FROM ".GROUPS_TABLE." WHERE group_name = '".$army_name." HC' OR group_name = '".$army_name." Officers'";
				$result = $this->db->sql_query($sql);
				$rowset = $this->db->sql_fetchrowset();
				$this->db->sql_freeresult($result);
				if(count($rowset) != 2)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', $this->user->lang['ABC_ARMY_ERR_HC_OFF']);
					return;
				}
				$HC_id = $officer_id = new \stdClass();
				for($i=0; $i<count($rowset); $i++)
				{
					if($rowset[$i]['group_name'] == $army_name." HC")
					{
						$HC_id = $rowset[$i]['group_id'];
					}
					elseif($rowset[$i]['group_name'] == $army_name." Officers")
					{
						$officer_id = $rowset[$i]['group_id'];
					}
				}
				
				include $this->root_path . 'includes/functions_user.php';
				/*Strip all groups*/
				group_user_del($HC_id, $user_id_ary = $user_id);
				group_user_del($officer_id, $user_id_ary = $user_id);
				/*Assign new groups*/
				if($set_to == 'set_HC')
				{
					group_user_add($HC_id, $user_id_ary = $user_id);
					group_user_add($officer_id, $user_id_ary = $user_id);
				}
				elseif($set_to == 'set_officer')
				{
					group_user_add($officer_id, $user_id_ary = $user_id);
				}
			}
		}
		
		$this->army_list();
		return;
	}
}