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
		
		$abc_content = "<h2>".$this->user->lang['ABC_DRAFT_TITLE']."</h2>";
		
		/*Get if alread in draft*/
		$sql = "SELECT user_id FROM abc_users WHERE user_is_signed_up = 1 AND campaign_id = (SELECT MAX(campaign_id) FROM abc_users) AND user_id = ";
		$sql .= $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$user_id = $this->db->sql_fetchfield('user_id');
		$this->db->sql_freeresult($result);
		if($user_id)
		{
			$abc_content .= "<p>".$this->user->lang['ABC_DRAFT_IN_DRAFT']."</p>";
			$abc_content .= "<fieldset class=\"submit-buttons\">";
			$abc_content .= "<input type=\"submit\" name=\"draft_leave\" id=\"draft_leave\" value=\"".$this->user->lang['ABC_DRAFT_LEAVE']."\" class=\"button1\"/>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		$abc_content .= "<p>".$this->user->lang['ABC_DRAFT_EXPLAIN']."</p>";
		$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\"><dl>";
		$abc_content .= "<dt><label for=\"draft_division\">".$this->user->lang['ABC_DRAFT_CHOOSE']."</label><br>";
		$abc_content .= "<span></span></dt>";
		$abc_content .= "<dd><select name=\"draft_division\" id=\"draft_division\">".$draft_select."</select></dd>";
		$abc_content .= "</dl><dl>";
		$abc_content .= "<dt><label for=\"draft_avail\">".$this->user->lang['ABC_DRAFT_AVAIL']."</label><br>";
		$abc_content .= "<span>".$this->user->lang['ABC_DRAFT_AVAIL_EXP']."</span></dt>";
		$abc_content .= "<dd><input type=\"text\" class=\"inputbox\" name=\"draft_avail\" value=\"\" maxlength=\"132\" /></dd>";
		$abc_content .= "</dl><dl>";
		$abc_content .= "<dt><label for=\"draft_local\">".$this->user->lang['ABC_DRAFT_LOCAL']."</label><br>";
		$abc_content .= "<span>".$this->user->lang['ABC_DRAFT_LOCAL_EXP']."</span></dt>";
		$abc_content .= "<dd><input type=\"text\" class=\"inputbox\" name=\"draft_local\" value=\"\" maxlength=\"52\" /></dd>";
		$abc_content .= "</dl><dl>";
		$abc_content .= "<dt><label for=\"draft_notes\">".$this->user->lang['ABC_DRAFT_NOTES']."</label><br>";
		$abc_content .= "<span>".$this->user->lang['ABC_DRAFT_NOTES_EXP']."</span></dt>";
		$abc_content .= "<dd><input type=\"text\" class=\"inputbox\" name=\"draft_notes\" value=\"\" maxlength=\"255\" /></dd>";
		$abc_content .= "</dl><br>";
		$abc_content .= "<p>".$this->user->lang['ABC_DRAFT_PW_EXPLAIN'];
		$abc_content .= $this->user->lang['ABC_DRAFT_PW_EXPL']."</p>";
		$abc_content .= "<dl><dt><label for=\"draft_pw\">".$this->user->lang['ABC_DRAFT_PW']."</label><br>";
		$abc_content .= "<span></span></dt>";
		$abc_content .= "<dd><input type=\"text\" class=\"inputbox\" name=\"draft_pw\" value=\"\" /></dd>";
		$abc_content .= "</dl></fieldset>";
		
		$abc_content .= "<fieldset class=\"submit-buttons\">";
		$abc_content .= "<input type=\"submit\" name=\"draft_submit\" id=\"draft_submit\" value=\"".$this->user->lang['ABC_DRAFT_JOIN']."\" class=\"button1\"/>";
		$abc_content .= "</fieldset>";
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	
	public function join_draft()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		/*If somene tries to be funny*/
		$password = $this->request->variable('draft_pw', '', true);
		if($password == "it" or $password == "it below")
		{
			$abc_content = "<h2>".$this->user->lang['ABC_DRAFT_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_DRAFT_FUNNY']."</p>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		}
		else
		{
			$default = false;
			$group_name = '';
			$colour = '000000';
			$user_time_stamp = strtotime("now");
			$other_nonsense = "0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0";
			
			/*store in user_soldierid in abc_users*/
			$username = $this->user->data['username'];
			$sql = "SELECT user_rank FROM phpbb_users WHERE username = '$username'";
			$result = $this->db->sql_query($sql);
			$user_soldierid = $this->db->sql_fetchfield('user_rank');
			$this->db->sql_freeresult($result);
			
			/*If a correct army password is entered*/
			if($password == sql_abc_unclean($this->config['army1_password']) or
				$password == sql_abc_unclean($this->config['armyb_password']) or
				$password == sql_abc_unclean($this->config['ta_password']))
			{
				$army = '';
				if($password == sql_abc_unclean($this->config['ta_password']))
				{
					$army = 'ta_';
					$default = true;
				}
				elseif($password == sql_abc_unclean($this->config['army1_password']))
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
				$sql = "SELECT group_id FROM ". GROUPS_TABLE ." WHERE group_name = '$group_name'";
				$result = $this->db->sql_query($sql);
				$group_id = $this->db->sql_fetchfield('group_id');
				$this->db->sql_freeresult($result);
				if(!$group_id)
				{
					$abc_content = "<h2>".$this->user->lang['ABC_DRAFT_TITLE']."</h2>";
					$abc_content .= "<p>".$this->user->lang['ABC_DRAFT_ERR_GID']."</p>";
					
					$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
					return;
				}
				
				include $this->root_path . 'includes/functions_user.php';
				$user_id = $this->user->data['user_id'];
				group_user_add($group_id, $user_id_ary = array($user_id,));
				group_user_attributes('default', $group_id, $user_id_ary = array($user_id,));
				
				/*Add user to abc_users*/			
				$sql = "SELECT ad.army_id, ad.division_id, ar.rank_id, ar.rank_phpbb_id, aa.campaign_id
						FROM abc_divisions AS ad
						JOIN abc_armies AS aa ON ad.army_id = aa.army_id
						JOIN abc_ranks AS ar ON ad.army_id = ar.army_id
						WHERE aa.campaign_id = (SELECT MAX(campaign_id) FROM abc_armies) AND ad.division_is_default = 1 AND ar.rank_order = 99 AND aa.army_name = '$group_name'";			
				$result = $this->db->sql_query($sql);
				$rowset = $this->db->sql_fetchrowset();
				$this->db->sql_freeresult($result);
				
				$campaign_id = $rowset[0]['campaign_id'];
				$army_id = $rowset[0]['army_id'];
				$division_id = $rowset[0]['division_id'];
				$rank_id = $rowset[0]['rank_id'];
				
				$sql = "SELECT MAX(abc_user_id) FROM abc_users";
				$result = $this->db->sql_query($sql);
				$abc_user_id = $this->db->sql_fetchfield('MAX(abc_user_id)');
				$this->db->sql_freeresult($result);
				$abc_user_id++;
				
				$sql = "INSERT INTO abc_users VALUES ($abc_user_id, $user_id, $campaign_id, $army_id, $division_id, $rank_id, 'img', 0, '$username', '', '', '', '', $user_time_stamp, '', $user_soldierid, $other_nonsense)";
				$result = $this->db->sql_query($sql);
				$this->db->sql_freeresult($result);
			}
			/*If joining a division draft*/
			else
			{
				$user_id = $this->user->data['user_id'];
				$username = $this->user->data['username'];
				$division = sql_abc_clean($this->request->variable('draft_division', ''));
				$location = sql_abc_clean($this->request->variable('draft_local', '', true));
				$availability = sql_abc_clean($this->request->variable('draft_avail', '', true));
				$notes = sql_abc_clean($this->request->variable('draft_notes', '', true));
				
				$sql = "SELECT MAX(campaign_id) FROM abc_campaigns";
				$result = $this->db->sql_query($sql);
				$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
				$this->db->sql_freeresult($result);
				
				$sql = "SELECT MAX(abc_user_id) FROM abc_users";
				$result = $this->db->sql_query($sql);
				$abc_user_id = $this->db->sql_fetchfield('MAX(abc_user_id)');
				$this->db->sql_freeresult($result);
				$abc_user_id++;
				
				$sql = "INSERT INTO abc_users VALUES ($abc_user_id, $user_id, $campaign_id, 0, 0, 0, 'img', 1, '$username', '$availability', '$location', '', '$notes', $user_time_stamp, '$division', $user_soldierid, $other_nonsense)";
				$result = $this->db->sql_query($sql);
				$this->db->sql_freeresult($result);
				
				$group_name = $division.' Player Draft';
			}
			
			$abc_content = "<h2>".$this->user->lang['ABC_DRAFT_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_DRAFT_JOIN_ARMY']."<span style=\"color:#$colour; font-weight:bold\">$group_name</span>!</p>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		}
		return;
	}
	
	public function leave_draft()
	{
		$user_id = $this->user->data['user_id'];
		
		$sql = "SELECT MAX(campaign_id) FROM abc_campaigns";
		$result = $this->db->sql_query($sql);
		$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
		$this->db->sql_freeresult($result);
		
		$sql = "DELETE FROM abc_users WHERE user_id = $user_id AND campaign_id = $campaign_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		$abc_content = "<h2>".$this->user->lang['ABC_DRAFT_TITLE']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_DRAFT_LEFT']."<p>";
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	
	public function draft_list()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		$campaign_divisions = $this->config['campaign_divisions'];
		$camp_div = explode(",", $campaign_divisions);
		$army1 = $this->config['army1_name'];
		$armyb = $this->config['armyb_name'];
		$ta_army = $this->config['ta_name'];
		$is_ta = $this->permissions->whitelist(array($ta_army,));
		
		$sql = "SELECT MAX(campaign_id) FROM abc_campaigns";
		$result = $this->db->sql_query($sql);
		$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
		$this->db->sql_freeresult($result);
		
		$draft_list = '';
		foreach($camp_div as $c_div)
		{
			$draft_list .= "<h2>".$c_div."</h2>";
			
			$sql = "SELECT user_bf3_name, user_availability, user_location, user_other_notes FROM abc_users WHERE Role = '$c_div' AND campaign_id = $campaign_id AND user_is_signed_up = 1";
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
					$draft_list .= $this->user->lang['ABC_DRAFT_LIST_NAME']." <b>".$rowset[$i]['user_bf3_name']."</b><br>";
					$draft_list .= $this->user->lang['ABC_DRAFT_AVAIL']." ".sql_abc_unclean($rowset[$i]['user_availability'])."<br>";
					$draft_list .= $this->user->lang['ABC_DRAFT_LOCAL']." ".sql_abc_unclean($rowset[$i]['user_location'])."<br>";
					$draft_list .= $this->user->lang['ABC_DRAFT_NOTES']." ".sql_abc_unclean($rowset[$i]['user_other_notes']);
					if($is_ta)
					{
						$draft_list .= "<br>";
						$draft_list .= $this->user->lang['ABC_DRAFT_LIST_ARMY']." ";
						$username = $rowset[$i]['user_bf3_name'];
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
		
		
		$abc_content = "<h2>".$this->user->lang['ABC_DRAFT_LIST_TITLE']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_DRAFT_LIST_EXPLAIN']."<p>";
		$abc_content .= $draft_list;
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	
	public function run_draft()
	{
		/*Get all players in draft*/
		$sql = "SELECT MAX(campaign_id) FROM abc_campaigns";
		$result = $this->db->sql_query($sql);
		$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
		$this->db->sql_freeresult($result);
		
		$sql = "SELECT user_id, user_bf3_name FROM abc_users WHERE campaign_id = $campaign_id AND user_is_signed_up = 1";
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
			
			/*Get army group_id, default division and new recruit rank*/
			$sql = "SELECT gt.group_id, gt.group_name, ad.army_id, ad.division_id, ar.rank_id, ar.rank_phpbb_id
					FROM abc_divisions AS ad
					JOIN abc_armies AS aa ON ad.army_id = aa.army_id
					JOIN abc_ranks AS ar ON ad.army_id = ar.army_id
					JOIN ".GROUPS_TABLE." AS gt on aa.army_name = gt.group_name
					WHERE aa.campaign_id = $campaign_id AND ad.division_is_default = 1 AND ar.rank_order = 99";
			$result = $this->db->sql_query($sql);
			$group_rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			if(count($group_rowset) != 3)
			{
				$abc_content = "<h2>".$this->user->lang['ABC_DRAFT_LIST_TITLE']."</h2>";
				$abc_content .= "<p>".$this->user->lang['ABC_DRAFT_LIST_EXPLAIN']."<p>";
				$abc_content .= $this->user->lang['ABC_DRAFT_ERR_WRONGNUM'];
				
				$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
				return;
			}
			
			$group_ids = [];
			$army_ids = [];
			$division_ids = [];
			$rank_ids = [];
			$rank_phpbb_id = [];
			
			for($i=0; $i<3; $i++)
			{
				if($group_rowset[$i]['group_name'] == $army1)
				{
					$group_ids[$army1] = $group_rowset[$i]['group_id'];
					$army_ids[$army1] = $group_rowset[$i]['army_id'];
					$division_ids[$army1] = $group_rowset[$i]['division_id'];
					$rank_ids[$army1] = $group_rowset[$i]['rank_id'];
					$rank_phpbb_ids[$army1] = $group_rowset[$i]['rank_phpbb_id'];
				}
				elseif($group_rowset[$i]['group_name'] == $armyb)
				{
					$group_ids[$armyb] = $group_rowset[$i]['group_id'];
					$army_ids[$armyb] = $group_rowset[$i]['army_id'];
					$division_ids[$armyb] = $group_rowset[$i]['division_id'];
					$rank_ids[$armyb] = $group_rowset[$i]['rank_id'];
					$rank_phpbb_ids[$armyb] = $group_rowset[$i]['rank_phpbb_id'];
				}
				elseif($group_rowset[$i]['group_name'] == $ta_army)
				{
					$group_ids[$ta_army] = $group_rowset[$i]['group_id'];
					$army_ids[$ta_army] = $group_rowset[$i]['army_id'];
					$division_ids[$ta_army] = $group_rowset[$i]['division_id'];
					$rank_ids[$ta_army] = $group_rowset[$i]['rank_id'];
					$rank_phpbb_ids[$ta_army] = $group_rowset[$i]['rank_phpbb_id'];
				}
			}
			
			$drafted_to = [];
			$drafted_to[$army1] = [];
			$drafted_to[$armyb] = [];
			$drafted_to[$ta_army] = [];
			
			/*See if any players have been assigned an army*/
			for($i=0; $i<count($rowset); $i++)
			{
				$username = $rowset[$i]['user_bf3_name'];
				$username = str_replace(" ", "_", $username);
				$selected = $this->request->variable($username, '');
				
				foreach($armies as $army)
				{
					/*If assigned an army, add user to army array*/
					if($selected == $army)
					{
						$drafted_to[$army][] = $rowset[$i]['user_id'];
						break;
					}
				}
			}
			
			/*Assign to army*/
			include $this->root_path . 'includes/functions_user.php';
			foreach($armies as $army)
			{
				$army_id = $army_ids[$army];
				$division_id = $division_ids[$army];
				$rank_id = $rank_ids[$army];
				$rank_phpbb_id = $rank_phpbb_ids[$army];
				
				if($drafted_to[$army])
				{
					/*Add to army group*/
					group_user_add($group_ids[$army], $user_id_ary = $drafted_to[$army]);
					group_user_attributes('default', $group_ids[$army], $user_id_ary = $drafted_to[$army]);
					/*Update abc_users*/
					$sql = "UPDATE abc_users SET army_id = $army_id, division_id = $division_id, rank_id = $rank_id, user_is_signed_up = 0 WHERE";
					$sql_users = "";
					foreach($drafted_to[$army] as $user_id)
					{
						$sql_users .= " user_id = $user_id OR";
					}
					$sql .= $sql_users;
					$sql = substr($sql, 0, strlen($sql)-3);
					$result = $this->db->sql_query($sql);
					$this->db->sql_freeresult($result);
					/*Update phpbb_users*/
					$sql = "UPDATE ".USERS_TABLE." SET user_rank = $rank_phpbb_id WHERE";
					$sql .= $sql_users;
					$sql = substr($sql, 0, strlen($sql)-3);
					$result = $this->db->sql_query($sql);
					$this->db->sql_freeresult($result);
				}
			}
		}
		
		/*Repopulate draft list*/
		$this->draft_list();
		return;
	}
}