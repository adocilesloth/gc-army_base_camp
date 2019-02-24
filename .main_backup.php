<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\controller;

class main
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;
	
	/** @var request_interface */
	protected $request;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;
	
	/** @var string */
	protected $root_path;
	
	/* @var \globalconflict\abc\core\permissions */
	protected $permissions;

	/**
	* Constructor
	*
	* @param \phpbb\config\config		$config
	* @param \phpbb\controller\helper	$helper
	* @param \phpbb\template\template	$template
	* @param \phpbb\user				$user
	*/
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\request\request $request,
		\phpbb\db\driver\driver_interface $db,
		$root_path,
		\globalconflict\abc\core\permissions $permissions)
	{
		$this->config		= $config;
		$this->helper		= $helper;
		$this->template		= $template;
		$this->user 		= $user;
		$this->request		= $request;
		$this->db			= $db;
		$this->root_path	= $root_path;
		$this->permissions	= $permissions;
	}

	/**
	* Demo controller for route /demo/{name}
	*
	* @param string		$name
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function handle($name)
	{
		/*Start Page*/
		if($this->request->is_set_post('start'))
		{
			$campaign_name = $this->config['campaign_name'];
			$campaign_divisions = $this->config['campaign_divisions'];
			$army1_name = $this->config['army1_name'];
			$army1_colour = $this->config['army1_colour'];
			$army1_general = $this->config['army1_general'];
			$army1_password = $this->config['army1_password'];
			$armyb_name = $this->config['armyb_name'];
			$armyb_colour = $this->config['armyb_colour'];
			$armyb_general = $this->config['armyb_general'];
			$armyb_password = $this->config['armyb_password'];
			$ta_name = $this->config['ta_name'];
			$ta_colour = $this->config['ta_colour'];
			$ta_general = $this->config['ta_general'];
			$ta_password = $this->config['ta_password'];
		
			$this->template->assign_vars(array(
				'ABC_START_NAME'		=> $campaign_name,
				'ABC_START_DIV'			=> $campaign_divisions,
				'ABC_START_ARMY1'		=> $army1_name,
				'ABC_START_COL1'		=> $army1_colour,
				'ABC_START_GEN1'		=> $army1_general,
				'ABC_START_PW1'			=> $army1_password,
				'ABC_START_ARMYB'		=> $armyb_name,
				'ABC_START_COLB'		=> $armyb_colour,
				'ABC_START_GENB'		=> $armyb_general,
				'ABC_START_PWB'			=> $armyb_password,
				'ABC_START_TA'			=> $ta_name,
				'ABC_START_COLTA'		=> $ta_colour,
				'ABC_START_GENTA'		=> $ta_general,
				'ABC_START_PWTA'		=> $ta_password,
			));
			return $this->helper->render('abc_start.html', $name);
		}
		/*Finish Page*/
		if($this->request->is_set_post('finish'))
		{
			return $this->helper->render('abc_finish.html', $name);
		}
		/*Draft Page*/
		if($this->request->is_set_post('draft'))
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
			return $this->helper->render('abc_draft.html', $name);
		}
		/*Draft List*/
		if($this->request->is_set_post('draft_list'))
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
				'ABC_COMPLETE_DRAFT_LIST'		=> $draft_list,
			));
			
			return $this->helper->render('abc_draft_list.html', $name);
		}
		/*Army List*/
		if($this->request->is_set_post('army_list'))
		{
			$complete_army_list = '';
			/*Get user army*/
			$army = '';
			$army_name = '';
			$armies = array('army1', 'army2', 'ta');
			foreach($armies as $armee)
			{
				if($this->permissions->whitelist(array($this->config[$armee.'_name'],)))
				{
					$army = $armee;
					$army_name = $this->config[$armee.'_name'];
					break;
				}
			}
			if($army == 'army1' or $army == 'army2')
			{
				$selector = '';
				
				/*General*/
				/*We need to General group id and user id but can get General name from config more easily*/
				$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name." General'";
				$result = $this->db->sql_query($sql);
				$general_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$general_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing General Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $general_id";
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
				
				$hc_army_list = "<h2>".$this->user->lang['ABC_ARMY_GENERAL']."</h2>";
				$hc_army_list .= "<p>".$this->config[$army.'_general']."</p>";
				$hc_army_list .= "<br>";
				
				/*HC*/
				$high_rank = $this->permissions->whitelist(array($army_name." General",));
				$hc_army_list .= "<h2>".$this->user->lang['ABC_ARMY_HC']."</h2>";
				$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name." HC'";
				$result = $this->db->sql_query($sql);
				$HC_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$HC_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing HC Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $HC_id";
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
					foreach($HC_user_id as $user_id)
					{
						$sql = "SELECT username FROM ".USERS_TABLE." WHERE user_id = $user_id";
						$result = $this->db->sql_query($sql);
						$username = $this->db->sql_fetchfield('username');
						$this->db->sql_freeresult($result);
						if($username)
						{
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
				$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name." Officers'";
				$result = $this->db->sql_query($sql);
				$officer_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$officer_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing Officer Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $officer_id";
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
					foreach($officer_user_id as $user_id)
					{
						$sql = "SELECT username FROM ".USERS_TABLE." WHERE user_id = $user_id";
						$result = $this->db->sql_query($sql);
						$username = $this->db->sql_fetchfield('username');
						$this->db->sql_freeresult($result);
						if($username)
						{
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
				$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name."'";
				$result = $this->db->sql_query($sql);
				$squaddie_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$squaddie_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing Army Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $squaddie_id";
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
					foreach($squaddie_user_id as $user_id)
					{
						$sql = "SELECT username FROM ".USERS_TABLE." WHERE user_id = $user_id";
						$result = $this->db->sql_query($sql);
						$username = $this->db->sql_fetchfield('username');
						$this->db->sql_freeresult($result);
						if($username)
						{
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
				$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name."'";
				$result = $this->db->sql_query($sql);
				$ta_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$ta_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing TA Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $ta_id";
				$result = $this->db->sql_query($sql);
				$rowset = $this->db->sql_fetchrowset();
				$this->db->sql_freeresult($result);
				$ta_user_id = [];
				if($rowset)
				{
					for($i=0; $i<count($rowset); $i++)
					{
						$ta_user_id[] = $rowset[$i]['user_id'];
					}
					foreach($ta_user_id as $user_id)
					{
						$sql = "SELECT username FROM ".USERS_TABLE." WHERE user_id = $user_id";
						$result = $this->db->sql_query($sql);
						$username = $this->db->sql_fetchfield('username');
						$this->db->sql_freeresult($result);
						if($username)
						{
							$complete_army_list .= "<p>$username</p>";;
						}
					}
				}
			}
			
			$this->template->assign_vars(array(
				'ABC_ARMY_NAME'				=> $army_name,
				'ABC_COMPLETE_ARMY_LIST'	=> $complete_army_list,
			));
			return $this->helper->render('abc_army.html', $name);
		}
		/*Start Campaign*/
		if($this->request->is_set_post('start_submit'))
		{
			/*Reject incomplete submission*/
			if($this->request->variable('campaign_name', '', true) == '' or
				$this->request->variable('campaign_divisions', '', true) == '' or
				$this->request->variable('army1_name', '', true) == '' or
				$this->request->variable('army1_colour', '') == '' or
				$this->request->variable('army1_general', '', true) == '' or
				$this->request->variable('army1_password', '', true) == '' or
				$this->request->variable('armyb_name', '', true) == '' or
				$this->request->variable('armyb_colour', '') == '' or
				$this->request->variable('armyb_general', '', true) == '' or
				$this->request->variable('armyb_password', '', true) == '' or
				$this->request->variable('ta_name', '', true) == '' or
				$this->request->variable('ta_colour', '') == '' or
				$this->request->variable('ta_general', '', true) == '' or
				$this->request->variable('ta_password', '', true) == '')
			{
				$this->template->assign_var('ACP_FAILED', true);
				$this->template->assign_var('ACP_SUCCESS', false);
			}
			else
			{
				$sql = "SELECT * FROM ". USERS_TABLE ." WHERE username = '".$this->request->variable('army1_general', '', true)."'";
				$result = $this->db->sql_query($sql);
				$user_id_1 = $this->db->sql_fetchfield('user_id');
				$this->db->sql_freeresult($result);
				
				$sql = "SELECT * FROM ". USERS_TABLE ." WHERE username = '".$this->request->variable('armyb_general', '', true)."'";
				$result = $this->db->sql_query($sql);
				$user_id_b = $this->db->sql_fetchfield('user_id');
				$this->db->sql_freeresult($result);
				
				$sql = "SELECT * FROM ". USERS_TABLE ." WHERE username = '".$this->request->variable('ta_general', '', true)."'";
				$result = $this->db->sql_query($sql);
				$user_id_ta = $this->db->sql_fetchfield('user_id');
				$this->db->sql_freeresult($result);
				
				/*Reject nonexistant generals*/
				if($user_id_1 < 1 or $user_id_b < 1 or $user_id_ta < 1)
				{
					$this->template->assign_var('ACP_FAILED', true);
					$this->template->assign_var('ACP_SUCCESS', false);
				}
				/*Setup Campaign*/
				else
				{				
					/*Campaign Settings*/
					$this->config->set('campaign_state', '1');
					$this->config->set('campaign_name', $this->request->variable('campaign_name', '', true));
					$this->config->set('campaign_divisions', $this->request->variable('campaign_divisions', 'Infantry,Armour,Air', true));
					/*Army 1 Settings*/
					$this->config->set('army1_name', $this->request->variable('army1_name', '', true));
					$this->config->set('army1_colour', $this->request->variable('army1_colour', '084CA1'));
					$this->config->set('army1_general', $this->request->variable('army1_general', '', true));
					$this->config->set('army1_password', $this->request->variable('army1_password', '', true));
					/*Army B Settings*/
					$this->config->set('armyb_name', $this->request->variable('armyb_name', '', true));
					$this->config->set('armyb_colour', $this->request->variable('armyb_colour', 'ED1C24'));
					$this->config->set('armyb_general', $this->request->variable('armyb_general', '', true));
					$this->config->set('armyb_password', $this->request->variable('armyb_password', '', true));
					/*TA Settings*/
					$this->config->set('ta_name', $this->request->variable('ta_name', 'Tournament Administrators', true));
					$this->config->set('ta_colour', $this->request->variable('ta_colour', '0099FF'));
					$this->config->set('ta_general', $this->request->variable('ta_general', '', true));
					$this->config->set('ta_password', $this->request->variable('ta_password', '', true));
					
					$this->template->assign_var('ACP_START', false);
					
					//$this->abc_forum->create_forum($forum_type = FORUM_POST);
					$gen_ids = array('army1_' => $user_id_1, 'armyb_' => $user_id_b,);
					/*Create Army User Groups*/
					include $this->root_path . 'includes/functions_user.php';
					$desc = '';
					$armies = array('army1_', 'armyb_');
					$groups = array('', ' Officers', ' HC', ' General');
					foreach($armies as $army)
					{
						$group_colour = $this->config[$army.'colour'];
						foreach($groups as $group)
						{
							$group_id = FALSE;
							$type = GROUP_CLOSED;
							$group_name = $this->config[$army.'name'].$group;
							$group_legend = 0;
							$group_pm = 1;
							$default = false;
							if($group == '')
							{
								$group_legend = 1;
								$default = true;
							}
							elseif($group == ' General')
							{
								$group_pm = 0;
								$type = GROUP_HIDDEN;
							}
							$group_attributes = array(
										'group_colour' 		=> $group_colour,
										'group_legend' 		=> $group_legend,
										'group_receive_pm'	=> $group_pm);
							group_create($group_id, $type, $group_name, $desc, $group_attributes);
							
							group_user_add($group_id, $user_id_ary = array($gen_ids[$army],));
							if($default)
							{
								group_user_attributes('default', $group_id, $user_id_ary = array($gen_ids[$army],));
							}
							group_user_attributes('promote', $group_id, $user_id_ary = array($gen_ids[$army],));
						}
					}
					
					/*Create TA User Group*/
					$group_id = FALSE;
					$type = GROUP_CLOSED;
					$group_name = $this->config['ta_name'];
					$group_colour = $this->config['ta_colour'];
					$group_legend = 1;
					$group_pm = 1;
					$group_attributes = array(
								'group_colour' 		=> $group_colour,
								'group_legend' 		=> $group_legend,
								'group_receive_pm'	=> $group_pm);
					group_create($group_id, $type, $group_name, $desc, $group_attributes);
					
					group_user_add($group_id, $user_id_ary = array($user_id_ta,));
					group_user_attributes('default', $group_id, $user_id_ary = array($user_id_ta,));
					group_user_attributes('promote', $group_id, $user_id_ary = array($user_id_ta,));
					/*Add TA User Group to ABC Admins*/
					$abc_admins = $this->config['start_perm_groups'];
					$abc_admins .= $group_name.',';
					$this->config->set('start_perm_groups', $abc_admins);
					
					/*Create Draft Table*/
					$sql = 'CREATE TABLE abc_draft (user_id int, username varchar(255), division varchar(255), availability varchar(255), notes varchar(255))';
					$result = $this->db->sql_query($sql);
					$this->db->sql_freeresult($result);
					
					$this->template->assign_var('ACP_FAILED', false);
					$this->template->assign_var('ACP_SUCCESS', true);
				}
			}
			return $this->helper->render('abc_start.html', $name);
		}
		/*End Campaign*/
		if($this->request->is_set_post('finish_submit'))
		{
			include $this->root_path . 'includes/functions_user.php';
			/*Delete Army User Groups*/
			$armies = array('army1_', 'armyb_');
			$groups = array('', ' Officers', ' HC', ' General');
			foreach($armies as $army)
			{
				foreach($groups as $group)
				{				
					$group_name = $this->config[$army.'name'].$group;
					
					$sql = "SELECT * FROM ". GROUPS_TABLE ." WHERE group_name = '".$group_name."'";
					$result = $this->db->sql_query($sql);
					$group_id = $this->db->sql_fetchfield('group_id');
					$this->db->sql_freeresult($result);
					if($group_id)
					{
						group_delete($group_id);
					}
				}
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
			/*Delete TA User Group*/
			$sql = "SELECT * FROM ". GROUPS_TABLE ." WHERE group_name = '".$group_name."'";
			$result = $this->db->sql_query($sql);
			$group_id = $this->db->sql_fetchfield('group_id');
			$this->db->sql_freeresult($result);
			if($group_id)
			{
				group_delete($group_id);
			}
			
			/*Delete Draft Table*/
			$sql = 'DROP TABLE abc_draft';
			$result = $this->db->sql_query($sql);
			$this->db->sql_freeresult($result);
			
			/*Reset Campaign Settings*/
			$this->config->set('campaign_state', '0');
			$this->config->set('campaign_name', '');
			$this->config->set('campaign_divisions', 'Infantry,Armour,Air');
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
			
			return $this->helper->render('abc_finish.html', $name);
		}
		/*Join Draft*/
		if($this->request->is_set_post('draft_submit'))
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
					$sql = "SELECT * FROM ". GROUPS_TABLE ." WHERE group_name = '".$group_name."'";
					$result = $this->db->sql_query($sql);
					$group_id = $this->db->sql_fetchfield('group_id');
					$this->db->sql_freeresult($result);
					
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
			return $this->helper->render('abc_draft.html', $name);
		}
		/*Leave Draft*/
		if($this->request->is_set_post('draft_leave'))
		{
			$user_id = $this->user->data['user_id'];
			$sql = "DELETE FROM abc_draft WHERE user_id = $user_id";
			$result = $this->db->sql_query($sql);
			$this->db->sql_freeresult($result);
			$this->template->assign_var('ABC_DRAFT_LEFT', true);
			
			return $this->helper->render('abc_draft.html', $name);
		}
		/*Run Draft*/
		if($this->request->is_set_post('run_draft'))
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
				$sql = "SELECT group_id FROM ". GROUPS_TABLE ." WHERE group_name = '".$army1."'";
				$result = $this->db->sql_query($sql);
				$group_ids[$army1] = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				$sql = "SELECT group_id FROM ". GROUPS_TABLE ." WHERE group_name = '".$armyb."'";
				$result = $this->db->sql_query($sql);
				$group_ids[$armyb] = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				$sql = "SELECT group_id FROM ". GROUPS_TABLE ." WHERE group_name = '".$ta_army."'";
				$result = $this->db->sql_query($sql);
				$group_ids[$ta_army] = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				
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
				'ABC_COMPLETE_DRAFT_LIST'		=> $draft_list,
			));
			
			return $this->helper->render('abc_draft_list.html', $name);
		}
		/*Set Army List*/
		if($this->request->is_set_post('army_set'))
		{
			$complete_army_list = '';
			/*Get user army*/
			$army = '';
			$army_name = '';
			$armies = array('army1', 'army2', 'ta');
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
					'ABC_COMPLETE_ARMY_LIST'	=> 'set_to failed',
				));
				return $this->helper->render('abc_army.html', $name);
			}
			
			/**Promote/demote users**/
			if( ($army == 'army1' || $army == 'army2') && $set_to != 'none')
			{
				/*Get user ids for army*/
				$sql = "SELECT group_id FROM ".GROUPS_TABLE." WHERE group_name = '$army_name'";
				$result = $this->db->sql_query($sql);
				$group_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$group_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing Army Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $group_id";
				$result = $this->db->sql_query($sql);
				$rowset = $this->db->sql_fetchrowset();
				$this->db->sql_freeresult($result);
				if(!$rowset)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing All Army user_id');
					return $this->helper->render('abc_army.html', $name);
				}
				
				/*Get usernames for army*/
				$user_id = [];
				$sql_user_id = 'user_id = '.$rowset[0]['user_id'];
				for($i=0; $i<count($rowset); $i++)
				{
					$user_id[] = $rowset[$i]['user_id'];
					$sql_user_id .= ' OR user_id = '.$rowset[$i]['user_id'];
				}
				$sql = "SELECT user_id, username FROM ".USERS_TABLE." WHERE group_id = $sql_user_id";
				$result = $this->db->sql_query($sql);
				$rowset = $this->db->sql_fetchrowset();
				$this->db->sql_freeresult($result);
				if(!$rowset)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', $sql_user_id.'<br>Missing All Army username');
					return $this->helper->render('abc_army.html', $name);
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
					$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name." HC'";
					$result = $this->db->sql_query($sql);
					$HC_id = $this->db->sql_fetchfield('group_id');
					$this->db->sql_freeresult($result);
					if(!$HC_id)
					{
						$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing HC Group');
						return $this->helper->render('abc_army.html', $name);
					}
					
					$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name." Officers'";
					$result = $this->db->sql_query($sql);
					$officer_id = $this->db->sql_fetchfield('group_id');
					$this->db->sql_freeresult($result);
					if(!$officer_id)
					{
						$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing Officer Group');
						return $this->helper->render('abc_army.html', $name);
					}
					
					include $this->root_path . 'includes/functions_user.php';
					/*Strip all groups*/	
					//foreach($user_id as $uid)
					//{
						group_user_del($HC_id, $user_id_ary = $user_id);//array($uid,));
						group_user_del($officer_id, $user_id_ary = $user_id);//array($uid,));
						/*Assign new groups*/
						if($set_to == 'set_HC')
						{
							group_user_add($HC_id, $user_id_ary = $user_id);//array($uid,));
							group_user_add($officer_id, $user_id_ary = $user_id);//array($uid,));
						}
						elseif($set_to == 'set_officer')
						{
							group_user_add($officer_id, $user_id_ary = $user_id);//array($uid,));
						}
					//}
				}
			}
			
			/*Rebuild Army List*/
			if($army == 'army1' || $army == 'army2')
			{
				$selector = '';
				
				/*General*/
				/*We need to General group id and user id but can get General name from config more easily*/
				$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name." General'";
				$result = $this->db->sql_query($sql);
				$general_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$general_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing General Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $general_id";
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
				
				$hc_army_list = "<h2>".$this->user->lang['ABC_ARMY_GENERAL']."</h2>";
				$hc_army_list .= "<p>".$this->config[$army.'_general']."</p>";
				$hc_army_list .= "<br>";
				
				/*HC*/
				$high_rank = $this->permissions->whitelist(array($army_name." General",));
				$hc_army_list .= "<h2>".$this->user->lang['ABC_ARMY_HC']."</h2>";
				$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name." HC'";
				$result = $this->db->sql_query($sql);
				$HC_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$HC_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing HC Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $HC_id";
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
					foreach($HC_user_id as $user_id)
					{
						$sql = "SELECT username FROM ".USERS_TABLE." WHERE user_id = $user_id";
						$result = $this->db->sql_query($sql);
						$username = $this->db->sql_fetchfield('username');
						$this->db->sql_freeresult($result);
						if($username)
						{
							$hc_army_list .= "<p>$username";
							if($high_rank)
							{
								$username = str_replace(" ", "_", $username);
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
				$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name." Officers'";
				$result = $this->db->sql_query($sql);
				$officer_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$officer_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing Officer Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $officer_id";
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
					foreach($officer_user_id as $user_id)
					{
						$sql = "SELECT username FROM ".USERS_TABLE." WHERE user_id = $user_id";
						$result = $this->db->sql_query($sql);
						$username = $this->db->sql_fetchfield('username');
						$this->db->sql_freeresult($result);
						if($username)
						{
							$officer_army_list .= "<p>$username";
							if($high_rank)
							{
								$username = str_replace(" ", "_", $username);
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
				$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name."'";
				$result = $this->db->sql_query($sql);
				$squaddie_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$squaddie_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing Army Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $squaddie_id";
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
					foreach($squaddie_user_id as $user_id)
					{
						$sql = "SELECT username FROM ".USERS_TABLE." WHERE user_id = $user_id";
						$result = $this->db->sql_query($sql);
						$username = $this->db->sql_fetchfield('username');
						$this->db->sql_freeresult($result);
						if($username)
						{
							$squaddie_army_list .= "<p>$username";
							if($high_rank)
							{
								$username = str_replace(" ", "_", $username);
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
				$sql = 'SELECT group_id FROM '.GROUPS_TABLE." WHERE group_name = '".$army_name."'";
				$result = $this->db->sql_query($sql);
				$ta_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$ta_id)
				{
					$this->template->assign_var('ABC_COMPLETE_ARMY_LIST', 'Missing TA Group');
					return $this->helper->render('abc_army.html', $name);
				}
				$sql = "SELECT user_id FROM ".USER_GROUP_TABLE." WHERE group_id = $ta_id";
				$result = $this->db->sql_query($sql);
				$rowset = $this->db->sql_fetchrowset();
				$this->db->sql_freeresult($result);
				$ta_user_id = [];
				if($rowset)
				{
					for($i=0; $i<count($rowset); $i++)
					{
						$ta_user_id[] = $rowset[$i]['user_id'];
					}
					foreach($ta_user_id as $user_id)
					{
						$sql = "SELECT username FROM ".USERS_TABLE." WHERE user_id = $user_id";
						$result = $this->db->sql_query($sql);
						$username = $this->db->sql_fetchfield('username');
						$this->db->sql_freeresult($result);
						if($username)
						{
							$complete_army_list .= "<p>$username</p>";;
						}
					}
				}
			}
			
			$this->template->assign_vars(array(
				'ABC_ARMY_NAME'				=> $army_name,
				'ABC_COMPLETE_ARMY_LIST'	=> $complete_army_list,
			));
			return $this->helper->render('abc_army.html', $name);
		}

		return $this->helper->render('abc_home.html', $name);
	}
}
