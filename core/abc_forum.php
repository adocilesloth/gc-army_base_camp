<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_forum
{
	/* @var \phpbb\config\config */
	protected $config;
	
	/* @var \phpbb\template\template */
	protected $template;
	
	/** @var request_interface */
	protected $request;
	
	/* @var \phpbb\user */
	protected $user;
	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	
	/** @var \phpbb\auth\auth */
	protected $auth;
	
	/** @var string */
	protected $root_path;
	
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\template\template $template,
		\phpbb\request\request $request,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\auth\auth $auth,
		$root_path)
	{
		$this->config = $config;
		$this->template = $template;
		$this->request = $request;
		$this->user = $user;
		$this->db = $db;
		$this->auth = $auth;
		$this->root_path = $root_path;
	}
	
	public function start_forum()
	{
		/*Default groups*/
		/*These group_ids are not as expected, so pull them from the database*/
		$sql = "SELECT group_id, group_name FROM ".GROUPS_TABLE." WHERE
				group_name = 'REGISTERED' OR group_name = 'REGISTERED_COPPA' OR group_name = 'ADMINISTRATORS'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(count($rowset) != 3)
		{
			$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_START_SUCCESS']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_FORUM_FAILED'].$this->user->lang['ABC_FORUM_ERR_GRP']."</p>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return false;
		}
		$registered_id = $reg_coppa_id = $admin_id = -1;
		for($i=0; $i<count($rowset); $i++)
		{
			if($rowset[$i]['group_name'] == "REGISTERED")
			{
				$registered_id = $rowset[$i]['group_id'];
			}
			elseif($rowset[$i]['group_name'] == "REGISTERED_COPPA")
			{
				$reg_coppa_id = $rowset[$i]['group_id'];
			}
			elseif($rowset[$i]['group_name'] == "ADMINISTRATORS")
			{
				$admin_id = $rowset[$i]['group_id'];
			}
		}
		if($registered_id < 1 || $reg_coppa_id < 1 || $admin_id < 1)
		{
			$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_START_SUCCESS']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_FORUM_FAILED'].$this->user->lang['ABC_FORUM_ERR_GID']."</p>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return false;
		}
		
		/*Army Groups*/
		$army1 = $this->config['army1_name'];
		$armyb = $this->config['armyb_name'];
		$ta = $this->config['ta_name'];
		$sql = "SELECT group_id, group_name FROM ".GROUPS_TABLE." WHERE group_name = '".$army1." HC' OR group_name = '".$armyb." HC' OR group_name = '".$ta."'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(count($rowset) != 3)
		{
			$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_START_SUCCESS']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_FORUM_FAILED'].$this->user->lang['ABC_FORUM_ERR_GRP']."</p>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return false;
		}
		
		$army1_id = $armyb_id = $ta_id = -1;
		for($i=0; $i<count($rowset); $i++)
		{
			if($rowset[$i]['group_name'] == $army1." HC")
			{
				$army1_id = $rowset[$i]['group_id'];
			}
			elseif($rowset[$i]['group_name'] == $armyb." HC")
			{
				$armyb_id = $rowset[$i]['group_id'];
			}
			elseif($rowset[$i]['group_name'] == $ta)
			{
				$ta_id = $rowset[$i]['group_id'];
			}
		}
		if($army1_id < 1 || $armyb_id < 1 || $ta_id < 1)
		{
			$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_START_SUCCESS']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_FORUM_FAILED'].$this->user->lang['ABC_FORUM_ERR_GID']."</p>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return false;
		}
		
		/*Permissions*/
		$permissions = array(
			//Registered
			array('group_id' => $registered_id, 'auth_option_id' => 0, 'auth_role_id' => 17, 'auth_setting' => 0 ), //READ ONLY
			//Registered_coppa
			array('group_id' => $reg_coppa_id, 'auth_option_id' => 0, 'auth_role_id' => 17, 'auth_setting' => 0 ), //READ ONLY
			//Admin
			array('group_id' => $admin_id, 'auth_option_id' => 0, 'auth_role_id' => 14, 'auth_setting' => 0 ), //FULL
			array('group_id' => $admin_id, 'auth_option_id' => 0, 'auth_role_id' => 10, 'auth_setting' => 0 ), //MOD_FULL
			//TAs are admins
			array('group_id' => $ta_id, 'auth_option_id' => 0, 'auth_role_id' => 14, 'auth_setting' => 0 ), //FULL
			array('group_id' => $ta_id, 'auth_option_id' => 0, 'auth_role_id' => 10, 'auth_setting' => 0 ) //MOD_FULL
		);
			
		/*Category*/
		$options = array('name' => $this->config['campaign_name'], 'parent_id' => 0, 'forum_type' => 0);
		$cat_id = $this->create_forum($options, $forum_data = false, $permissions = $permissions);
		
		/*Campaign Rules, Results and Information*/
		$options = array('name' => $this->user->lang['ABC_FORUM_CRRI'], 'parent_id' => $cat_id, 'forum_type' => 1);
		$crri_id = $this->create_forum($options, $forum_data = false, $permissions = $permissions);
		
		/*TA*/
		$permissions = array(
			//Admin
			array('group_id' => $admin_id, 'auth_option_id' => 0, 'auth_role_id' => 14, 'auth_setting' => 0 ), //FULL
			array('group_id' => $admin_id, 'auth_option_id' => 0, 'auth_role_id' => 10, 'auth_setting' => 0 ), //MOD_FULL
			//TAs are admins
			array('group_id' => $ta_id, 'auth_option_id' => 0, 'auth_role_id' => 14, 'auth_setting' => 0 ), //FULL
			array('group_id' => $ta_id, 'auth_option_id' => 0, 'auth_role_id' => 10, 'auth_setting' => 0 ) //MOD_FULL
		);
		$options = array('name' => $ta, 'parent_id' => $cat_id, 'forum_type' => 1);
		$ta_f_id = $this->create_forum($options, $forum_data = false, $permissions = $permissions);
		
		/*TA + HC*/
		$permissions = array(
			//Admin
			array('group_id' => $admin_id, 'auth_option_id' => 0, 'auth_role_id' => 14, 'auth_setting' => 0 ), //FULL
			array('group_id' => $admin_id, 'auth_option_id' => 0, 'auth_role_id' => 10, 'auth_setting' => 0 ), //MOD_FULL
			//TAs are admins
			array('group_id' => $ta_id, 'auth_option_id' => 0, 'auth_role_id' => 14, 'auth_setting' => 0 ), //FULL
			array('group_id' => $ta_id, 'auth_option_id' => 0, 'auth_role_id' => 10, 'auth_setting' => 0 ), //MOD_FULL
			//HCs
			array('group_id' => $army1_id, 'auth_option_id' => 0, 'auth_role_id' => 15, 'auth_setting' => 0 ), //Army1 HC STANDARD
			array('group_id' => $armyb_id, 'auth_option_id' => 0, 'auth_role_id' => 15, 'auth_setting' => 0 )  //Armyb HC STANDARD
		);
		$options = array('name' => $ta.' + HC', 'parent_id' => $cat_id, 'forum_type' => 1);
		$tahc_id = $this->create_forum($options, $forum_data = false, $permissions = $permissions);
		
		/*Move the catagory up to second position*/
		$sql = "SELECT * FROM ".FORUMS_TABLE." WHERE forum_type = 0 AND parent_id = 0";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(!$rowset)
		{
			$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_START_SUCCESS']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_FORUM_FAILED'].$this->user->lang['ABC_FORUM_ERR_CAT']."</p>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return false;
		}
		$num_cat = count($rowset);
		
		$sql = "SELECT * FROM ".FORUMS_TABLE." WHERE forum_id = $cat_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult($result);
		
		if(!class_exists('acp_forums'))
		{
			include $this->root_path . 'includes/acp/acp_forums.php';
		}
		if(!function_exists('validate_range'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_forum_inc.php';
		}
		$forums_admin = new \acp_forums();
		
		$moved = $forums_admin->move_forum_by($row, 'move_up', $num_cat-2);
		if(!$moved)
		{
			$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_START_SUCCESS']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_FORUM_FAILED'].$this->user->lang['ABC_FORUM_ERR_MOV']."</p>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return false;
		}
		
		$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= "<h2>".$this->user->lang['ABC_START_SUCCESS']."</h2>";
		$abc_content .= "</fieldset>";
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return true;
	}
	
	public function finish_forum()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		/*Check inputs*/
		$campaign_archive = sql_abc_clean($this->request->variable('campaign_archive', '', true));
		$campaign_hidden_archive = sql_abc_clean($this->request->variable('campaign_hidden_archive', '', true));
		$archivist_string = $this->request->variable('archivist', '', true); /*This input includes a slash, so don't clean*/
		if($campaign_archive == '' or $campaign_hidden_archive == '')
		{
			$abc_content = "<h2>".$this->user->lang['ABC_FINISH']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_FINISH_FAILED']."</p>";
			$abc_content .= "<p>".$this->user->lang['ABC_FINISH_ERR_ARCH']."</p>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return false;
		}
		/*Check archives exist*/
		$sql = "SELECT forum_id FROM ".FORUMS_TABLE." WHERE forum_name = '$campaign_archive' OR forum_name = '$campaign_hidden_archive'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(count($rowset) != 2)
		{
			$abc_content = "<h2>".$this->user->lang['ABC_FINISH']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_FINISH_FAILED']."</p>";
			$abc_content .= "<p>".$sql."</p>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return false;
		}
		
		$campaign_name =  $this->config['campaign_name'];
		/*Get catagory id*/
		$sql = "SELECT forum_id FROM ".FORUMS_TABLE." WHERE forum_name = '$campaign_name'";
		$result = $this->db->sql_query($sql);
		$camp_id = $this->db->sql_fetchfield('forum_id');
		$this->db->sql_freeresult($result);
		if(!$camp_id)
		{
			return true;
		}
		
		/*Move category to the bottom*/
		$sql = "SELECT * FROM ".FORUMS_TABLE." WHERE forum_type = 0 AND parent_id = 0";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(!$rowset)
		{
			return true;
		}
		$num_cat = count($rowset);
		
		$sql = "SELECT * FROM ".FORUMS_TABLE." WHERE forum_id = $camp_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult($result);
		
		if(!class_exists('acp_forums'))
		{
			include $this->root_path . 'includes/acp/acp_forums.php';
		}
		$forums_admin = new \acp_forums();
		
		$moved = $forums_admin->move_forum_by($row, 'move_down', $num_cat-2);
		if(!$moved)
		{
			return true;
		}
		
		/*Get info for archiving forums*/
		$ta_name = $this->config['ta_name'];
		$tahc_name = $ta_name.' + HC';
		
		$sql = "SELECT * FROM ".FORUMS_TABLE." WHERE forum_name = '$campaign_hidden_archive'
				OR forum_name = '$ta_name' OR forum_name = '$tahc_name'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(count($rowset) != 3)
		{
			return true;
		}
		
		/*forum_id for the archive forums*/
		$hidden_id = 0;
		/*data for forums*/
		$hidden_data = $ta_data= $tahc_data = [];
		for($i=0; $i<count($rowset); $i++)
		{
			if($rowset[$i]['forum_name'] == $campaign_hidden_archive)
			{
				$hidden_id = $rowset[$i]['forum_id'];
				$hidden_data = $rowset[$i];
			}
			elseif($rowset[$i]['forum_name'] == $ta_name)
			{
				$ta_data = $rowset[$i];
			}
			elseif($rowset[$i]['forum_name'] == $tahc_name)
			{
				$tahc_data = $rowset[$i];
			}
		}
		if($hidden_id < 1 || count($ta_data) == 0 || count($tahc_data) == 0)
		{
			return true;
		}
		
		$old_left_id = $ta_data['left_id'];
		$new_left_id = $hidden_data['right_id'];
		
		/*make a hole for the hidden forums*/
		$sql = "UPDATE ".FORUMS_TABLE." SET left_id = left_id + 4 WHERE left_id > $new_left_id AND left_id < $old_left_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		$sql = "UPDATE ".FORUMS_TABLE." SET right_id = right_id + 4 WHERE right_id >= $new_left_id AND right_id < $old_left_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Update TA and TA+HC forum positions*/
		$sql = "UPDATE ".FORUMS_TABLE." SET forum_name = '".$campaign_name.": ".$ta_name."', left_id = $new_left_id, right_id = $new_left_id + 1, parent_id = $hidden_id, forum_parents = '' ".
				"WHERE forum_id = ".$ta_data['forum_id'];
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		$sql = "UPDATE ".FORUMS_TABLE." SET forum_name = '".$campaign_name.": ".$tahc_name."', left_id = $new_left_id + 2, right_id = $new_left_id + 3, parent_id = $hidden_id, forum_parents = '' ".
				"WHERE forum_id = ".$tahc_data['forum_id'];
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Get info for moving category*/
		$sql = "SELECT * FROM ".FORUMS_TABLE." WHERE forum_name = '$campaign_archive' OR forum_name = '$campaign_name'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(count($rowset) != 2)
		{
			return true;
		}
		
		/*forum_id for the archive forums*/
		$archive_id = 0;
		/*data for forums*/
		$archive_data = $campaign_data = [];
		for($i=0; $i<count($rowset); $i++)
		{
			if($rowset[$i]['forum_name'] == $campaign_archive)
			{
				$archive_id = $rowset[$i]['forum_id'];
				$archive_data = $rowset[$i];
			}
			elseif($rowset[$i]['forum_name'] == $campaign_name)
			{
				$campaign_data = $rowset[$i];
			}
		}
		if($archive_id < 1 || count($campaign_data) == 0)
		{
			return true;
		}
		
		/*Move category into archive*/
		$old_left_id = $campaign_data['left_id'];
		$delta = $old_left_id - $archive_data['right_id'];
		$sql = "UPDATE ".FORUMS_TABLE." SET left_id = left_id - $delta, right_id = right_id - $delta WHERE right_id > $old_left_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		/*Set category parent and name*/
		$camp_id = $campaign_data['forum_id'];
		$new_name = $campaign_name." (Archive)";
		$sql = "UPDATE ".FORUMS_TABLE." SET parent_id = $archive_id, forum_parents = '', forum_name = '$new_name' WHERE forum_id = $camp_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Make archive longer*/
		$delta = ($campaign_data['right_id'] - $campaign_data['left_id']) + 1;
		$sql = "UPDATE ".FORUMS_TABLE." SET right_id = right_id + $delta WHERE forum_id = $archive_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		
		/**PERMISSIONS**/
		/*Hidden Archive*/
		/*Want ABC admins but not TA group*/
		$abc_admins_string = $this->config['start_perm_groups'];
		$abc_admins = explode(",", $abc_admins_string);
		$access = [];
		foreach($abc_admins as $admins)
		{
			if($admins != $ta_name && $admins != '')
			{
				$access[] = $admins;
			}
		}
		if(count($access) > 0)
		{
			$sql = "SELECT group_id FROM ".GROUPS_TABLE." WHERE";
			foreach($access as $acc)
			{
				$sql .= " group_name = '$acc' OR";
			}
			$sql = substr($sql, 0, strlen($sql)-3);
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			if(count($rowset) != count($access))
			{
				return true;
			}
		}
		
		/*Setup Hidden Archive permissions*/
		$permissions = [];
		for($i=0; $i<count($rowset); $i++)
		{
			$permissions[] = Array('group_id' => $rowset[$i]['group_id'],
				'forum_id' => $ta_data['forum_id'],
				'auth_option_id' => 0,
				'auth_role_id' => 17,  //READ ONLY
				'auth_setting' => 0 );
			$permissions[] = Array('group_id' => $rowset[$i]['group_id'],
				'forum_id' => $tahc_data['forum_id'],
				'auth_option_id' => 0,
				'auth_role_id' => 17,  //READ ONLY
				'auth_setting' => 0 );
		}
		
		/*Archives*/
		/*Want all the forum_id. Start at category and work down*/
		$forum_ids = [];
		$temp_ids = Array($camp_id,);
		while(true)
		{
			$sql = "SELECT forum_id FROM ".FORUMS_TABLE." WHERE";
			foreach($temp_ids as $tid)
			{
				$sql .= " parent_id = $tid OR";
			}
			$sql = substr($sql, 0, strlen($sql)-3);
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			$forum_ids = array_merge($forum_ids, $temp_ids);
			if(count($rowset) == 0)
			{
				break;
			}
			
			$temp_ids = [];
			for($i=0; $i<count($rowset); $i++)
			{
				$temp_ids[] = $rowset[$i]['forum_id'];
			}
		}
		/*Get archivist groups*/
		$archivist_string = sql_abc_clean($this->request->variable('archivist', '', true));
		if($archivist_string != '')
		{
			$archivist = explode(",", $archivist_string);
			$access = array_merge($access, $archivist);
		}
		if($access[sizeof($access)-1] == '')
		{
			array_pop($access);
		}
		
		$sql = "SELECT group_id FROM ".GROUPS_TABLE." WHERE";
		foreach($access as $acc)
		{
			$sql .= " group_name = '$acc' OR";
		}
		$sql = substr($sql, 0, strlen($sql)-3);
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(count($rowset) != count($access))
		{
			return true;
		}
		
		/*Setup Archive permissions*/
		for($i=0; $i<count($rowset); $i++)
		{
			foreach($forum_ids as $fid)
			{
				$permissions[] = Array('group_id' => $rowset[$i]['group_id'],
					'forum_id' => $fid,
					'auth_option_id' => 0,
					'auth_role_id' => 17,  //READ ONLY
					'auth_setting' => 0 );
			}
		}
		
		/*Remove current permissions*/
		$this->auth->acl_clear_prefetch();
		
		$sql = "DELETE FROM ".ACL_GROUPS_TABLE." WHERE";
		foreach($forum_ids as $fid)
		{
			$sql .= " forum_id = $fid OR";
		}
		$sql = substr($sql, 0, strlen($sql)-3);
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);

		/*Insert new permissions*/
		if(!$this->db->sql_multi_insert(ACL_GROUPS_TABLE, $permissions))
		{
			//$this->template->assign_var('APC_FINISH_UNABLE', $this->user->lang['ABC_FORUM_ERR_ARK'].$this->user->lang['ABC_FORUM_ERR_AKP'].
			//													$this->user->lang['ABC_FORUM_ERR_PER']);
		}
		
		return true;
	}
	
	public function find_forums()
	{
		
		$abc_content = "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= "<h2>".$this->user->lang['ABC_FORUM']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_FORUM_EXPLAIN']."</p>";
					
		$abc_content .= "<dl><dt><label for=\"forum_name\">".$this->user->lang['ABC_FORUM_NAME']."</label><br>";
		$abc_content .= "<span>".$this->user->lang['ABC_FORUM_NAME_EXPL']."</span></dt>";
		$abc_content .= "<dd><input type=\"text\" class=\"inputbox\" name=\"forum_name\" value=\"\" /></dd>";
		$abc_content .= "</dl><dl>";
		$abc_content .= "<dt><label for=\"forum_officer\">".$this->user->lang['ABC_FORUM_OFFICER']."</label></dt>";
		$abc_content .= "<dd><input type=\"radio\" class=\"radio\" name=\"forum_officer\" value=\"1\" /> ".$this->user->lang['YES']." &nbsp;";
		$abc_content .= "<input type=\"radio\" class=\"radio\" name=\"forum_officer\" value=\"0\" checked=\"checked\" /> ".$this->user->lang['NO']."</dd></dl>";
					
		/*Get army*/
		$army = '';
		if($this->user->data['username'] == $this->config['army1_general'])
		{
			$army = $this->config['army1_name'];
		}
		elseif($this->user->data['username'] == $this->config['armyb_general'])
		{
			$army = $this->config['armyb_name'];
		}
		
		/*Get army HC group_id*/
		$sql = "SELECT group_id FROM ".GROUPS_TABLE." WHERE group_name = '$army HC'";
		$result = $this->db->sql_query($sql);
		$group_id = $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);
		if(!$group_id)
		{
			$abc_content .= "group_id";
			$abc_content .= "</fieldset>";
			$abc_content .= "<fieldset class=\"submit-buttons\">";
			$abc_content .= "<input type=\"submit\" name=\"create_forum\" id=\"create_forum\" value=\"".$this->user->lang['ABC_FORUM_CREATE']."\" class=\"button1\"/>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		/*Get forum_id where HC is FULL*/
		$sql = "SELECT forum_id FROM ".ACL_GROUPS_TABLE." WHERE group_id = $group_id AND auth_role_id = 14";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(!$rowset)
		{
			$abc_content .= "";
			$abc_content .= "</fieldset>";
			$abc_content .= "<fieldset class=\"submit-buttons\">";
			$abc_content .= "<input type=\"submit\" name=\"create_forum\" id=\"create_forum\" value=\"".$this->user->lang['ABC_FORUM_CREATE']."\" class=\"button1\"/>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		/*Get forum_name where HC is FULL*/
		$sql = "SELECT forum_name FROM ".FORUMS_TABLE." WHERE";
		for($i=0; $i<count($rowset); $i++)
		{
			$fid = $rowset[$i]['forum_id'];
			$sql .= " forum_id = $fid OR";
		}
		$sql = substr($sql, 0, strlen($sql)-3);
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		if(!$rowset)
		{
			$abc_content .= "";
			$abc_content .= "</fieldset>";
			$abc_content .= "<fieldset class=\"submit-buttons\">";
			$abc_content .= "<input type=\"submit\" name=\"create_forum\" id=\"create_forum\" value=\"".$this->user->lang['ABC_FORUM_CREATE']."\" class=\"button1\"/>";
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		/*Setup if forum is subforum*/
		$html_string = "<br><dl>
					<dt><label for=\"subforum\">".$this->user->lang['ABC_FORUM_SUBFORUM']."</label></dt>
					<dd><input type=\"radio\" class=\"radio\" name=\"subforum\" value=\"1\" /> ".$this->user->lang['YES']." &nbsp;
						<input type=\"radio\" class=\"radio\" name=\"subforum\" value=\"0\" checked=\"checked\" /> ".$this->user->lang['NO']."</dd>
				</dl>";
		/*Put forum_name into selectable thingy*/
		$html_string .= "<dl>
					<dt><label for=\"forum_parent\">".$this->user->lang['ABC_FORUM_PARENT']."</label><br>
					<span>".$this->user->lang['ABC_FORUM_PARENT_EXPL']."</span></dt>
					<dd><select name=\"forum_parent\" id=\"forum_parent\">";
		$html_string .= "<option value=\"none\" selected=\"selected\"> </option>";
		for($i=0; $i<count($rowset); $i++)
		{
			$html_string .= "<option value=\"";
			$html_string .= $rowset[$i]['forum_name'];
			$html_string .= "\">";
			$html_string .= $rowset[$i]['forum_name'];
			$html_string .= "</option>";
		}
		$html_string .= "</select></dd></dl>";
		
		$abc_content .= $html_string;
		$abc_content .= "</fieldset>";
		$abc_content .= "<fieldset class=\"submit-buttons\">";
		$abc_content .= "<input type=\"submit\" name=\"create_forum\" id=\"create_forum\" value=\"".$this->user->lang['ABC_FORUM_CREATE']."\" class=\"button1\"/>";
		$abc_content .= "</fieldset>";
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		
		return;
	}
	
	public function add_forum()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		$forum_name = sql_abc_clean($this->request->variable('forum_name', '', true));
		if($forum_name == '')
		{
			$this->find_forums();
			return;
		}
		$is_officer = $this->request->variable('forum_officer', false);
		$is_subforum = $this->request->variable('subforum', false);
		$parent = sql_abc_clean($this->request->variable('forum_parent', 'none', false));
		
		/*Get army*/
		$army = '';
		if($this->user->data['username'] == $this->config['army1_general'])
		{
			$army = $this->config['army1_name'];
		}
		elseif($this->user->data['username'] == $this->config['armyb_general'])
		{
			$army = $this->config['armyb_name'];
		}
		
		$parent_id = -1;
		$left_id  = -1;
		/*If not subforum*/
		if(!$is_subforum)
		{
			/*Campaign category is parent*/
			$parent = $this->config['campaign_name'];
		}
		if($parent == 'none')
		{
			$this->find_forums();
			return;
		}
	
		/*Get parent information*/
		$sql = "SELECT forum_id, right_id FROM ".FORUMS_TABLE." WHERE forum_name = '$parent'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult($result);
		if(!$row)
		{
			$this->template->assign_var('ABC_PAGE_CONTENT', $this->user->lang['ABC_FORUM_ERR_PAGE'].
															$this->user->lang['ABC_FORUM_ERR_PARENT'].
															"<br>$sql");
			return;
		}
		$parent_id = $row['forum_id'];
		$left_id = $row['right_id'];
		
		/*Create forum array*/
		$forum_data = array(
			'parent_id'					=> $parent_id,				//parent_id
			'left_id'					=> $left_id,				//left_id
			'right_id'					=> $left_id + 1,			//right_id
			'forum_parents'				=> '',						//forum_parents
			'forum_name'				=> $forum_name,				//forum_name
			'forum_desc'				=> '',						//forum_desc
			'forum_desc_bitfield'		=> '',						//forum_desc_bitfield
			'forum_desc_options'		=> 7,						//forum_desc_options
			'forum_desc_uid'			=> '',						//forum_desc_uid
			'forum_link'				=> '',						//forum_link
			'forum_password'			=> '',						//forum_password
			'forum_style'				=> 0,						//forum_style
			'forum_image'				=> '',						//forum_image
			'forum_rules'				=> '',						//forum_rules
			'forum_rules_link'			=> '',						//forum_rules_link
			'forum_rules_bitfield'		=> '',						//forum_rules_bitfield
			'forum_rules_options'		=> 7,						//forum_rules_options
			'forum_rules_uid'			=> '',						//forum_rules_uid
			'forum_topics_per_page'		=> 0,						//forum_topics_per_page
			'forum_type'				=> 1,						//forum_type
			'forum_status'				=> 0,						//forum_status
			'forum_last_post_id'		=> 0,						//forum_last_post_id
			'forum_last_poster_id'		=> 0,						//forum_last_poster_id
			'forum_last_post_subject'	=> '',						//forum_last_post_subject
			'forum_last_post_time'		=> 0,						//forum_last_post_time
			'forum_last_poster_name'	=> '',						//forum_last_poster_name
			'forum_last_poster_colour'	=> '',						//forum_last_poster_colour
			'forum_flags'				=> 32,						//forum_flags
			'display_on_index'			=> true,					//display_on_index
			'enable_indexing'			=> true,					//enable_indexing
			'enable_icons'				=> false,					//enable_icons
			'enable_prune'				=> false,					//enable_prune
			'prune_next'				=> 0,						//prune_next
			'prune_days'				=> 7,						//prune_days
			'prune_viewed'				=> 7,						//prune_viewed
			'prune_freq'				=> 1,						//prune_freq
			'display_subforum_list'		=> 1,						//display_subforum_list
			'forum_options'				=> 0,						//forum_options
			'enable_shadow_prune'		=> 0,						//enable_shadow_prune
			'prune_shadow_days'			=> 7,						//prune_shadow_days
			'prune_shadow_freq'			=> 1,						//prune_shadow_freq
			'prune_shadow_next'			=> 0,						//prune_shadow_next
		);
		
		/*Get admins*/
		$admins_string = $this->config['start_perm_groups'];
		$admins = explode(",", $admins_string);
		if(end($admins) == '')
		{
			array_pop($admins);
		}
		$admins[] = "$army HC";
		$sql = "SELECT group_id FROM ".GROUPS_TABLE." WHERE";
		foreach($admins as $acc)
		{
			$sql .= " group_name = '$acc' OR";
		}
		$sql = substr($sql, 0, strlen($sql)-3);
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(count($rowset) != count($admins))
		{
			$this->template->assign_var('ABC_PAGE_CONTENT', $this->user->lang['ABC_FORUM_ERR_PAGE'].
															$this->user->lang['ABC_FORUM_ERR_ADMIN'].
															"<br>$sql");
			return;
		}
		
		/*Create permissions array*/
		$permissions = [];
		for($i=0; $i<count($rowset); $i++)
		{
			$permissions[] = Array('group_id' => $rowset[$i]['group_id'],
							'auth_option_id' => 0,
							'auth_role_id' => 14,  //FULL
							'auth_setting' => 0 );
		}
		
		/*Get army members*/
		$readers = Array("$army Officers",);
		if(!$is_officer)
		{
			$readers[] = "$army";
		}
		$sql = "SELECT group_id FROM ".GROUPS_TABLE." WHERE";
		foreach($readers as $acc)
		{
			$sql .= " group_name = '$acc' OR";
		}
		$sql = substr($sql, 0, strlen($sql)-3);
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(count($rowset) != count($readers))
		{
			$this->template->assign_var('ABC_PAGE_CONTENT', $this->user->lang['ABC_FORUM_ERR_PAGE'].
															$this->user->lang['ABC_FORUM_ERR_STD'].
															"<br>$sql");
			return true;
		}
		for($i=0; $i<count($rowset); $i++)
		{
			$permissions[] = Array('group_id' => $rowset[$i]['group_id'],
							'auth_option_id' => 0,
							'auth_role_id' => 15,  //STANDARD
							'auth_setting' => 0 );
		}
		
		/*create_forum*/
		$options = array('name' => 'default', 'parent_id' => 0, 'forum_type' => 1);	//No needed, just looks nice
		$forum_id = $this->create_forum($options, $forum_data, $permissions);
		
		/*If Army 1, we need to move it up*/
		if($army == $this->config['army1_name'] && !$is_subforum)
		{
			/*Get other army's forums with HC as FULL*/
			$HC_group = $this->config['armyb_name'];
			$HC_group .= " HC";
			$sql = "SELECT group_id FROM ".GROUPS_TABLE." WHERE group_name = '$HC_group'";
			$result = $this->db->sql_query($sql);
			$group_id = $this->db->sql_fetchfield('group_id');
			$this->db->sql_freeresult($result);
			if(!$group_id)
			{
				$this->template->assign_var('ABC_PAGE_CONTENT', $this->user->lang['ABC_FORUM_ERR_PAGE'].
																$this->user->lang['ABC_FORUM_ERR_HCG'].
																$HC_group.
																"!<br>$sql");
				return;
			}
			
			$sql = "SELECT forum_id FROM ".ACL_GROUPS_TABLE." WHERE group_id = $group_id AND auth_role_id = 14";
			$result = $this->db->sql_query($sql);
			$rowset = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($result);
			
			/*If the other army has forums*/
			if(count($rowset) > 0)
			{
				/*Get "root" information*/
				$sql = "SELECT forum_id FROM ".FORUMS_TABLE." WHERE parent_id = $parent_id AND (";
				for($i=0; $i<count($rowset); $i++)
				{
					$sql .= " forum_id = ".$rowset[$i]['forum_id']." OR";
				}
				$sql = substr($sql, 0, strlen($sql)-2);
				$sql .= ")";
				$result = $this->db->sql_query($sql);
				$rowset = $this->db->sql_fetchrowset();
				$this->db->sql_freeresult($result);
				
				if(count($rowset) > 0)
				{
					/*Get data for the new forum*/
					$sql = "SELECT * FROM ".FORUMS_TABLE." WHERE forum_id = $forum_id";
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow();
					$this->db->sql_freeresult($result);
					
					/*Load functions and classes*/
					if(!class_exists('acp_forums'))
					{
						include $this->root_path . 'includes/acp/acp_forums.php';
					}
					if(!function_exists('validate_range'))
					{
						include $this->root_path . '/ext/globalconflict/abc/include/abc_forum_inc.php';
					}
					$forums_admin = new \acp_forums();
					/*Move the forum*/
					$moved = $forums_admin->move_forum_by($row, 'move_up', count($rowset));
					if(!$moved)
					{
						$this->template->assign_var('ABC_PAGE_CONTENT', $this->user->lang['ABC_FORUM_ERR_PAGE'].
																		$this->user->lang['ABC_FORUM_ERR_MOVENEW'].
																		"<br>$sql");
						return;
					}
				}
			}
		}
		
		$this->find_forums();
		return;
	}
	
	/**
	* create_forum Adapted from: https://www.phpbb.com/community/viewtopic.php?f=46&t=1289975#p12998250
	* Origional Author: Michael Fairchild <mfairchild365@gmail.com>
	*/
	function create_forum($options = array('name' => 'default', 'parent_id' => 0, 'forum_type' => 1), $forum_data = false, $permissions = false)
	{	
		if(!isset($options['parent_id']))
		{
			$options['parent_id'] = 0;
		}
		//forum type: 1 = forum, 0 = category.
		if(!isset($options['forum_type']))
		{
			$options['forum_type'] = 1;
		}
		
		if(!$forum_data)
		{
			$forum_data = array(
				'parent_id'					=> $options['parent_id'],	//parent_id
				'left_id'					=> 0,						//left_id
				'right_id'					=> 0,						//right_id
				'forum_parents'				=> '',						//forum_parents
				'forum_name'				=> $options['name'],		//forum_name
				'forum_desc'				=> '',						//forum_desc
				'forum_desc_bitfield'		=> '',						//forum_desc_bitfield
				'forum_desc_options'		=> 7,						//forum_desc_options
				'forum_desc_uid'			=> '',						//forum_desc_uid
				'forum_link'				=> '',						//forum_link
				'forum_password'			=> '',						//forum_password
				'forum_style'				=> 0,						//forum_style
				'forum_image'				=> '',						//forum_image
				'forum_rules'				=> '',						//forum_rules
				'forum_rules_link'			=> '',						//forum_rules_link
				'forum_rules_bitfield'		=> '',						//forum_rules_bitfield
				'forum_rules_options'		=> 7,						//forum_rules_options
				'forum_rules_uid'			=> '',						//forum_rules_uid
				'forum_topics_per_page'		=> 0,						//forum_topics_per_page
				'forum_type'				=> $options['forum_type'],	//forum_type
				'forum_status'				=> 0,						//forum_status
				'forum_last_post_id'		=> 0,						//forum_last_post_id
				'forum_last_poster_id'		=> 0,						//forum_last_poster_id
				'forum_last_post_subject'	=> '',						//forum_last_post_subject
				'forum_last_post_time'		=> 0,						//forum_last_post_time
				'forum_last_poster_name'	=> '',						//forum_last_poster_name
				'forum_last_poster_colour'	=> '',						//forum_last_poster_colour
				'forum_flags'				=> 32,						//forum_flags
				'display_on_index'			=> true,					//display_on_index
				'enable_indexing'			=> true,					//enable_indexing
				'enable_icons'				=> false,					//enable_icons
				'enable_prune'				=> false,					//enable_prune
				'prune_next'				=> 0,						//prune_next
				'prune_days'				=> 7,						//prune_days
				'prune_viewed'				=> 7,						//prune_viewed
				'prune_freq'				=> 1,						//prune_freq
				'display_subforum_list'		=> 1,						//display_subforum_list
				'forum_options'				=> 0,						//forum_options
				'enable_shadow_prune'		=> 0,						//enable_shadow_prune
				'prune_shadow_days'			=> 7,						//prune_shadow_days
				'prune_shadow_freq'			=> 1,						//prune_shadow_freq
				'prune_shadow_next'			=> 0,						//prune_shadow_next
			);
		}

		if(!class_exists('acp_forums'))
		{
			//global $phpbb_root_path, $phpEx;
			include $this->root_path . 'includes/acp/acp_forums.php';
		}
		if(!function_exists('validate_range'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_forum_inc.php';
		}

		$forums_admin = new \acp_forums();
		
		//update_forum_data will return only errors.  If success, there will be no return data.
		if($error = $forums_admin->update_forum_data($forum_data))
		{
			return false;
		}

		//Set the permissions
		if($permissions == false)
		{
			$permissions = Array(
			//guests
			Array('group_id' => 1,
				'forum_id' => $forum_data['forum_id'],
				'auth_option_id' => 0,
				'auth_role_id' => 17,  //READ ONLY
				'auth_setting' => 0 ),
			//Registered
			Array('group_id' => 2,
				'forum_id' => $forum_data['forum_id'],
				'auth_option_id' => 0,
				'auth_role_id' => 15,  //STANDARD
				'auth_setting' => 0 ),
			//Registered_coppa
			Array('group_id' => 3,
				'forum_id' => $forum_data['forum_id'],
				'auth_option_id' => 0,
				'auth_role_id' => 15,  //STANDARD
				'auth_setting' => 0 ),
			//Global_moderators
			Array('group_id' => 4,
				'forum_id' => $forum_data['forum_id'], 
				'auth_option_id' => 0,
				'auth_role_id' => 21,  //POLLS
				'auth_setting' => 0 ),
			//Admin
			Array('group_id' => 5,
				'forum_id' => $forum_data['forum_id'],
				'auth_option_id' => 0,
				'auth_role_id' => 14,  //FULL
				'auth_setting' => 0 ),
			Array('group_id' => 5,
				'forum_id' => $forum_data['forum_id'],
				'auth_option_id' => 0,
				'auth_role_id' => 10,  //MOD_FULL
				'auth_setting' => 0 ),
			//Bots
			Array('group_id' => 6,
				'forum_id' => $forum_data['forum_id'],
				'auth_option_id' => 0,
				'auth_role_id' => 19,  //BOT
				'auth_setting' => 0 ),
			//Newly_registered
			Array('group_id' => 7,
				'forum_id' => $forum_data['forum_id'],
				'auth_option_id' => 0,
				'auth_role_id' => 24, //NEW_MEMBER
				'auth_setting' => 0 ),
			);
		}
		else
		{
			for($i=0; $i<count($permissions); $i++)
			{
				$permissions[$i]['forum_id'] = $forum_data['forum_id'];
			}
		}

		$this->auth->acl_clear_prefetch();

		// Now insert the data
		if(!$this->db->sql_multi_insert(ACL_GROUPS_TABLE, $permissions))
		{
			return false;
		}
		
		return $forum_data['forum_id'];
	}
}