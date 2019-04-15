<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_battleday
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
	
	public function battleday_list()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		/*Force UCT*/
		date_default_timezone_set('UTC');
		
		/*Create new battleday*/
		$battle_create = "<dl><dt><label for=\"battle_name\">".$this->user->lang['ABC_BATTLE_NAME']."</label><br><span></span></dt>";
		$battle_create .= "<dd><input type=\"text\" name=\"battle_name\" value=\"\" maxlength=\"34\" size=\"39\" /></dd></dl>";
		/*battle_start*/
		$battle_create .= "<dl><dt><label for=\"battle_start\">".$this->user->lang['ABC_BATTLE_START']."</label><br>";
		$battle_create .= "<span>".$this->user->lang['ABC_BATTLE_START_EXPL']."</span></dt>";
		$battle_create .= "<dd><input type=\"text\" name=\"battle_start\" value=\"\" maxlength=\"10\" size=\"10\" /></dd></dl>";
		/*battle_start_time*/
		$battle_create .= "<dl><dt><label for=\"battle_start_time\">".$this->user->lang['ABC_BATTLE_START_TIME']."</label><br><span></span></dt>";
		$battle_create .= "<dd>SBT <input type=\"text\" name=\"battle_start_time\" value=\"\" maxlength=\"3\" size=\"3\" /></dd></dl>";
		/*battle_length*/
		$battle_create .= "<dl><dt><label for=\"battle_length\">".$this->user->lang['ABC_BATTLE_LENGTH']."</label><br><span></span></dt>";
		$battle_create .= "<dd><input type=\"text\" name=\"battle_length\" value=\"\" maxlength=\"3\" size=\"3\" /> hours</dd></dl>";
		
		$battle_create .= "<dl><input type=\"submit\" name=\"create_battle\" id=\"create_battle\" value=\"".$this->user->lang['ABC_BATTLE_CREATE']."\" class=\"button1\"/></dl>";
		
		
		/*Existing battledays*/
		$current_time = strtotime("now");
		
		$sql = "SELECT battle_id, battle_name, battle_start, battle_length FROM abc_battles 
				WHERE battle_start > $current_time AND campaign_id = (SELECT MAX(campaign_id) FROM abc_battles)
				ORDER BY battle_start ASC";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(!$rowset)
		{
			$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_BATTLE_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_BATTLE_EXPLAIN']."</p>";
			$abc_content .= "</fieldset>";
					
			$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_BATTLE_NEW']."</h2>";
			$abc_content .= $battle_create;
			$abc_content .= "</fieldset>";
					
			$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_BATTLE_EXIST']."</h2>";
			$abc_content .=  $this->user->lang['ABC_NONE'];
			$abc_content .= "</fieldset>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		/*Create existing battle list*/
		$battle_list = "";
		for($i=0; $i<count($rowset); $i++)
		{
			$battle_id = $rowset[$i]['battle_id'];
			$battle_name = sql_abc_unclean($rowset[$i]['battle_name']);
			$battle_start = $rowset[$i]['battle_start'];
			$battle_length = $rowset[$i]['battle_length'];
			
			$battle_start_date = date("Y-m-d", $battle_start);
			$sbt_start_time = $this->utc_to_sbt($battle_start);
			
			/*Correct for SBT < -11*/
			if($sbt_start_time < -11)
			{
				$sbt_start_time += 24;
				$Ym = substr($battle_start_date, 0, 8);
				$d = substr($battle_start_date, 8, 2);
				$d = (int)$d;
				$d--;
				if($d < 10)
				{
					$battle_start_date = $Ym.'0'.$d;
				}
				else
				{
					$battle_start_date = $Ym.$d;
				}
			}
			
			$sbt_pm = '+';
			if($sbt_start_time < 0)
			{
				$sbt_pm = '';
			}
			
			$battle_list .= "<div class=\"abc_medal_edit\">";
			/*battle_name*/
			$battle_list .= "<dl><dt><label for=\"battle_name_$battle_id\">".$this->user->lang['ABC_BATTLE_NAME']."</label><br><span></span></dt>";
			$battle_list .= "<dd><input type=\"text\" name=\"battle_name_$battle_id\" value=\"$battle_name\" maxlength=\"34\" size=\"39\" /></dd></dl>";
			/*battle_start*/
			$battle_list .= "<dl><dt><label for=\"battle_start_$battle_id\">".$this->user->lang['ABC_BATTLE_START']."</label><br>";
			$battle_list .= "<span>".$this->user->lang['ABC_BATTLE_START_EXPL']."</span></dt>";
			$battle_list .= "<dd><input type=\"text\" name=\"battle_start_$battle_id\" value=\"$battle_start_date\" maxlength=\"10\" size=\"10\" /></dd></dl>";
			/*battle_start_time*/
			$battle_list .= "<dl><dt><label for=\"battle_start_time_$battle_id\">".$this->user->lang['ABC_BATTLE_START_TIME']."</label><br><span></span></dt>";
			$battle_list .= "<dd>SBT <input type=\"text\" name=\"battle_start_time_$battle_id\" value=\"$sbt_pm$sbt_start_time\" maxlength=\"3\" size=\"6\" /></dd></dl>";			
			/*battle_length*/
			$battle_list .= "<dl><dt><label for=\"battle_length_$battle_id\">".$this->user->lang['ABC_BATTLE_LENGTH']."</label><br><span></span></dt>";
			$battle_list .= "<dd><input type=\"text\" name=\"battle_length_$battle_id\" value=\"$battle_length\" maxlength=\"3\" size=\"6\" /> hours</dd></dl>";

			/*Edit this medal radio button*/
			$battle_list .= "<dl><dt><label for=\"".$battle_id."\">".$this->user->lang['ABC_BATTLE_EDIT_THIS']."</label></dt>";
			$battle_list .= "<dd><input type=\"radio\" name=\"battle_radio\" value=\"".$battle_id."\"></dd></dl>";
			/*Edit button*/
			$battle_list .= "<dl><input type=\"submit\" name=\"edit_battle\" id=\"edit_battle\" value=\"".$this->user->lang['ABC_BATTLE_EDIT']."\" class=\"button1\"/> ";
			/*Delete button*/
			$battle_list .= "<input type=\"submit\" name=\"delete_battle\" id=\"delete_battle\" value=\"".$this->user->lang['ABC_BATTLE_DELETE']."\" class=\"button1\"/></dl>";
			
			$battle_list .= "</div>";
		}
		
		$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= "<h2>".$this->user->lang['ABC_BATTLE_TITLE']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_BATTLE_EXPLAIN']."</p>";
		$abc_content .= "</fieldset>";
				
		$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= "<h2>".$this->user->lang['ABC_BATTLE_NEW']."</h2>";
		$abc_content .= $battle_create;
		$abc_content .= "</fieldset>";
				
		$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= "<h2>".$this->user->lang['ABC_BATTLE_EXIST']."</h2>";
		$abc_content .=  $battle_list;
		$abc_content .= "</fieldset>";
		$abc_content .= "</fieldset>";
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	public function add_battleday()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		date_default_timezone_set('UTC');
		
		/*get campaign_id*/
		$sql = "SELECT MAX(campaign_id) FROM abc_campaigns";
		$result = $this->db->sql_query($sql);
		$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
		$this->db->sql_freeresult($result);
		
		/*Get battle_id*/
		$sql = "SELECT MAX(battle_id) FROM abc_battles";
		$result = $this->db->sql_query($sql);
		$battle_id = $this->db->sql_fetchfield('MAX(battle_id)');
		$this->db->sql_freeresult($result);
		$battle_id++;
		
		$battle_name = sql_abc_clean($this->request->variable('battle_name', '', true));
		$battle_date = $this->request->variable('battle_start', '', false);
		$battle_start_time = (int)$this->request->variable('battle_start_time', '', false);
		$battle_length = (int)$this->request->variable('battle_length', '', false);
		
		$battle_start = $this->sbt_to_utc($battle_date, $battle_start_time);	
		$battle_time_stamp = strtotime('now');
		
		/*Create battle*/
		$sql = "INSERT INTO abc_battles VALUES ($battle_id, $campaign_id, '$battle_name', $battle_start, $battle_length, 0, $battle_time_stamp)";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload battle list*/
		$this->battleday_list();
		return;
	}
	
	public function edit_battleday()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		/*Get battles*/
		$rowset = $this->get_battle_db_row();
		if(!$rowset)
		{
			$this->battleday_list();
			return;
		}
		
		/*Get new information*/
		$battle_id = $rowset['battle_id'];
		$battle_name = sql_abc_clean($this->request->variable('battle_name_'.$battle_id, '', true));
		$battle_date = $this->request->variable('battle_start_'.$battle_id, '', false);
		$battle_start_time = (int)$this->request->variable('battle_start_time_'.$battle_id, '', false);
		$battle_length = (int)$this->request->variable('battle_length_'.$battle_id, '', false);
		
		$battle_start = $this->sbt_to_utc($battle_date, $battle_start_time);
		
		/*Edit battle*/
		$sql = "UPDATE abc_battles SET battle_name = '$battle_name', battle_start = $battle_start, battle_length = $battle_length WHERE battle_id = $battle_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload battle list*/
		$this->battleday_list();
		return;
	}
	
	public function delete_battleday()
	{
		$rowset = $this->get_battle_db_row();
		if(!$rowset)
		{
			$this->battleday_list();
			return;
		}
		
		$battle_id = $rowset['battle_id'];
		
		$sql = "DELETE FROM abc_battles WHERE battle_id = $battle_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload battle list*/
		$this->battleday_list();
		return;
	}
	
	public function battleday_signup($battle_id = -1)
	{
		/*Get campaign_id*/
		$sql = "SELECT MAX(campaign_id) from abc_campaigns";
		$result = $this->db->sql_query($sql);
		$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
		$this->db->sql_freeresult($result);
		
		date_default_timezone_set('UTC');
		$current_time = strtotime('now');
		
		$sql = "SELECT battle_id, battle_name, battle_start FROM abc_battles 
				WHERE battle_start > $current_time AND campaign_id = $campaign_id
				ORDER BY battle_start ASC";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		if(!$rowset)
		{
			$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= $battle_select;
			$abc_content .= "<h2>$signup_title</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_BATTLEDAY_SIGNUP_EXPL']."</p>";
			$abc_content .= "<p>".$this->user->lang['ABC_BATTLEDAY_SIGNUP_NONE']."</p>";
			$abc_content .= "</fieldset>";
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		/*Get battle_id*/
		if($battle_id == -1)
		{		
			$battle_id = $rowset[0]['battle_id'];
		}
		
		/*battle selector*/
		$battle_select = "<select name=\"battle_choice\" id=\"battle_choice\">";
		foreach($rowset as $row)
		{
			$the_battle_id = $row['battle_id'];
			$the_battle_name = $row['battle_name'];
			$the_battle_start = $row['battle_start'];
			$the_battle_date = date('Y-m-d', $the_battle_start);
			$selected = '';
			if($the_battle_id == $battle_id)
			{
				$selected = "selected=\"selected\"";
			}
			$battle_select .= "<option value=\"$the_battle_id\" $selected>$the_battle_date $the_battle_name</option>";
		}
		$battle_select .= "</select>";
		$battle_select .= " <input type=\"submit\" name=\"select_battle_signup\" id=\"select_battle_signup\" value=\"".$this->user->lang['ABC_BATTLEDAY_SELECT']."\" class=\"button1\"/><br>";
		
		/*Get battleday info*/
		$sql = "SELECT battle_name, battle_start, battle_length FROM abc_battles WHERE battle_id = $battle_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult($result);
		
		$battle_name = $row['battle_name'];
		$battle_date = date('Y-m-d', $row['battle_start']);
		$battle_start = $this->utc_to_sbt($row['battle_start']);
		$battle_length = $row['battle_length'];
		
		/*Correct for SBT < -11*/
		if($sbt < -11)
		{
			$sbt += 24;
			$Ym = substr($the_battle_date, 0, 8);
			$d = substr($the_battle_date, 8, 2);
			$d = (int)$d;
			$d--;
			if($d < 10)
			{
				$the_battle_date = $Ym.'0'.$d;
			}
			else
			{
				$the_battle_date = $Ym.$d;
			}
		}
		
		$user_id = $this->user->data['user_id'];
		/*Get army_id*/
		$sql = "SELECT army_id FROM abc_users WHERE campaign_id = $campaign_id AND user_id = $user_id";
		$result = $this->db->sql_query($sql);
		$army_id = $this->db->sql_fetchfield('army_id');
		$this->db->sql_freeresult($result);
		
		/*Get signups*/
		$sql = "SELECT au.user_bf3_name, au.user_id, absu.sign_up_hours FROM abc_battle_sign_ups as absu
				JOIN abc_users AS au ON absu.user_id = au.user_id
				WHERE absu.battle_id = $battle_id AND au.campaign_id = $campaign_id AND au.army_id = $army_id
				ORDER BY absu.sign_up_time_stamp ASC";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		$signup_title = $this->user->lang['ABC_BATTLEDAY_SIGNUP']."$battle_name";
		
		/*Hidden element. Used to get battle_id*/
		$signup_list = "<input hidden type=\"radio\" name=\"signup_radio\" value=\"".$battle_id."\" checked>";
		/*Display signups*/
		$signup_list .= "<div class=\"abc_signup\">";
		$signup_list .= "<div class=\"abc_signup_name\">$battle_date</div>";
		$signup_list .= "<div>";
		for($i=0; $i<$battle_length; $i++)
		{
			$time = $battle_start + $i;
			$signup_list .= "<div class=\"abc_signup_clear\">SBT ";
			if($time >= 0)
			{
				$signup_list .= "+";
			}
			$signup_list .= "$time</div>";
		}
		$signup_list .= "</div>";
		
		$new_row = true;
		if($rowset)
		{
			foreach($rowset as $row)
			{
				$username = $row['user_bf3_name'];
				$this_user_id = $row['user_id'];
				$sign_up_hours = $row['sign_up_hours'];
				$sign_up_string = decbin($sign_up_hours);
				
				/*decbin clips leading zeros, so add them back*/
				while(strlen($sign_up_string) < $battle_length)
				{
					$sign_up_string = '0'.$sign_up_string;
				}
				$end = $battle_length-1;
				
				$is_user = $user_id == $this_user_id;
				if($is_user)
				{
					$new_row = false;
				}
				
				$signup_list .= "<div class=\"abc_signup_name\">$username</div>";
				$signup_list .= "<div>";
				for($i=0; $i<$battle_length; $i++)
				{
					if($sign_up_string[$end-$i] == '1')
					{
						$signup_list .= "<div class=\"abc_signup_green\">";
						if($is_user)
						{
							$signup_list .= "<input type=\"checkbox\" id=\"sbt_$i\" name=\"sbt_$i\" checked>";
						}
					}
					else
					{
						$signup_list .= "<div class=\"abc_signup_red\">";
						if($is_user)
						{
							$signup_list .= "<input type=\"checkbox\" id=\"sbt_$i\" name=\"sbt_$i\">";
						}
					}
					$signup_list .= "</div>";
				}
				$signup_list .= "</div>";
			}
		}
		if($new_row)
		{
			$username = $this->user->data['username'];
			$signup_list .= "<div class=\"abc_signup_name\">$username</div>";
			$signup_list .= "<div>";
			for($i=0; $i<$battle_length; $i++)
			{
				$signup_list .= "<div class=\"abc_signup_clear\">";
				$signup_list .= "<input type=\"checkbox\" id=\"sbt_$i\" name=\"sbt_$i\">";
				$signup_list .= "</div>";
			}
			$signup_list .= "</div>";
		}
		$signup_list .= "</div>";
		
		$signup_list .= "<br>";
		$signup_list .= "<input type=\"submit\" name=\"sign_up\" id=\"sign_up\" value=\"".$this->user->lang['ABC_BATTLEDAY_SIGN']."\" class=\"button1\"/>";
		
		$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= $battle_select;
		$abc_content .= "<h2>$signup_title</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_BATTLEDAY_SIGNUP_EXPL']."</p>";
		$abc_content .= $signup_list;
		$abc_content .= "</fieldset>";
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	
	public function select_battleday_signup()
	{
		$battle_id = $this->request->variable('battle_choice', '');
		$this->battleday_signup((int)$battle_id);
		return;
	}
	
	public function signup_to_battleday()
	{
		$battle_id = $this->request->variable('signup_radio', '');
		/*Get battle info*/
		$sql = "SELECT battle_length FROM abc_battles WHERE battle_id = $battle_id";
		$result = $this->db->sql_query($sql);
		$battle_length = $this->db->sql_fetchfield('battle_length');
		$this->db->sql_freeresult($result);
		
		$sign_up_hours = 0;
		for($i=0; $i<$battle_length; $i++)
		{
			if($this->request->variable('sbt_'.$i, false))
			{
				$sign_up_hours += pow(2, $i);
			}
		}
		
		$sql = "SELECT MAX(sign_up_id) FROM abc_battle_sign_ups";
		$result = $this->db->sql_query($sql);
		$sign_up_id = $this->db->sql_fetchfield('MAX(sign_up_id)');
		$this->db->sql_freeresult($result);
		$sign_up_id++;
		
		$user_id = $this->user->data['user_id'];
		$sign_up_time_stamp = strtotime('now');
		
		/*If user already signed up, update signup*/
		$sql = "SELECT * FROM abc_battle_sign_ups WHERE user_id = $user_id AND battle_id = $battle_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult($result);
		if($row)
		{
			$sql = "UPDATE abc_battle_sign_ups SET sign_up_hours = $sign_up_hours WHERE user_id = $user_id AND battle_id = $battle_id";
			$result = $this->db->sql_query($sql);
			$this->db->sql_freeresult($result);
		}
		else
		{
			$sql = "INSERT INTO abc_battle_sign_ups VALUES ($sign_up_id, $battle_id, $user_id, $sign_up_hours, $sign_up_time_stamp)";
			$result = $this->db->sql_query($sql);
			$this->db->sql_freeresult($result);
		}
		
		$this->battleday_signup((int)$battle_id);
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
	
	public function sbt_to_utc($date, $sbt)
	{
		date_default_timezone_set('UTC');
		/*If Winter time, SBT = 18:00 UCT*/
		/*If DST/Summer time, SBT = 17:00 UCT*/
		$sbt_zero = 18;
		$battle = strtotime($date);
		
		$month = substr($date, 5, 2);
		$month = (int)$month;
		if($month <= 6)
		{
			/*Follow EU DST in spring*/
			date_default_timezone_set('Europe/London');
			$DST = date('I', $battle);
			if($DST)
			{
				$sbt_zero = 17;
			}
		}
		else
		{
			/*Follow US DST in autumn*/
			date_default_timezone_set('America/New_York');
			$DST = date('I', $battle);
			if($DST)
			{
				$sbt_zero = 17;
			}
		}
		
		date_default_timezone_set('UTC');
		
		$hour = $sbt_zero + $sbt;
		
		$zro = '';
		if($hour < 10)
		{
			$zro = '0';
		}
		elseif($hour > 24)
		{
			$hour -= 24;
			$year = substr($date, 0, 4);
			$day = substr($date, 8, 2);
			$day = (int)$day;
			$day++;
			if($month < 10)
			{
				if($day < 10)
				{
					$date = $year."-0".$month."-0".$day;
				}
				else
				{
					$date = $year."-0".$month."-".$day;
				}
			}
			else
			{
				if($day < 10)
				{
					$date = $year."-".$month."-0".$day;
				}
				else
				{
					$date = $year."-".$month."-".$day;
				}
			}
			
		}
		
		$utc = strtotime($date.' '.$zro.$hour.':00:00');
		
		return $utc;
	}
	
	public function get_battle_db_row()
	{
		$current_time = strtotime("now");
		/*Get battles*/
		$sql = "SELECT battle_id FROM abc_battles 
				WHERE battle_start > $current_time AND campaign_id = (SELECT MAX(campaign_id) FROM abc_battles)
				ORDER BY battle_start DESC";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(!$rowset)
		{
			return false;
		}
		
		$row_idx = -1;
		$to_edit = $this->request->variable('battle_radio', '');
		for($i=0; $i<count($rowset); $i++)
		{
			if($to_edit == $rowset[$i]['battle_id'])
			{
				return $rowset[$i];
			}
		}
		
		return false;
	}
}