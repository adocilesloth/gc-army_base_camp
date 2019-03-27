<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_medal
{
	/* @var \phpbb\config\config */
	protected $config;
	
	/* @var \phpbb\template\template */
	protected $template;
	
	/** @var request_interface */
	protected $request;
	
	/** @var \phpbb\files\factory */
	protected $factory;
	
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
		\phpbb\files\factory $factory,
		\phpbb\user $user,
		\phpbb\db\driver\driver_interface $db,
		$root_path)
	{
		$this->config = $config;
		$this->template = $template;
		$this->request = $request;
		$this->factory = $factory;
		$this->user = $user;
		$this->db = $db;
		$this->root_path = $root_path;
	}
	
	public function medal_list()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		/*Create new medal*/
		$medal_create = "<dl><dt><label for=\"medal_name\">".$this->user->lang['ABC_MEDAL_NAME']."</label><br><span></span></dt>";
		$medal_create .= "<dd><input type=\"text\" name=\"medal_name\" value=\"\" maxlength=\"44\" size=\"39\" /></dd></dl>";
		/*medal_description*/
		$medal_create .= "<dl><dt><label for=\"medal_desc\">".$this->user->lang['ABC_MEDAL_DESC']."</label><br><span></span></dt>";
		$medal_create .= "<dd><textarea class=\"abc_description\" name=\"medal_desc\" cols=\"40\" rows=\"5\" maxlength=\"242\"></textarea></dd></dl>";
		/*medal_image*/
		$medal_create .= "<dl><dt><label for=\"medal_image\">".$this->user->lang['ABC_MEDAL_IMAGE']."</label></dt>";
		$medal_create .= "<dd><input type=\"file\" name=\"medal_image\" id=\"medal_image_".$medal_id."\" class=\"inputbox autowidth\"/></dd></dl>";
		/*medal_ribbon*/
		$medal_create .= "<dl><dt><label for=\"medal_ribbon\">".$this->user->lang['ABC_MEDAL_RIBBON']."</label></dt>";
		$medal_create .= "<dd><input type=\"file\" name=\"medal_ribbon\" id=\"medal_ribbon\" class=\"inputbox autowidth\"/></dd></dl>";
		$medal_create .= "<dl><input type=\"submit\" name=\"create_medal\" id=\"create_medal\" value=\"".$this->user->lang['ABC_LOGISTICS_CREATE']."\" class=\"button1\"/></dl>";
		
		$army_id = -1;
		$medal_path = "";
		$this->get_medal_path($medal_path, $army_id);
		
		/*Get existing medals*/
		$sql = "SELECT medal_id, medal_name, medal_img, medal_ribbon, medal_description FROM abc_medals WHERE army_id = $army_id";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(!$rowset)
		{
			$this->template->assign_vars(array(
				'ABC_LOGISTICS_TITLE'		=> $this->user->lang['ABC_MEDAL_TITLE'],
				'ABC_LOGISTICS_EXPLAIN'		=> $this->user->lang['ABC_MEDAL_EXPLAIN'],
				'ABC_LOGISTICS_NEW'			=> $this->user->lang['ABC_MEDAL_NEW'],
				'ABC_LOGISTICS_CREATE'		=> $medal_create,
				'ABC_LOGISTICS_EXIST'		=> $this->user->lang['ABC_MEDAL_EXIST'],
				'ABC_LOGISTICS_EXISTING'	=> $this->user->lang['ABC_NONE'],
			));	
			return;
		}
		
		/*Create existing medal list*/
		$medal_list = "";
		for($i=0; $i<count($rowset); $i++)
		{
			$medal_id = $rowset[$i]['medal_id'];
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
			
			$medal_list .= "<div class=\"abc_medal_edit\">";
			/*medal_name*/
			$medal_list .= "<dl><dt><label for=\"medal_name_".$medal_id."\">".$this->user->lang['ABC_MEDAL_NAME']."</label><br><span></span></dt>";
			$medal_list .= "<dd><input type=\"text\" name=\"medal_name_".$medal_id."\" value=\"$medal_name\" maxlength=\"44\" size=\"39\" /></dd></dl>";
			/*medal_description*/
			$medal_list .= "<dl><dt><label for=\"medal_desc_".$medal_id."\">".$this->user->lang['ABC_MEDAL_DESC']."</label><br><span></span></dt>";
			$medal_list .= "<dd><textarea class=\"abc_description\" name=\"medal_desc_".$medal_id."\" cols=\"40\" rows=\"5\" maxlength=\"242\">$medal_desc</textarea></dd></dl>";
			/*medal_image*/
			$medal_list .= "<dl><dt><label for=\"medal_image_".$medal_id."\">".$this->user->lang['ABC_MEDAL_IMAGE']."</label></dt>";
			if($rowset[$i]['medal_img'] != '')
			{
				$medal_list .= "<img src=\"/$medal_image\" width=\"100\">";
			}
			$medal_list .= "<dd><input type=\"file\" name=\"medal_image_".$medal_id."\" id=\"medal_image_".$medal_id."\" class=\"inputbox autowidth\"/></dd></dl>";
			/*medal_ribbon*/
			$medal_list .= "<dl><dt><label for=\"medal_ribbon_".$medal_id."\">".$this->user->lang['ABC_MEDAL_RIBBON']."</label></dt>";
			if($rowset[$i]['medal_ribbon'] != '')
			{
				$medal_list .= "<img src=\"/$medal_ribbon\" width=\"100\">";
			}
			$medal_list .= "<dd><input type=\"file\" name=\"medal_ribbon_".$medal_id."\" id=\"medal_ribbon_".$medal_id."\" class=\"inputbox autowidth\"/></dd></dl>";
			/*Edit this medal radio button*/
			$medal_list .= "<dl><dt><label for=\"".$medal_id."\">".$this->user->lang['ABC_MEDIAL_EDIT_THIS']."</label></dt>";
			$medal_list .= "<dd><input type=\"radio\" name=\"medal_radio\" value=\"".$medal_id."\"></dd></dl>";
			/*Edit button*/
			$medal_list .= "<dl><input type=\"submit\" name=\"edit_medal\" id=\"edit_medal\" value=\"".$this->user->lang['ABC_LOGISTICS_EDIT']."\" class=\"button1\"/> ";
			/*Delete button*/
			$medal_list .= "<input type=\"submit\" name=\"delete_medal\" id=\"delete_medal\" value=\"".$this->user->lang['ABC_LOGISTICS_DELETE']."\" class=\"button1\"/></dl>";
			
			$medal_list .= "</div>";
		}
				
		$this->template->assign_vars(array(
			'ABC_LOGISTICS_TITLE'		=> $this->user->lang['ABC_MEDAL_TITLE'],
			'ABC_LOGISTICS_EXPLAIN'		=> $this->user->lang['ABC_MEDAL_EXPLAIN'],
			'ABC_LOGISTICS_NEW'			=> $this->user->lang['ABC_MEDAL_NEW'],
			'ABC_LOGISTICS_CREATE'		=> $medal_create,
			'ABC_LOGISTICS_EXIST'		=> $this->user->lang['ABC_MEDAL_EXIST'],
			'ABC_LOGISTICS_EXISTING'	=> $medal_list,
		));	
		return;
	}
	
	public function add_medal()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		/*Get army_id and medal path*/
		$army_id = -1;
		$medal_path = "";
		$this->get_medal_path($medal_path, $army_id);
		
		$extn = ['png', 'jpeg', 'jpg', 'tiff', 'gif'];
		$upload = $this->factory->get('files.upload');
		$upload->set_allowed_extensions($extn);
		
		/*Upload medal_image*/
		if(!$file = (isset($this->factory)) ? $upload->handle_upload('files.types.form', 'medal_image') : $upload->form_upload('medal_image') )
		{
			trigger_error($this->user->lang['ERR_UPLOAD']);
		}
		/*Save medal_image*/
		$medal_image = '';
		if($file->get('uploadname'))
		{
			$file->clean_filename('uploadname');
			$medal_image .= $medal_path . $file->get('uploadname');
			if(!$file->move_file($medal_path, true, true, 0644))
			{
				trigger_error($this->user->lang['ERR_SAVE'].'<br>'
								."medal_image: "
								.$medal_image);
			}
			rename($medal_path.$file->get('realname'), $medal_image);
		}
		
		unset($file);
		
		/*Upload medal_ribbon*/
		if(!$file = (isset($this->factory)) ? $upload->handle_upload('files.types.form', 'medal_ribbon') : $upload->form_upload('medal_ribbon') )
		{
			trigger_error($this->user->lang['ERR_UPLOAD']);
		}
		/*Save medal_ribbon*/
		$medal_ribbon = '';
		if($file->get('uploadname'))
		{
			$file->clean_filename('uploadname');
			$medal_ribbon .= $medal_path . $file->get('uploadname');
			if(!$file->move_file($medal_path, true, true, 0644))
			{
				trigger_error($this->user->lang['ERR_SAVE'].'<br>'
								."medal_ribbon: "
								.$medal_ribbon);
			}
			rename($medal_path.$file->get('realname'), $medal_ribbon);
		}
		
		/*Get medal_id*/
		$sql = "SELECT MAX(medal_id) FROM abc_medals";
		$result = $this->db->sql_query($sql);
		$medal_id = $this->db->sql_fetchfield('MAX(medal_id)');
		$this->db->sql_freeresult($result);
		$medal_id++;
		
		$medal_name = sql_abc_clean($this->request->variable('medal_name', '', true));
		$medal_desc = sql_abc_clean($this->request->variable('medal_desc', '', true));
		$medal_time_stamp = strtotime("now");
		
		/*Add medal to abc_medals*/
		$sql = "INSERT INTO abc_medals VALUES ($medal_id, $army_id, '$medal_name', '$medal_image', '$medal_ribbon', $medal_time_stamp, '$medal_desc')";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload medal list*/
		$this->medal_list();
		return;
	}
	
	public function edit_medal()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		$medal_path = "";
		$army_id = -1;
		$rowset = $this->get_medal_db_row($medal_path, $army_id);
		if(!$rowset)
		{
			$this->medal_list();
			return;
		}
		
		$medal_id = $rowset['medal_id'];
		$medal_image = "";
		$medal_ribbon = "";
		if($army_id < 40)
		{
			$medal_image .= $this->root_path."/abc/";
			$medal_ribbon .= $this->root_path."/abc/";
		}
		$medal_image .= $rowset['medal_img'];
		$medal_ribbon .= $rowset['medal_ribbon'];
		
		$extn = ['png', 'jpeg', 'jpg', 'tiff', 'gif'];
		$upload = $this->factory->get('files.upload');
		$upload->set_allowed_extensions($extn);
		
		/*Upload medal_image*/
		if(!$file = (isset($this->factory)) ? $upload->handle_upload('files.types.form', 'medal_image_'.$medal_id) : $upload->form_upload('medal_image_'.$medal_id) )
		{
			trigger_error($this->user->lang['ERR_UPLOAD']);
		}
		/*Save medal_image*/
		if($file->get('uploadname'))
		{
			if($rowset['medal_img'] != '')
			{
				unlink($medal_path);
			}
			$file->clean_filename('uploadname');
			$medal_image = $medal_path . $file->get('uploadname');
			if(!$file->move_file($medal_path, true, true, 0644))
			{
				trigger_error($this->user->lang['ERR_SAVE'].'<br>'
								."medal_image: "
								.$medal_image);
			}
			rename($medal_path.$file->get('realname'), $medal_image);
		}
		
		unset($file);
		
		/*Upload medal_ribbon*/
		if(!$file = (isset($this->factory)) ? $upload->handle_upload('files.types.form', 'medal_ribbon_'.$medal_id) : $upload->form_upload('medal_ribbon_'.$medal_id) )
		{
			trigger_error($this->user->lang['ERR_UPLOAD']);
		}
		/*Save medal_ribbon*/
		if($file->get('uploadname'))
		{
			if($rowset['medal_ribbon'] != '')
			{
				unlink($medal_ribbon);
			}
			$file->clean_filename('uploadname');
			$medal_ribbon = $medal_path . $file->get('uploadname');
			if(!$file->move_file($medal_path, true, true, 0644))
			{
				trigger_error($this->user->lang['ERR_SAVE'].'<br>'
								."medal_ribbon: "
								.$medal_ribbon);
			}
			rename($medal_path.$file->get('realname'), $medal_ribbon);
		}
		
		$medal_name = sql_abc_clean($this->request->variable('medal_name_'.$medal_id, '', true));
		$medal_desc = sql_abc_clean($this->request->variable('medal_desc_'.$medal_id, '', true));
		
		/*Update medal in abc_medals*/
		$sql = "UPDATE abc_medals SET medal_name = '$medal_name', medal_img = '$medal_image', medal_ribbon = '$medal_ribbon', medal_description = '$medal_desc' WHERE medal_id = $medal_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload medal list*/
		$this->medal_list();
		return;
	}
	
	public function delete_medal()
	{
		$medal_path = "";
		$army_id = -1;
		$rowset = $this->get_medal_db_row($medal_path, $army_id);
		if(!$rowset)
		{
			$this->medal_list();
			return;
		}
		
		$medal_id = $rowset['medal_id'];
		$medal_image = "";
		$medal_ribbon = "";
		if($army_id < 40)
		{
			$medal_image .= $this->root_path."/abc/";
			$medal_ribbon .= $this->root_path."/abc/";
		}
		$medal_image .= $rowset['medal_img'];
		$medal_ribbon .= $rowset['medal_ribbon'];
		
		if($rowset['medal_img'] != '')
		{
			unlink($medal_image);
		}
		if($rowset['medal_ribbon'] != '')
		{
			unlink($medal_ribbon);
		}
		
		$sql = "DELETE FROM abc_medals WHERE medal_id = $medal_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload medal list*/
		$this->medal_list();
		return;
	}
	
	public function get_medal_path(&$medal_path, &$army_id)
	{	
		$sql = "SELECT MAX(campaign_id) FROM abc_campaigns";
		$result = $this->db->sql_query($sql);
		$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
		$this->db->sql_freeresult($result);
		
		/*Get army_id*/
		$user_id = $this->user->data['user_id'];
		$sql = "SELECT army_id FROM abc_users WHERE user_id = $user_id and campaign_id = $campaign_id";
		$result = $this->db->sql_query($sql);
		$army_id = $this->db->sql_fetchfield('army_id');
		$this->db->sql_freeresult($result);
		
		$medal_path = $root_path."ext/globalconflict/abc/images/medals/".$campaign_id."/".$army_id."/";
		return;
	}
	
	public function get_medal_db_row(&$medal_path, &$army_id)
	{
		/*Get army_id and medal path*/
		$army_id = -1;
		$medal_path = "";
		$this->get_medal_path($medal_path, $army_id);
		
		/*Get medal_id*/
		$sql = "SELECT * FROM abc_medals WHERE army_id = $army_id";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		if(!$rowset)
		{
			return false;
		}
		
		$row_idx = -1;
		$to_edit = $this->request->variable('medal_radio', '');
		for($i=0; $i<count($rowset); $i++)
		{
			if($to_edit == $rowset[$i]['medal_id'])
			{
				return $rowset[$i];
			}
		}
		
		return false;
	}
}