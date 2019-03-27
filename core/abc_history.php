<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_history
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
	
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\request\request $request,
		\phpbb\db\driver\driver_interface $db,
		$root_path)
	{
		$this->config		= $config;
		$this->template		= $template;
		$this->user			= $user;
		$this->request		= $request;
		$this->db			= $db;
		$this->root_path	= $root_path;
	}
	
	public function show_history($army_id = -1)
	{
		/*Get army_id*/
		if($army_id == -1)
		{
			/*If campaign is running*/
			if($this->config['campaign_state'] != '0')
			{
				$user_id = $this->user->data['user_id'];
				/*Get user army*/
				$sql = "SELECT MAX(army_id) FROM abc_users WHERE user_id = $user_id AND campaign_id = (SELECT MAX(campaign_id) FROM abc_users)";
				$result = $this->db->sql_query($sql);
				$army_id = $this->db->sql_fetchfield('MAX(army_id)');
				$this->db->sql_freeresult($result);
			}
			/*If campaign is not running or user not assigned army*/
			if(!$army_id || $army_id < 1)
			{
				$sql = "SELECT MIN(army_id) FROM abc_armies WHERE campaign_id = (SELECT MAX(campaign_id) FROM abc_users)";
				$result = $this->db->sql_query($sql);
				$army_id = $this->db->sql_fetchfield('MIN(army_id)');
				$this->db->sql_freeresult($result);
			}
		}
		
		/*Army selector*/
		$army_select = ""; "<select name=\"army_choice\" id=\"army_choice\">";
		
		$sql = "SELECT campaign_id, army_id, army_name FROM abc_armies ORDER BY army_id ASC";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		if($rowset)
		{
			$army_select .= "<select name=\"army_choice\" id=\"army_choice\">";
			foreach($rowset as $row)
			{
				$the_campaign_id = $row['campaign_id'];
				$the_army_name = $row['army_name'];
				$the_army_id = $row['army_id'];
				$selected = '';
				if($the_army_id == $army_id)
				{
					$selected = "selected=\"selected\"";
				}
				$army_select .= "<option value=\"$the_army_id\" $selected>$the_campaign_id. $the_army_name</option>";
			}
			$army_select .= "</select>";
			$army_select .= " <input type=\"submit\" name=\"select_army_history\" id=\"select_army_history\" value=\"".$this->user->lang['ABC_HISTORY_SELECT']."\" class=\"button1\"/><br>";
		}
		
		$sql = "SELECT army_name FROM abc_armies WHERE army_id = $army_id";
		$result = $this->db->sql_query($sql);
		$army_name = $this->db->sql_fetchfield('army_name');
		$this->db->sql_freeresult($result);
		
		if($army_id == 32) //21CW army is broken
		{
			$this->template->assign_vars(array(
				'ABC_HISTORY_SELECT'		=> $army_select,
				'ABC_STRUCTURE_ARMY_NAME'	=> $this->user->lang['ABC_HISTORY_STRUCTURE'].$army_name,
				'ABC_COMPLETE_ARMY_LIST'	=> $this->user->lang['ABC_NONE'],
				'ABC_MEDALS_ARMY_NAME'		=> $this->user->lang['ABC_HISTORY_MEDALS'].$army_name,
				'ABC_COMPLETE_MEDAL_LIST'	=> $this->user->lang['ABC_NONE'],
			));
			return;
		}
		
		/*Get Divisions*/
		$sql = "SELECT division_id, division_name, division_icon FROM abc_divisions WHERE army_id = $army_id ORDER BY division_id ASC";
		$result = $this->db->sql_query($sql);
		$divisions = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		/*Build Army list*/
		$army_list .= "<div class=\"abc_army\">";
		
		/*HC Division*/
		$army_list .= "<div></div>";
		$army_list .= "<div class=\"abc_army_division\">";
		$idx = 1;
		if($army_id % 3 == 0 || $army_id < 40) //TA or legacy army
		{
			$idx = 0;
		}
		if($divisions[$idx]['division_icon'] != '')
		{
			$division_image = '';
			if($army_id < 40)
			{
				$division_image .= $this->root_path . "/abc/";
			}
			$division_image .= $divisions[$idx]['division_icon'];
			$army_list .= "<div align=\"center\"><img src=\"/$division_image\" width=\"150\"></div><br>";
		}
		$division_name = $divisions[$idx]['division_name'];
		$army_list .= "<div class=\"abc_division_name\">$division_name</div><br>";
		$division_id = $divisions[$idx]['division_id'];
		$sql = "";
		$user_string = "";
		if($army_id < 40)
		{
			$user_string = 'username';
			$sql .= "SELECT ut.username, au.rank_id, ar.rank_short, ar.rank_img FROM abc_users AS au
					JOIN abc_ranks AS ar ON ar.rank_id = au.rank_id
					JOIN ".USERS_TABLE." AS ut ON au.user_id = ut.user_id
					WHERE au.army_id = $army_id AND au.division_id = $division_id 
					ORDER BY ar.rank_order ASC";
		}
		else
		{
			$user_string = 'user_bf3_name';
			$sql .= "SELECT au.user_bf3_name, au.rank_id, ar.rank_order, ar.rank_short, ar.rank_img FROM abc_users AS au
					JOIN abc_ranks AS ar ON ar.rank_id = au.rank_id
					WHERE au.army_id = $army_id AND au.division_id = $division_id
					ORDER BY ar.rank_order ASC";
		}
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		if($rowset)
		{
			foreach($rowset as $row)
			{
				$rank_img = "";
				if($army_id < 40)
				{
					$rank_img .= $this->root_path . "/abc/";
				}
				$rank_img .= $row['rank_img'];
				$rank_short = $row['rank_short'];
				$rank_order = $row['rank_order'];
				$username = $row[$user_string];
				if($rank_img != '' && $rank_img != $this->root_path . "/abc/")
				{
					$army_list .= "<img src=\"/$rank_img\" height=\"25\"> ";
				}
				$army_list .= "$rank_short $username";
				$army_list .= "<br>";
			}
		}
		$army_list .= "</div>";
		$army_list .= "<div></div>";
		
		$min_div_num = 2;
		if($army_id % 3 == 0) //TA army
		{
			$min_div_num = 1;
		}
		
		/*Other Divisions*/
		if(count($divisions) > $min_div_num)
		{
			for($i=$min_div_num; $i<count($divisions); $i++)
			{
				$army_list .= "<div class=\"abc_army_division\">";
				if($divisions[$idx]['division_icon'] != '')
				{
					$division_image = '';
					if($army_id < 40)
					{
						$division_image .= $this->root_path . "/abc/";
					}
					$division_image .= $divisions[$idx]['division_icon'];
					$army_list .= "<div align=\"center\"><img src=\"/$division_image\" width=\"150\"></div><br>";
				}
				$division_name = $divisions[$i]['division_name'];
				$army_list .= "<div class=\"abc_division_name\">$division_name</div><br>";
				
				$division_id = $divisions[$i]['division_id'];
				$sql = "";
				$user_string = "";
				if($army_id < 40)
				{
					$user_string = 'username';
					$sql .= "SELECT ut.username, au.rank_id, ar.rank_short, ar.rank_img FROM abc_users AS au
							JOIN abc_ranks AS ar ON ar.rank_id = au.rank_id
							JOIN ".USERS_TABLE." AS ut ON au.user_id = ut.user_id
							WHERE au.army_id = $army_id AND au.division_id = $division_id 
							ORDER BY ar.rank_order ASC";
				}
				else
				{
					$user_string = 'user_bf3_name';
					$sql .= "SELECT au.user_bf3_name, au.rank_id, ar.rank_order, ar.rank_short, ar.rank_img FROM abc_users AS au
							JOIN abc_ranks AS ar ON ar.rank_id = au.rank_id
							WHERE au.army_id = $army_id AND au.division_id = $division_id
							ORDER BY ar.rank_order ASC";
				}
				$result = $this->db->sql_query($sql);
				$rowset = $this->db->sql_fetchrowset();
				$this->db->sql_freeresult($result);
				
				if($rowset)
				{
					foreach($rowset as $row)
					{
						$rank_img = "";
						if($army_id < 40)
						{
							$rank_img .= $this->root_path . "/abc/";
						}
						$rank_img .= $row['rank_img'];
						$rank_short = $row['rank_short'];
						$rank_order = $row['rank_order'];
						$username = $row[$user_string];
						if($rank_img != '' && $rank_img != $this->root_path . "/abc/")
						{
							$army_list .= "<img src=\"/$rank_img\" height=\"25\"> ";
						}
						$army_list .= "$rank_short $username";
						$army_list .= "<br>";
					}
				}
				$army_list .= "</div>";
			}
			/*Add blanks*/
			$buffer = (count($divisions)-$min_div_num) % 3;
			if($buffer > 0)
			{
				$buffer = 3-$buffer;
				for($i=0; $i<$buffer; $i++)
				{
					$army_list .= "<div></div>";
				}
			}
		}
		
		/*New Recruits Division*/
		if($army_id % 3 != 0) //not TA army
		{
			$idx = 0;
			if($army_id < 40) //legacy army
			{
				$idx = 1;
			}
			$army_list .= "<div></div>";
			$army_list .= "<div class=\"abc_army_division\">";
			if($divisions[$idx]['division_icon'] != '')
			{
				$division_image = '';
				if($army_id < 40)
				{
					$division_image .= $this->root_path . "/abc/";
				}
				$division_image .= $divisions[$idx]['division_icon'];
				$army_list .= "<div align=\"center\"><img src=\"/$division_image\" width=\"150\"></div><br>";
			}
			$division_name = $divisions[$idx]['division_name'];
			$army_list .= "<div class=\"abc_division_name\">$division_name</div><br>";
			$division_id = $divisions[$idx]['division_id'];
			$sql = "";
			$user_string = "";
			if($army_id < 40)
			{
				$user_string = 'username';
				$sql .= "SELECT ut.username, au.rank_id, ar.rank_short, ar.rank_img FROM abc_users AS au
						JOIN abc_ranks AS ar ON ar.rank_id = au.rank_id
						JOIN ".USERS_TABLE." AS ut ON au.user_id = ut.user_id
						WHERE au.army_id = $army_id AND au.division_id = $division_id 
						ORDER BY ar.rank_order ASC";
			}
			else
			{
				$user_string = 'user_bf3_name';
				$sql .= "SELECT au.user_bf3_name, au.rank_id, ar.rank_order, ar.rank_short, ar.rank_img FROM abc_users AS au
						JOIN abc_ranks AS ar ON ar.rank_id = au.rank_id
						WHERE au.army_id = $army_id AND au.division_id = $division_id
						ORDER BY ar.rank_order ASC";
			}
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			if($rowset)
			{
				foreach($rowset as $row)
				{
					$rank_img = '';
					if($army_id < 40)
					{
						$rank_img .= $this->root_path . "/abc/";
					}
					$rank_img .= $row['rank_img'];
					$rank_short = $row['rank_short'];
					$rank_order = $row['rank_order'];
					$username = $row[$user_string];
					if($rank_img != '' && $rank_img != $this->root_path . "/abc/")
					{
						$army_list .= "<img src=\"/$rank_img\" height=\"25\"> ";
					}
					$army_list .= "$rank_short $username";
					$army_list .= "<br>";
				}
			}
			$army_list .= "</div>";
			$army_list .= "<div></div>";
		}
		/*End army_list*/
		$army_list .= "</div>";
		
		/*Build Medal list*/
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		/*Get existing medals*/
		$sql = "SELECT medal_id, medal_name, medal_img, medal_ribbon, medal_description FROM abc_medals WHERE army_id = $army_id";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		$medal_list = '';
		if($rowset)
		{
			$medal_list = "<div class=\"abc_medals\">";
			$medal_list .= "<div class=\"abc_medals_line\"><strong>".$this->user->lang['ABC_MEDAL_NAME_EXIST']."</strong></div>";
			$medal_list .= "<div class=\"abc_medals_line\"><strong>".$this->user->lang['ABC_MEDAL_IMAGE_EXIST']."</strong></div>";
			$medal_list .= "<div class=\"abc_medals_line\"><strong>".$this->user->lang['ABC_MEDAL_DESC_EXIST']."</strong></div>";
			for($i=0; $i<count($rowset); $i++)
			{
				$medal_name = sql_abc_unclean($rowset[$i]['medal_name']);
				$medal_desc = sql_abc_unclean($rowset[$i]['medal_description']);
				$medal_image = "";
				$medal_ribbon = "";
				if($army_id < 40)
				{
					$medal_image .= $this->root_path."/abc/";
					$medal_ribbon .= $this->root_path."/abc/";
				}
				$medal_image .= $rowset[$i]['medal_img'];
				$medal_ribbon .= $rowset[$i]['medal_ribbon'];
				
				$medal_list .= "<div class=\"abc_medals_line\">$medal_name</div>";
				$medal_list .= "<div class=\"abc_medals_line\">";
				if($medal_image != '' || $medal_image != $this->root_path . "/abc/")
				{
					$medal_list .= "<img src=\"/$medal_image\" width=\"100\">";
				}
				//$medal_list .= "<br><img src=\"/$medal_ribbon\" width=\"100\">";
				$medal_list .= "</div><div class=\"abc_medals_line\">$medal_desc</div>";
			}
			$medal_list .= "</div>";
		}
		else
		{
			$medal_list .= $this->user->lang['ABC_HISTORY_NO_MEDALS'];
		}
		
		$this->template->assign_vars(array(
			'ABC_HISTORY_SELECT'		=> $army_select,
			'ABC_STRUCTURE_ARMY_NAME'	=> $this->user->lang['ABC_HISTORY_STRUCTURE'].$army_name,
			'ABC_COMPLETE_ARMY_LIST'	=> $army_list,
			'ABC_MEDALS_ARMY_NAME'		=> $this->user->lang['ABC_HISTORY_MEDALS'].$army_name,
			'ABC_COMPLETE_MEDAL_LIST'	=> $medal_list,
		));
		return;
	}
	
	public function show_selected_history()
	{
		$army_id = $this->request->variable('army_choice', '');
		$this->show_history((int)$army_id);
		return;
	}
}