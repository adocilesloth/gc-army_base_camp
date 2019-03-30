<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_soldier
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
	
	public function show_soldier($user_id = -1)
	{
		$soldier_select = $this->user->lang['ABC_SOLDIER_SELECT'];
		$soldier_select .= "<input type=\"text\" name=\"soldier_name\" value=\"\" maxlength=\"255\" size=\"20\" /> ";
		$soldier_select .= "<input type=\"submit\" name=\"soldier_search\" id=\"soldier_search\" value=\"".$this->user->lang['ABC_SOLDIER_SEARCH']."\" class=\"button1\"/>";
		$soldier_select .= "<br>";
		
		$sql = "SELECT MIN(campaign_id), MAX(campaign_id) FROM abc_campaigns";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult($result);
		$min_campaign_id = $row['MIN(campaign_id)'];
		$max_campaign_id = $row['MAX(campaign_id)'];
		
		if($user_id <= 0)
		{
			$user_id = $this->user->data['user_id'];
		}
		
		$username = $this->user->lang['ABC_SOLDIER_HISTORY'];
		$sql = "SELECT username FROM ".USERS_TABLE." WHERE user_id = $user_id";
		$result = $this->db->sql_query($sql);
		$username .= $this->db->sql_fetchfield('username');
		$this->db->sql_freeresult($result);
		
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		$soldier_list = '';
		for($campaign_id = $max_campaign_id; $campaign_id >= $min_campaign_id; $campaign_id--)//$min_campaign_id; $campaign_id--)
		{
			/*Get army, rank and division*/
			$sql = "SELECT ac.campaign_name, aa.army_name, aa.army_colour, ad.division_name, ar.rank_name FROM abc_armies AS aa
					JOIN abc_users as au on aa.army_id = au.army_id
					JOIN abc_divisions AS ad ON au.division_id = ad.division_id
					JOIN abc_ranks AS ar ON au.rank_id = ar.rank_id
					JOIN abc_campaigns as ac on aa.campaign_id = ac.campaign_id
					WHERE au.user_id = $user_id AND au.campaign_id = $campaign_id";
					
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow();
			$this->db->sql_freeresult($result);
			
			if(!$row)
			{
				continue;
			}
			
			$soldier_list .= "<div class=\"abc_soldier_hist\">";
			
			$campaign_name = sql_abc_unclean($row['campaign_name']);
			$army_name = sql_abc_unclean($row['army_name']);
			$army_colour = sql_abc_unclean($row['army_colour']);
			$division_name = sql_abc_unclean($row['division_name']);
			$rank_name = sql_abc_unclean($row['rank_name']);
			
			$soldier_list .= "<h2>$campaign_name</h2>";
			$soldier_list .= "<div class=\"abc_soldier_army\">";
			$soldier_list .= "<div>".$this->user->lang['ABC_SOLDIER_ARMY']."</div><div><span style=\"color:#$army_colour; font-weight:bold\">$army_name</span></div>";
			$soldier_list .= "<div>".$this->user->lang['ABC_SOLDIER_DIVISION']."</div><div>$division_name</div>";
			$soldier_list .= "<div>".$this->user->lang['ABC_SOLDIER_RANK']."</div><div>$rank_name</div>";
			$soldier_list .= "</div><br>";
			
			/*Get awarded medals*/
			$sql = '';
			if($campaign_id < 15)
			{
				$sql .= "SELECT am.medal_name, am.medal_img, awa.award_time_stamp, awa.award_reason FROM abc_medal_awards AS awa
						JOIN abc_users AS au on awa.user_id = au.abc_user_id
						JOIN abc_medals AS am on awa.medal_id = am.medal_id
						WHERE au.user_id = $user_id AND awa.campaign_id = $campaign_id
						ORDER BY awa.award_time_stamp DESC";
			}
			else
			{
				$sql .= "SELECT am.medal_name, am.medal_img, awa.award_time_stamp, awa.award_reason FROM abc_medal_awards AS awa
						JOIN abc_medals AS am on awa.medal_id = am.medal_id
						WHERE awa.user_id = $user_id AND awa.campaign_id = $campaign_id
						ORDER BY awa.award_time_stamp DESC";
			}
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			if(count($rowset)>0)
			{
				$abc_user_id = -1;
				$ribbon_path = "";
				if($campaign_id < 15)
				{
					$sql = "SELECT abc_user_id FROM abc_users WHERE user_id = $user_id AND campaign_id = $campaign_id";
					$result = $this->db->sql_query($sql);
					$abc_user_id = $this->db->sql_fetchfield('abc_user_id');
					$this->db->sql_freeresult($result);
					
					if($campaign_id == 2) //For some reason, the sigs for $campaign_id == 2 are in medals
					{
						$ribbon_path .= "/abc/images/cache/medals/";
					}
					else
					{
						$ribbon_path .= "/abc/images/cache/sigs/";
					}
				}
				else
				{
					$abc_user_id = $user_id;
					$ribbon_path .= "/ext/globalconflict/abc/images/sigs/";
				}
				
				$ribbon_path .= $campaign_id."/".$abc_user_id.".gif";
				if(file_exists($this->root_path.$ribbon_path))
				{
					$soldier_list .= "<img src=\"/$this->root_path.$ribbon_path\"><br>";
					$soldier_list .= "BBCode: <input type=\"text\" name=\"ribbons-path\" id=\"ribbons-path\" size=\"75\" value=\"[img]http://global-conflict.org".$ribbon_path."[/img]\" readonly=\"readonly\">";
					$soldier_list .= "<br><br>";
				}
				
				$soldier_list .= "<input type=\"button\" value=\"Show Medals\" class=\"button1\" onclick=\"show_medals($campaign_id)\">";
				$soldier_list .= "<br><div class=\"abc_medals_history\" id=\"camp_$campaign_id\" style=\"display: none;\">";
				$soldier_list .= "<div class=\"abc_medals_line\"><strong>".$this->user->lang['ABC_MEDAL_NAME_EXIST']."</strong></div>";
				$soldier_list .= "<div class=\"abc_medals_line\"><strong>".$this->user->lang['ABC_MEDAL_IMAGE_EXIST']."</strong></div>";
				$soldier_list .= "<div class=\"abc_medals_line\"><strong>".$this->user->lang['ABC_SOLDIER_REASON']."</strong></div>";
				$soldier_list .= "<div class=\"abc_medals_line\"><strong>".$this->user->lang['ABC_SOLDIER_DATE']."</strong></div>";
				foreach($rowset as $row)
				{
					$medal_name = sql_abc_unclean($row['medal_name']);
					$medal_image = '';
					if($campaign_id < 15)
					{
						$medal_image .= $this->root_path . "/abc/";
					}
					$medal_image .= $row['medal_img'];
					$award_reason = sql_abc_unclean($row['award_reason']);
					$award_time = date('Y-m-d', $row['award_time_stamp']);
					
					$soldier_list .= "<div class=\"abc_medals_line\">$medal_name</div>";
					$soldier_list .= "<div class=\"abc_medals_line\">";
					if($medal_image != '' || $medal_image != $this->root_path . "/abc/")
					{
						$soldier_list .= "<img src=\"/$medal_image\" width=\"100\">";
					}
					$soldier_list .= "</div>";
					$soldier_list .= "<div class=\"abc_medals_line\">$award_reason</div>";
					$soldier_list .= "<div class=\"abc_medals_line\">$award_time</div>";
				}
				$soldier_list .= "</div>";
			}
			else
			{
				$soldier_list .= $this->user->lang['ABC_SOLDIER_NO_MEDALS'];
			}
			
			$soldier_list .= "</div>";
		}
		
		$this->template->assign_vars(array(
			'ABC_SOLDIER_SELECT'		=> $soldier_select,
			'ABC_SOLDIER_NAME'			=> $username,
			'ABC_COMPLETE_SOLDIER_LIST'	=> $soldier_list,
		));
		return;
	}
	
	public function show_selected_soldier()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		$username = sql_abc_clean($this->request->variable('soldier_name', ''));
		if($username == '')
		{
			$this->show_soldier();
			return;
		}
		
		$sql = "SELECT user_id FROM ".USERS_TABLE." WHERE username = '$username'";
		$result = $this->db->sql_query($sql);
		$user_id = $this->db->sql_fetchfield('user_id');
		$this->db->sql_freeresult($result);
		
		if($user_id)
		{
			$this->show_soldier((int)$user_id);
		}
		else
		{
			$soldier_select = $this->user->lang['ABC_SOLDIER_SELECT'];
			$soldier_select .= "<input type=\"text\" name=\"soldier_name\" value=\"\" maxlength=\"255\" size=\"20\" /> ";
			$soldier_select .= "<input type=\"submit\" name=\"soldier_search\" id=\"soldier_search\" value=\"".$this->user->lang['ABC_SOLDIER_SEARCH']."\" class=\"button1\"/>";
			$soldier_select .= "<br>";
		
			$this->template->assign_vars(array(
				'ABC_SOLDIER_SELECT'		=> $soldier_select,
				'ABC_SOLDIER_NAME'			=> $username." ".$this->user->lang['ABC_SOLDIER_MISSING'],
				'ABC_COMPLETE_SOLDIER_LIST'	=> '',
			));
		}
		return;
	}
}