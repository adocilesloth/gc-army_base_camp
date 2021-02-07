<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_menu
{
	/** @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;
	
	/** @var \phpbb\user */
	protected $user;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;
	
	/* @var \globalconflict\abc\core\permissions */
	protected $permissions;
	
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		\globalconflict\abc\core\permissions $permissions)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->db = $db;
		$this->permissions = $permissions;
	}
	
	public function generate_menu()
	{
		/*Campaign is running?*/
		$running = false;
		if($this->config['campaign_state'] != '0')
		{
			$running = true;
		}
		
		$nav_buttons = '<h2>'.$this->user->lang['ABC_MENU'].'</h2>';
		$nav_buttons .= '<input type="submit" name="submit" id="submit" value="'.$this->user->lang['ABC_HOME'].'" class="abc_button"/>';
		
		$nav_buttons .= '<br><input type="submit" name="history" id="history" value="'.$this->user->lang['ABC_HISTORY'].'" class="abc_button"/>';
		$nav_buttons .= '<br><input type="submit" name="soldier" id="soldier" value="'.$this->user->lang['ABC_SOLDIER'].'" class="abc_button"/>';
		
		/*If user can enter draft*/
		if($running)
		{
			$no_draft = [];
			/*Get TA group*/
			$no_draft[] = $this->config['ta_name'];
			/*Get Army Groups*/
			$armies = array('army1_', 'armyb_');
			$groups = array('', ' Officers', ' HC', ' General');
			foreach($armies as $army)
			{
				foreach($groups as $group)
				{
					$no_draft[] = $this->config[$army.'name'].$group;
				}
			}
			/*blacklist groups that don't need drafting*/
			$draftable = $this->permissions->blacklist($no_draft);
			if($draftable)
			{
				$nav_buttons .= '<br><input type="submit" name="draft" id="draft" value="'.$this->user->lang['ABC_DRAFT'].'" class="abc_button"/>';
			}
			
			/*get if in draft list - they can still enter page but get a special message*/
			$sql = "SELECT MAX(campaign_id) FROM abc_campaigns";
			$result = $this->db->sql_query($sql);
			$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
			$this->db->sql_freeresult($result);
			
			$sql = "SELECT user_id FROM abc_users WHERE user_is_signed_up = 1 AND campaign_id = $campaign_id AND user_id = ";
			$sql .= $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$user_id = $this->db->sql_fetchfield('user_id');
			$this->db->sql_freeresult($result);
			if($user_id)
			{
				$this->template->assign_var('ABC_DRAFT_IN', true);
			}
		}
		/*If user can see draft list - TA and HC*/
		if($running)
		{
			$see_draft_list = [];
			$see_draft_list[] = $this->config['ta_name'];
			$see_draft_list[] = $this->config['army1_name'].' HC';
			$see_draft_list[] = $this->config['armyb_name'].' HC';
			
			$can_see_draft = $this->permissions->whitelist($see_draft_list);
			if($can_see_draft)
			{
				$nav_buttons .= '<br><input type="submit" name="draft_list" id="draft_list" value="'.$this->user->lang['ABC_DRAFT_LIST'].'" class="abc_button"/>';
			}
		}
		/*If user can see army list*/
		if($running)
		{
			$see_army_list = [];
			$see_army_list[] = $this->config['ta_name'];
			$see_army_list[] = $this->config['army1_name'];
			$see_army_list[] = $this->config['armyb_name'];
			
			$can_see_army = $this->permissions->whitelist($see_army_list);
			if($can_see_army)
			{
				$nav_buttons .= '<br><input type="submit" name="army_list" id="army_list" value="'.$this->user->lang['ABC_ARMY'].'" class="abc_button"/>';
			}
		}
		/*If user can create medals/ranks/divisions - HC and TA*/
		if($running)
		{
			$logistics_nav_buttons = '';
			$see_logistics = [];
			$see_logistics[] = $this->config['army1_name'].' HC';
			$see_logistics[] = $this->config['armyb_name'].' HC';
			$see_logistics[] = $this->config['ta_name'];
			$can_see_logistics = $this->permissions->whitelist($see_logistics);
			if($can_see_logistics)
			{
				$nav_buttons .= '<br><input type="submit" name="logistics_list" id="logistics_list" value="'.$this->user->lang['ABC_LOGISTICS'].'" class="abc_button"/>';
				$logistics_nav_buttons .= '</fieldset></div></div>';
			}
		}
		/*If user can create forums - Generals*/
		if($running)
		{
			$see_forums = [];
			$see_forums[] = $this->config['army1_name'].' General';
			$see_forums[] = $this->config['armyb_name'].' General';
			
			$can_create_forum = $this->permissions->whitelist($see_forums);
			if($can_create_forum)
			{
				$nav_buttons .= '<br><input type="submit" name="forum_list" id="forum_list" value="'.$this->user->lang['ABC_FORUM'].'" class="abc_button"/>';
			}
		}
		/*Battleday Signup*/
		if($running)
		{
			$see_signup = [];
			$see_signup[] = $this->config['army1_name'];
			$see_signup[] = $this->config['armyb_name'];
			$see_signup[] = $this->config['ta_name'];
			
			$can_see_signup = $this->permissions->whitelist($see_signup);
			if($can_see_signup)
			{
				$nav_buttons .= '<br><input type="submit" name="battle_signup" id="battle_signup" value="'.$this->user->lang['ABC_BATTLE_SIGNUP'].'" class="abc_button"/>';
			}
		}
		
		/*If user can create battledays - TA*/
		if($running)
		{
			$logistics_nav_buttons = '';
			$see_create_battleday = array($this->config['ta_name'],);
			$can_create_battleday = $this->permissions->whitelist($see_create_battleday);
			if($can_create_battleday)
			{
				$nav_buttons .= '<br><input type="submit" name="battle_list" id="battle_list" value="'.$this->user->lang['ABC_BATTLE'].'" class="abc_button"/>';
			}
		}
		
		/*Start/Stop campaign permissions*/
		$start_string = $this->config['start_perm_groups'];
		$start_array = explode(",", $start_string);
		$admin = $this->permissions->whitelist($start_array);
		if($admin)
		{
			if(!$running)
			{
				$nav_buttons .= '<br><br><input type="submit" name="start" id="start" value="'.$this->user->lang['ABC_START'].'" class="abc_button"/>';
			}
			else
			{
				$nav_buttons .= '<br><br><input type="submit" name="finish" id="finish" value="'.$this->user->lang['ABC_FINISH'].'" class="abc_button"/>';
			}
		}
		
		/*Solder Info*/
		$username = $this->user->data['username'];
		$soldier_info = "<h2>$username</h2>";
		if(!$running)
		{
			$soldier_info .= $this->user->lang['ABC_USER_NOCAMP'];
		}
		else
		{
			$sql = "SELECT user_id, army_id, division_id, rank_id, user_is_signed_up FROM abc_users WHERE campaign_id = (SELECT MAX(campaign_id) FROM abc_armies) AND user_id = ";
			$sql .= $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow();
			$this->db->sql_freeresult($result);
			if($row)
			{
				if($row['user_is_signed_up'] == 1)
				{
					$soldier_info .= $this->user->lang['ABC_USER_INDRAFT'];
				}
				else
				{
					$army_id = $row['army_id'];
					$division_id = $row['division_id'];
					$rank_id = $row['rank_id'];
					
					$sql = "SELECT army_name, army_colour, army_tag FROM abc_armies WHERE army_id = $army_id";
					$result = $this->db->sql_query($sql);
					$army_row = $this->db->sql_fetchrow();
					$this->db->sql_freeresult($result);
					
					$army_name = $army_row['army_name'];
					$army_colour = $army_row['army_colour'];
					$army_tag = $army_row['army_tag'];
					
					$soldier_info .= $this->user->lang['ABC_USER_INARMY'];
					$soldier_info .= "<span style=\"color:#$army_colour; font-weight:bold\">$army_name</span><br>";
					
					$sql = "SELECT ad.division_tag, ar.rank_tag 
							FROM abc_divisions as ad 
							JOIN abc_ranks as ar ON ad.army_id = ar.army_id 
							WHERE ar.army_id = $army_id AND ad.division_id = $division_id AND ar.rank_id = $rank_id";
					$result = $this->db->sql_query($sql);
					$tag_row = $this->db->sql_fetchrow();
					$this->db->sql_freeresult($result);
					
					$division_tag = $tag_row['division_tag'];
					$rank_tag = $tag_row['rank_tag'];
					
					$soldier_info .= $this->user->lang['ABC_USER_TAG'];
					$soldier_info .= "[".$army_tag.$division_tag.$rank_tag."]";
				}
			}
			else
			{
				$soldier_info .= $this->user->lang['ABC_USER_NODRAFT'];
				$soldier_info .= '<br><input type="submit" name="draft" id="draft" value="'.$this->user->lang['ABC_DRAFT_JOIN'].'" class="button1"/>';
			}
		}
		
		/*Signup Histograms*/
		$signup_info = '';
		if($running)
		{
			date_default_timezone_set('UTC');
			$now = strtotime('now');
			$sql = "SELECT battle_id, battle_name, battle_start, battle_length FROM abc_battles WHERE battle_start > $now ORDER BY battle_start ASC";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			if($rowset)
			{
				$signup_info .= "<div class=\"panel\"><div class=\"inner\">";
				
				$army1_name = $this->config['army1_name'];
				$armyb_name = $this->config['armyb_name'];
				$army1_colour = $this->config['army1_colour'];
				$armyb_colour = $this->config['armyb_colour'];
				
				$max_height = 100;
				
				for($j=0; $j<count($rowset); $j++)
				{
					$battle_id = $rowset[$j]['battle_id'];
					$battle_name = $rowset[$j]['battle_name'];
					$battle_start = $rowset[$j]['battle_start'];
					$battle_length = $rowset[$j]['battle_length'];
					
					$sbt = $this->utc_to_sbt($battle_start);
					
					/*Correct for SBT < -11*/
					if($sbt < -11)
					{
						$sbt += 24;
					}
					
					$army1_signups = array_fill(0, $battle_length, 0);
					$armyb_signups = array_fill(0, $battle_length, 0);
					
					/*Select all signups for battle_id*/
					$army1_name = $this->config['army1_name'];
					$sql = "SELECT absu.sign_up_hours, aa.army_name FROM abc_battle_sign_ups AS absu
							JOIN abc_users AS au ON absu.user_id = au.user_id
							JOIN abc_armies AS aa ON au.army_id = aa.army_id
							WHERE absu.battle_id = $battle_id AND aa.campaign_id = (SELECT MAX(campaign_id) FROM abc_armies)";
					$result = $this->db->sql_query($sql);
					$battle_rowset = $this->db->sql_fetchrowset();
					$this->db->sql_freeresult($result);
					
					foreach($battle_rowset as $battle_row)
					{
						$sign_up_army = $battle_row['army_name'];
						$sign_up_hours = $battle_row['sign_up_hours'];
						$sign_up_string = decbin($sign_up_hours);
				
						/*decbin clips leading zeros, so add them back*/
						while(strlen($sign_up_string) < $battle_length)
						{
							$sign_up_string = '0'.$sign_up_string;
						}
						/*Get signup hours*/
						$end = $battle_length-1;
						for($i=0; $i<$battle_length; $i++)
						{
							if($sign_up_string[$end-$i] == '1')
							{
								if($sign_up_army == $army1_name)
								{
									$army1_signups[$i]++;
								}
								elseif($sign_up_army == $armyb_name)
								{
									$armyb_signups[$i]++;
								}
							}
						}
					}//foreach($battle_rowset as $battle_row)
					
					
					$army1_max = max($army1_signups);
					$armyb_max = max($armyb_signups);
					$max = max(array($army1_max, $armyb_max));
					
					$signup_info .= "<div id=\"histogram_$j\" style=\"display: ";
					if($j == 0)
					{
						$signup_info.= "show;\"";
					}
					else
					{
						$signup_info.= "none;\"";
					}
					$signup_info .= ">";
					$signup_info .= "<h2>$battle_name</h2>";
					$signup_info .= "<div style=\"display:table;\">";
					
					/*Histograms*/
					$signup_info .= "<div style=\"display:table-row; vertical-align: bottom; height: ".$max_height."px !important;\">";
					for($i=0; $i<$battle_length; $i++)
					{
						if($army1_signups[$i] > 0)
						{
							$signup_info .= "<div class=\"abc_signup_bar_army\" style=\"height: ".($army1_signups[$i]/$max)*$max_height."px; background-color: #$army1_colour;\">$army1_signups[$i]</div>";
						}
						else
						{
							$signup_info .= "<div class=\"abc_signup_bar_army\" style=\"height: 1.25em; color: #$army1_colour\">$army1_signups[$i]</div>";
						}
						if($armyb_signups[$i] > 0)
						{
							$signup_info .= "<div class=\"abc_signup_bar_army\" style=\"height: ".($armyb_signups[$i]/$max)*$max_height."px; background-color: #$armyb_colour;\">$armyb_signups[$i]</div>";
						}
						else
						{
							$signup_info .= "<div class=\"abc_signup_bar_army\" style=\"height: 1.25em; color: #$armyb_colour\">$armyb_signups[$i]</div>";
						}
						$signup_info .= "<div class=\"abc_signup_bar_blank\"></div>";
					}
					$signup_info .= "</div>";
					
					/*Histogram Labels*/
					$signup_info .= "<div style=\"display:table-row;\">";
					for($i=0; $i<$battle_length; $i++)
					{
						$the_time = $sbt+$i;
						$signup_info .= "<div class=\"abc_signup_bar_label\">";
						if($the_time >= 0)
						{
							$signup_info .= "+";
						}
						$signup_info .= "$the_time</div>";
					}
					$signup_info .= "</div>";
					
					/*Previous/Next buttons*/
					$signup_info .= "<div style=\"display:table-row;\">";
					$signup_info .= "<div class=\"abc_signup_previous\">";
					if($j > 0)
					{
						$signup_info .= "<input type=\"button\" value=\"".$this->user->lang['ABC_BATTLEDAY_PREV']."\" class=\"button1\" onclick=\"show_previous_signups($j)\">";
					}
					$signup_info .= "</div>";
					
					$signup_info .= "<div class=\"abc_signup_next\">";
					if($j < count($rowset)-1)
					{
						$signup_info .= "<input type=\"button\" value=\"".$this->user->lang['ABC_BATTLEDAY_NEXT']."\" class=\"button1\" onclick=\"show_next_signups($j)\">";
					}
					$signup_info .= "</div></div>";
					
					$signup_info .= "</div>";//style="display:table;"
					$signup_info .= "</div>";//id="histogram_$j"
					//break;
					
				}//for($j=0; $j<count($rowset); $j++)
				$signup_info .= "</div></div>";
			}
		}
		
		$this->template->assign_vars(array(
			'ABC_NAV_BUTTONS'	=> $nav_buttons,
			'ACB_USER_INFO'		=> $soldier_info,
			'ACB_SIGNUP_HIST'	=> $signup_info,
		));
		
		return;
	}
	
	public function utc_to_sbt($utc)
	{
		date_default_timezone_set('UTC');
		/*If Winter time, SBT = 18:00 UCT*/
		/*If DST/Summer time, SBT = 17:00 UCT*/
		$sbt_zero = 18;
		
		$month = date('m', $utc);
		if($month <= 6)
		{
			/*Follow EU DST in spring*/
			date_default_timezone_set('Europe/London');
			$DST = date('I', $utc);
			if($DST)
			{
				$sbt_zero = 17;
			}
		}
		else
		{
			/*Follow US DST in autumn*/
			date_default_timezone_set('America/New_York');
			$DST = date('I', $utc);
			if($DST)
			{
				$sbt_zero = 17;
			}
		}
		
		date_default_timezone_set('UTC');
		$hour = date('H', $utc);
		$sbt = (int)$hour - $sbt_zero;
		
		return $sbt;
	}
}