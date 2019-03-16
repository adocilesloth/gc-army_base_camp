<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;
class abc_start
{
	/* @var \phpbb\config\config */
	protected $config;
	
	/* @var \phpbb\template\template */
	protected $template;
	
	/** @var request_interface */
	protected $request;
	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/** @var string */
	protected $root_path;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\request\request $request,
		\phpbb\db\driver\driver_interface $db,
		$root_path)
	{
		$this->config = $config;
		$this->template = $template;
		$this->request = $request;
		$this->db = $db;
		$this->root_path = $root_path;
	}

	/*Start Page*/
	public function start_page()
	{
		$campaign_name = sql_abc_unclean($this->config['campaign_name']);
		$campaign_divisions = sql_abc_unclean($this->config['campaign_divisions']);
		$army1_name = sql_abc_unclean($this->config['army1_name']);
		$army1_colour = sql_abc_unclean($this->config['army1_colour']);
		$army1_general = sql_abc_unclean($this->config['army1_general']);
		$army1_password = sql_abc_unclean($this->config['army1_password']);
		$armyb_name = sql_abc_unclean($this->config['armyb_name']);
		$armyb_colour = sql_abc_unclean($this->config['armyb_colour']);
		$armyb_general = sql_abc_unclean($this->config['armyb_general']);
		$armyb_password = sql_abc_unclean($this->config['armyb_password']);
		$ta_name = sql_abc_unclean($this->config['ta_name']);
		$ta_colour = sql_abc_unclean($this->config['ta_colour']);
		$ta_general = sql_abc_unclean($this->config['ta_general']);
		$ta_password = sql_abc_unclean($this->config['ta_password']);
	
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
		
		return;
	}
	
	/*Start Campaign*/
	public function start_campaign()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
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
			return false;
		}
		else
		{
			$army1_general_name = sql_abc_clean($this->request->variable('army1_general', '', true));
			$armyb_general_name = sql_abc_clean($this->request->variable('armyb_general', '', true));
			$ta_general_name = sql_abc_clean($this->request->variable('ta_general', '', true));
			
			$sql = "SELECT user_id FROM ". USERS_TABLE ." WHERE username = '$army1_general_name' OR username = '$armyb_general_name' OR username = '$ta_general_name'";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			/*Reject nonexistant generals*/
			if(count($rowset) != 3)
			{
				$this->template->assign_var('ACP_FAILED', true);
				$this->template->assign_var('ACP_SUCCESS', false);
				return false;
			}
			/*Setup Campaign*/
			else
			{				
				$user_id_1 = $rowset[0]['user_id'];
				$user_id_b = $rowset[1]['user_id'];
				$user_id_ta = $rowset[2]['user_id'];
				
				/*Campaign Settings*/
				$this->config->set('campaign_state', '1');
				$this->config->set('campaign_name', sql_abc_clean($this->request->variable('campaign_name', '', true)));
				$this->config->set('campaign_divisions', sql_abc_clean($this->request->variable('campaign_divisions', 'Infantry,Armour,Air', true)));
				/*Army 1 Settings*/
				$this->config->set('army1_name', sql_abc_clean($this->request->variable('army1_name', '', true)));
				$this->config->set('army1_colour', sql_abc_clean($this->request->variable('army1_colour', '084CA1')));
				$this->config->set('army1_general', sql_abc_clean($this->request->variable('army1_general', '', true)));
				$this->config->set('army1_password', sql_abc_clean($this->request->variable('army1_password', '', true)));
				/*Army B Settings*/
				$this->config->set('armyb_name', sql_abc_clean($this->request->variable('armyb_name', '', true)));
				$this->config->set('armyb_colour', sql_abc_clean($this->request->variable('armyb_colour', 'ED1C24')));
				$this->config->set('armyb_general', sql_abc_clean($this->request->variable('armyb_general', '', true)));
				$this->config->set('armyb_password', sql_abc_clean($this->request->variable('armyb_password', '', true)));
				/*TA Settings*/
				$this->config->set('ta_name', sql_abc_clean($this->request->variable('ta_name', 'Tournament Administrators', true)));
				$this->config->set('ta_colour', sql_abc_clean($this->request->variable('ta_colour', '0099FF')));
				$this->config->set('ta_general', sql_abc_clean($this->request->variable('ta_general', '', true)));
				$this->config->set('ta_password', sql_abc_clean($this->request->variable('ta_password', '', true)));
				
				$this->template->assign_var('ACP_START', false);
				
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
				
				/*Update abc_campaigns table*/
				/*This uses the old ABC database, some columns are nolonger used*/
				$sql = "SELECT MAX(campaign_id) from abc_campaigns";
				$result = $this->db->sql_query($sql);
				$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
				$this->db->sql_freeresult($result);
				
				$campaign_id++;
				$campaign_name = $this->config['campaign_name'];
				$campaign_draft_date = 0;
				$campaign_time_stamp = strtotime("now");
				$sql = "INSERT INTO abc_campaigns VALUES ($campaign_id, 7, '$campaign_name', 0, 0, $campaign_draft_date, 0, 0, 0, $campaign_time_stamp)";
				$result = $this->db->sql_query($sql);
				$this->db->sql_freeresult($result);
				
				/*Update abc_armies and abc_users tables*/
				/*This uses the old ABC database, some columns are nolonger used*/
				$sql = "SELECT MAX(army_id) from abc_armies";
				$result = $this->db->sql_query($sql);
				$army_id = $this->db->sql_fetchfield('MAX(army_id)');
				$this->db->sql_freeresult($result);
				
				$armies = array('army1_', 'armyb_', 'ta_');
				foreach($armies as $army)
				{
					$army_id++;
					$army_name = $this->config[$army.'name'];
					$army_general_name = $this->config[$army.'general'];
					$sql = "SELECT user_id FROM ".USERS_TABLE." WHERE username = '$army_general_name'";
					$result = $this->db->sql_query($sql);
					$army_general = $this->db->sql_fetchfield('user_id');
					$this->db->sql_freeresult($result);
					$army_colour = $this->config[$army.'colour'];
					$army_tag = 'NA';
					$army_join_pw = $this->config[$army.'password'];
					$army_ts_pw = 'gcfun';
					$army_is_neutral = 0;
					if($army == 'ta_')
					{
						$army_is_neutral = 1;
					}
					
					$sql = "INSERT INTO abc_armies VALUES ($army_id, $campaign_id, '$army_name', $army_general, '$army_colour', 
							'NANA', '$army_tag', '$army_join_pw', '$army_ts_pw', $army_is_neutral, 0, 0, 0, $campaign_time_stamp)";
					$result = $this->db->sql_query($sql);
					$this->db->sql_freeresult($result);
					
					$abc_user_id = 0;
					$other_nonsense = "0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0";
					$sql = "INSERT INTO abc_users VALUES ($abc_user_id, $army_general, $campaign_id, $army_id, 0, 0, 'img', 0, '$army_general_name', '', '', '', '', $campaign_time_stamp, '', $other_nonsense)";
					$result = $this->db->sql_query($sql);
					$this->db->sql_freeresult($result);
				}
				
				$this->template->assign_var('ACP_FAILED', false);
				$this->template->assign_var('ACP_SUCCESS', true);
			}
		}
		return true;
	}
}