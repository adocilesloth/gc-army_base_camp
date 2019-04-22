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
		$army_list = '';
		/*Get campaign_id*/
		$sql = "SELECT MAX(campaign_id) FROM abc_campaigns";
		$result = $this->db->sql_query($sql);
		$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
		$this->db->sql_freeresult($result);
		
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
		
		/*Get army_id*/
		$sql = "SELECT army_id FROM abc_armies WHERE campaign_id = $campaign_id AND army_name = '$army_name'";
		$result = $this->db->sql_query($sql);
		$army_id = $this->db->sql_fetchfield('army_id');
		$this->db->sql_freeresult($result);
		
		/*Get Divisions*/
		$sql = "SELECT division_id, division_name, division_icon FROM abc_divisions WHERE army_id = $army_id ORDER BY division_id ASC";
		$result = $this->db->sql_query($sql);
		$divisions = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		/*Get user Rank*/
		$user_id = $this->user->data['user_id'];
		$sql = "SELECT ar.rank_order FROM abc_users AS au
				JOIN abc_ranks AS ar ON ar.rank_id = au.rank_id
				WHERE au.campaign_id = $campaign_id AND au.army_id = $army_id AND au.user_id = $user_id";
		$result = $this->db->sql_query($sql);
		$user_rank_order = $this->db->sql_fetchfield('rank_order');
		$this->db->sql_freeresult($result);
		
		/*User is Officer */
		$user_is_officer = $this->permissions->whitelist(array($army_name." Officers"));
		
		/*Build Army list*/
		$army_list .= "<div class=\"abc_army\">";
		
		/*HC Division*/
		$army_list .= "<div></div>";
		$army_list .= "<div class=\"abc_army_division\">";
		$idx = 0;
		if($divisions[$idx]['division_icon'] != '')
		{
			$division_image = $divisions[$idx]['division_icon'];
			$army_list .= "<div align=\"center\"><img src=\"/$division_image\" width=\"150\"></div><br>";
		}
		$division_name = $divisions[$idx]['division_name'];
		$army_list .= "<div class=\"abc_division_name\">$division_name</div><br>";
		$division_id = $divisions[$idx]['division_id'];
		$sql = "SELECT au.user_bf3_name, au.rank_id, ar.rank_order, ar.rank_short, ar.rank_img FROM abc_users AS au
				JOIN abc_ranks AS ar ON ar.rank_id = au.rank_id
				WHERE au.campaign_id = $campaign_id AND au.army_id = $army_id AND au.division_id = $division_id
				ORDER BY ar.rank_order ASC";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if($rowset)
		{
			foreach($rowset as $row)
			{
				$rank_img = $row['rank_img'];
				$rank_short = $row['rank_short'];
				$rank_order = $row['rank_order'];
				$username = $row['user_bf3_name'];
				if($rank_img != '')
				{
					$army_list .= "<img src=\"/$rank_img\" height=\"25\"> ";
				}
				$army_list .= "$rank_short $username";
				if( ($user_is_officer && $user_rank_order < $rank_order) || $user_rank_order == 1 || $army == 'ta')
				{
					$username = str_replace(" ", "_", $username);
					$army_list .= " <input type=\"checkbox\" id=\"$username\" name=\"$username\">";
				}
				$army_list .= "<br>";
			}
		}
		$army_list .= "</div>";
		$army_list .= "<div></div>";
		
		$min_div_num = 2;
		if($army == 'ta')
		{
			$min_div_num = 1;
		}
		
		/*Other Divisions*/
		if(count($divisions) > $min_div_num)
		{
			for($i=$min_div_num; $i<count($divisions); $i++)
			{
				$army_list .= "<div class=\"abc_army_division\">";
				if($divisions[$i]['division_icon'] != '')
				{
					$division_image = $divisions[$i]['division_icon'];
					$army_list .= "<div align=\"center\"><img src=\"/$division_image\" width=\"150\"></div><br>";
				}
				$division_name = $divisions[$i]['division_name'];
				$army_list .= "<div class=\"abc_division_name\">$division_name</div><br>";
				$division_id = $divisions[$i]['division_id'];
				$sql = "SELECT au.user_bf3_name, au.rank_id, ar.rank_order, ar.rank_short, ar.rank_img FROM abc_users AS au
						JOIN abc_ranks AS ar ON ar.rank_id = au.rank_id
						WHERE au.campaign_id = $campaign_id AND au.army_id = $army_id AND au.division_id = $division_id
						ORDER BY ar.rank_order ASC";
				$result = $this->db->sql_query($sql);
				$rowset = $this->db->sql_fetchrowset();
				$this->db->sql_freeresult($result);
				if($rowset)
				{
					foreach($rowset as $row)
					{
						$rank_img = $row['rank_img'];
						$rank_short = $row['rank_short'];
						$rank_order = $row['rank_order'];
						$username = $row['user_bf3_name'];
						if($rank_img != '')
						{
							$army_list .= "<img src=\"/$rank_img\" height=\"25\"> ";
						}
						$army_list .= "$rank_short $username";
						if( ($user_is_officer && $user_rank_order < $rank_order) || $user_rank_order == 1 || $army == 'ta')
						{
							$username = str_replace(" ", "_", $username);
							$army_list .= " <input type=\"checkbox\" id=\"$username\" name=\"$username\">";
						}
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
		if($army != 'ta')
		{
			$army_list .= "<div></div>";
			$army_list .= "<div class=\"abc_army_division\">";
			$idx = 1;
			if($divisions[$idx]['division_icon'] != '')
			{
				$division_image = $divisions[$idx]['division_icon'];
				$army_list .= "<div align=\"center\"><img src=\"/$division_image\" width=\"150\"></div><br>";
			}
			$division_name = $divisions[$idx]['division_name'];
			$army_list .= "<div class=\"abc_division_name\">$division_name</div><br>";
			$division_id = $divisions[$idx]['division_id'];
			$sql = "SELECT au.user_bf3_name, au.rank_id, ar.rank_order, ar.rank_short, ar.rank_img FROM abc_users AS au
					JOIN abc_ranks AS ar ON ar.rank_id = au.rank_id
					WHERE au.campaign_id = $campaign_id AND au.army_id = $army_id AND au.division_id = $division_id
					ORDER BY ar.rank_order ASC";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			if($rowset)
			{
				foreach($rowset as $row)
				{
					$rank_img = $row['rank_img'];
					$rank_short = $row['rank_short'];
					$rank_order = $row['rank_order'];
					$username = $row['user_bf3_name'];
					if($rank_img != '')
					{
						$army_list .= "<img src=\"/$rank_img\" height=\"25\"> ";
					}
					$army_list .= "$rank_short $username";
					if( ($user_is_officer && $user_rank_order < $rank_order) || $user_rank_order == 1)
					{
						$username = str_replace(" ", "_", $username);
						$army_list .= " <input type=\"checkbox\" id=\"$username\" name=\"$username\">";
					}
					$army_list .= "<br>";
				}
			}
			$army_list .= "</div>";
			$army_list .= "<div></div>";
		}
		
		/*Assign Medal/Rank/Division, Officer and up*/
		if($user_is_officer || $army == 'ta')
		{
			/*Medal list*/
			$sql = "SELECT medal_name, medal_id FROM abc_medals WHERE army_id = $army_id";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			$army_list .= "<div>";
			if($rowset)
			{
				$army_list .= "<select name=\"medal_choice\" id=\"medal_choice\">";
				$army_list .= "<option value=\"none\" selected=\"selected\"> </option>";
				for($i=0; $i<count($rowset); $i++)
				{
					$medal_name = $rowset[$i]['medal_name'];
					$medal_id = $rowset[$i]['medal_id'];
					$army_list .= "<option value=\"$medal_id\">$medal_name</option>";
				}
				$army_list .= "</select> ";
				$army_list .= "<input type=\"submit\" name=\"award_medal\" id=\"award_medal\" value=\"".$this->user->lang['ABC_ARMY_MEDAL']."\" class=\"button1\"/>";
				$army_list .= "<br>";
				$army_list .= "<textarea class=\"inputbox abc_description\" name=\"medal_reason\" cols=\"40\" rows=\"5\" maxlength=\"309\" placeholder=\"".$this->user->lang['ABC_ARMY_MEDAL_REASON']."\"></textarea>";
			}
			$army_list .= "</div>";	
			
			/*Rank List*/
			$sql = "SELECT rank_name, rank_id, rank_order FROM abc_ranks WHERE army_id = $army_id ORDER BY rank_order ASC";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			$army_list .= "<div>";
			if($rowset && count($rowset) > 1)
			{
				$army_list .= "<select name=\"rank_choice\" id=\"rank_choice\">";
				$army_list .= "<option value=\"none\" selected=\"selected\"> </option>";
				for($i=0; $i<count($rowset); $i++)
				{
					$rank_name = $rowset[$i]['rank_name'];
					$rank_id = $rowset[$i]['rank_id'];
					if($user_rank_order <= $rank_order || $user_rank_order == 1 || $army = 'ta')
					{
						$army_list .= "<option value=\"$rank_id\">$rank_name</option>";
					}
					else
					{
						break;
					}
				}
				$army_list .= "</select> ";
				$army_list .= "<input type=\"submit\" name=\"award_rank\" id=\"award_rank\" value=\"".$this->user->lang['ABC_ARMY_RANK']."\" class=\"button1\"/>";
			}
			$army_list .= "</div>";
			
			/*Division List*/
			$sql = "SELECT division_name, division_id FROM abc_divisions WHERE army_id = $army_id";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			$army_list .= "<div>";
			if($rowset && count($rowset) > 1)
			{
				$army_list .= "<select name=\"division_choice\" id=\"division_choice\">";
				$army_list .= "<option value=\"none\" selected=\"selected\"> </option>";
				for($i=0; $i<count($rowset); $i++)
				{
					$division_name = $rowset[$i]['division_name'];
					$division_id = $rowset[$i]['division_id'];
					$army_list .= "<option value=\"$division_id\">$division_name</option>";
				}
				$army_list .= "</select> ";
				$army_list .= "<input type=\"submit\" name=\"award_division\" id=\"award_division\" value=\"".$this->user->lang['ABC_ARMY_DIVISION']."\" class=\"button1\"/>";
			}
			$army_list .= "</div>";
		}
		
		/*End army_list*/
		$army_list .= "</div>";
		
		$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_ARMY_EXPLAIN']."</p>";
		$abc_content .= $army_list;
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	
	public function award_medal()
	{		
		$medal_id = $this->request->variable('medal_choice', 'none');
		if($medal_id == 'none')
		{
			$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_ARMY_ERR_MEDAL_NONE']."</p>";
		
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		$award_reason = sql_abc_clean($this->request->variable('medal_reason', ''));
		
		$user_ids = [];
		$campaign_id = -1;
		$army_name = '';
		if(!$this->get_user_ids($user_ids, $campaign_id, $army_name))
		{
			$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_ARMY_ERR_MEDAL_DATA']."</p>";
		
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		if(count($user_ids) > 0)
		{
			$award_time_stamp = strtotime("now");
			
			/*Get award_id*/
			$sql = "SELECT MAX(award_id) FROM abc_medal_awards";
			$result = $this->db->sql_query($sql);
			$award_id = $this->db->sql_fetchfield('MAX(award_id)');
			$this->db->sql_freeresult($result);
						
			foreach($user_ids as $user_id)
			{
				$award_id++;
				$sql = "INSERT INTO abc_medal_awards VALUES ($award_id, $campaign_id, $user_id, $medal_id, $award_time_stamp, '$award_reason')";
				$result = $this->db->sql_query($sql);
				$this->db->sql_freeresult($result);
			}
		}
		else
		{
			$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_ARMY_ERR_MEDAL_USER']."</p>";
		
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		$this->generate_ribbon_signature($user_ids, $campaign_id);
		
		$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_ARMY_MEDAL_SUCCESS']."</p>";
	
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	
	public function award_rank()
	{
		$rank_id = $this->request->variable('rank_choice', 'none');
		if($rank_id == 'none')
		{
			$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_ARMY_ERR_RANK_NONE']."</p>";
		
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		$user_ids = [];
		$campaign_id = -1;
		$army_name = '';
		if(!$this->get_user_ids($user_ids, $campaign_id, $army_name))
		{
			$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_ARMY_ERR_RANK_DATA']."</p>";
		
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		/*Get rank_phpbb_id and rank_is_officer*/
		$sql = "SELECT rank_phpbb_id, rank_is_officer FROM abc_ranks WHERE rank_id = $rank_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult($result);
		
		$rank_phpbb_id = $row['rank_phpbb_id'];
		$rank_is_officer = $row['rank_is_officer'];
		
		/*Award abc rank*/
		$sql = "UPDATE abc_users SET rank_id = $rank_id WHERE";
		$sql_users = "";
		foreach($user_ids as $user_id)
		{
			$sql_users .= " user_id = $user_id  OR";
		}
		$sql .= $sql_users;
		$sql = substr($sql, 0, strlen($sql)-3);
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Award phpbb rank*/
		$sql = "UPDATE phpbb_users SET user_rank = $rank_phpbb_id WHERE";
		$sql .= $sql_users;
		$sql = substr($sql, 0, strlen($sql)-3);
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Get Officer group_id*/
		$sql = "SELECT group_id, group_name FROM ".GROUPS_TABLE." WHERE group_name = '".$army_name." Officers'";
		$result = $this->db->sql_query($sql);
		$group_id = $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);
		
		/*Strip officer group*/
		include $this->root_path . 'includes/functions_user.php';
		group_user_del($group_id, $user_id_ary = $user_ids);
		
		/*If officer rank, add to officer group*/
		if($rank_is_officer == 1)
		{
			group_user_add($group_id, $user_id_ary = $user_ids);
		}
		
		$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_ARMY_RANK_SUCCESS']."</p>";
	
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	
	public function award_division()
	{
		$division_id = $this->request->variable('division_choice', 'none');
		if($division_id == 'none')
		{
			$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_ARMY_ERR_DIVISION_NONE']."</p>";

			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		$user_ids = [];
		$campaign_id = -1;
		$army_name = '';
		if(!$this->get_user_ids($user_ids, $campaign_id, $army_name))
		{
			$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_ARMY_ERR_DIVISION_DATA']."</p>";

			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		/*Get division_is_hc*/
		$sql = "SELECT division_is_hc FROM abc_divisions WHERE division_id = $division_id";
		$result = $this->db->sql_query($sql);
		$division_is_hc = $this->db->sql_fetchfield('division_is_hc');
		$this->db->sql_freeresult($result);
		
		/*Award abc division*/
		$sql = "UPDATE abc_users SET division_id = $division_id WHERE";
		$sql_users = "";
		foreach($user_ids as $user_id)
		{
			$sql_users .= " user_id = $user_id  OR";
		}
		$sql .= $sql_users;
		$sql = substr($sql, 0, strlen($sql)-3);
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Get HC group_id*/
		$sql = "SELECT group_id, group_name FROM ".GROUPS_TABLE." WHERE group_name = '".$army_name." HC'";
		$result = $this->db->sql_query($sql);
		$group_id = $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);
		
		/*Strip HC group*/
		include $this->root_path . 'includes/functions_user.php';
		group_user_del($group_id, $user_id_ary = $user_ids);
		
		/*If hc division, add to hc group*/
		if($division_is_hc == 1 && $army_name != $this->config['ta_name'])
		{
			group_user_add($group_id, $user_id_ary = $user_ids);
		}
		
		$abc_content = "<h2>$army_name ".$this->user->lang['ABC_ARMY_TITLE']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_ARMY_DIVISION_SUCCESS']."</p>";

		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	
	public function get_user_ids(&$user_ids, &$campaign_id, &$army_name)
	{
		/*Get campaign_id*/
		$sql = "SELECT MAX(campaign_id) FROM abc_campaigns";
		$result = $this->db->sql_query($sql);
		$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
		$this->db->sql_freeresult($result);
		
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
		
		/*Get army user data*/
		$sql = "SELECT au.user_id, au.user_bf3_name
				FROM abc_users AS au
				LEFT JOIN abc_armies AS aa ON au.army_id = aa.army_id
				WHERE aa.campaign_id = $campaign_id AND aa.army_name = '$army_name'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		if($rowset)
		{
			for($i=0; $i<count($rowset); $i++)
			{
				$username = str_replace(" ", "_", $rowset[$i]['user_bf3_name']);
				$do_things = $this->request->variable($username, false);
				if($do_things)
				{
					$user_ids[] = $rowset[$i]['user_id'];
				}
			}
		}
		else
		{
			return false;
		}
		return true;
	}
	
	public function generate_ribbon_signature($user_ids=[], $campaign_id=-1)
	{
		if(count($user_ids) == 0 || $campaign_id < 0)
		{
			return;
		}
		
		foreach($user_ids as $user_id)
		{
			$sql = "SELECT am.medal_ribbon FROM abc_medals AS am
					JOIN abc_medal_awards as awm ON awm.medal_id = am.medal_id
					WHERE awm.user_id = $user_id AND awm.campaign_id = $campaign_id
					ORDER BY am.medal_id ASC";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			$medal_img_paths = [];
			$medal_count = [];
			for($i=0; $i<count($rowset); $i++)
			{
				$medal_img_paths[] = $rowset[$i]['medal_ribbon'];
			}
			$medal_count = array_count_values($medal_img_paths);
			$medal_img_paths = array_unique($medal_img_paths);
			
			/*Create signature*/
			/*Mainly from old abc functions.ribbons.php*/
			$num_rows = ceil(count($medal_img_paths)/5);
			$width = 189;
			$height = $num_rows * 13 + ($num_rows - 1);
			
			//CREATE IMAGE & COLORS
			$image = imagecreatetruecolor($width, $height);
			imagesavealpha($image, true);
			imagealphablending($image, true);
			$white = imagecolorallocatealpha($image, 255, 255, 255, 127);
			imagefill($image, 0, 0, $white);
			imagecolortransparent($image, $white);
			$shadow = imagecolorallocatealpha($image, 0, 0, 0, 40);
			
			$row = 0;
			$medal = 0;
			foreach($medal_img_paths as $medal_img_path)
			{
				$num_awarded = $medal_count[$medal_img_path];
				$medal_img_attr = getimagesize($medal_img_path);
				$medal_img = imagecreatefromstring(file_get_contents($medal_img_path));
				imagealphablending($medal_img, true);
				imagesavealpha($medal_img, true);  

				$x = $medal * 38;
				$y = $row * 14;
				
				//LAST ROW GETS CENTERED
				if($row + 1 == $num_rows)
				{
					$num_lastrow = count($medal_img_paths) - ($num_rows - 1) * 5;
					$width_lastrow = $num_lastrow * 37 + ($num_lastrow - 1) * 1;
					$x_lastrow = (189 - $width_lastrow) / 2;
					$x = $x_lastrow + $medal * 38;
				}
				
				imagecopyresized($image, $medal_img, $x, $y, 0, 0, 37, 13, $medal_img_attr[0], $medal_img_attr[1]);
				
				//DECORATION FOR MORE THAN ONE AWARDS
				if($num_awarded > 1)
				{
					//GET STAR COLOR
					if($num_awarded > 7)
					{
						$deco_path = $this->root_path."/ext/globalconflict/abc/images/sigs/star_gold.png";
						$deco_number = $num_awarded - 7;
						if($deco_number > 3)
						{
							$deco_number = 3;
						}
					}
					elseif($num_awarded > 4)
					{
						$deco_path = $this->root_path."/ext/globalconflict/abc/images/sigs/star_silver.png";
						$deco_number = $num_awarded - 4;
					}
					else
					{
						$deco_path = $this->root_path."/ext/globalconflict/abc/images/sigs/star_bronze.png";
						$deco_number = $num_awarded - 1;
					}
					$deco_img = imagecreatefrompng($deco_path);
					imagealphablending($deco_img, true);
					imagesavealpha($deco_img, true);

					//CENTERING
					$gap = 1;
					$deco_width = 11 * $deco_number + $gap * ($deco_number - 1);
					$deco_x = $x + ((37 - $deco_width) / 2);

					//COPYING OF STARS
					$deco_index = 0;
					while($deco_index < $deco_number)
					{
						imagecopyresized($image, $deco_img, ($deco_x + $deco_index * (11 + $gap)), ($y + 1), 0, 0, 10, 11, 13, 13);
						$deco_index++;
					}      
				}
				
				//SAVE IMAGE
				imagegif($image, $this->root_path."/ext/globalconflict/abc/images/sigs/$campaign_id/$user_id.gif");
				
				if($medal == 4)
				{
					$medal = 0;
					$row++;
				}
				else
				{
					$medal++;
				}
			}
		}
		
		return;
	}
}