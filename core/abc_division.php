<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_division
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
	
	public function division_list()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		/*Create new division*/
		$division_create = "<dl><dt><label for=\"division_name\">".$this->user->lang['ABC_DIVISION_NAME']."</label><br><span></span></dt>";
		$division_create .= "<dd><input type=\"text\" name=\"division_name\" value=\"\" maxlength=\"33\" size=\"39\" /></dd></dl>";
		/*division tag*/
		$division_create .= "<dl><dt><label for=\"division_tag\">".$this->user->lang['ABC_DIVISION_TAG']."</label><br><span></span></dt>";
		$division_create .= "<dd><input type=\"text\" name=\"division_tag\" value=\"\" maxlength=\"3\" size=\"3\" /></dd></dl>";
		/*division_image*/
		$division_create .= "<dl><dt><label for=\"division_image\">".$this->user->lang['ABC_DIVISION_IMAGE']."</label></dt>";
		$division_create .= "<dd><input type=\"file\" name=\"division_image\" id=\"division_image_".$division_id."\" class=\"inputbox autowidth\"/></dd></dl>";
		$division_create .= "<dl><input type=\"submit\" name=\"create_division\" id=\"create_division\" value=\"".$this->user->lang['ABC_LOGISTICS_CREATE']."\" class=\"button1\"/></dl>";
		
		$army_id = -1;
		$division_path = "";
		$this->get_division_path($division_path, $army_id);
			
		/*Get existing divisions*/
		$sql = "SELECT division_id, division_name, division_icon, division_tag FROM abc_divisions WHERE army_id = $army_id ORDER BY division_id ASC";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(!$rowset)
		{
			$this->template->assign_vars(array(
				'ABC_LOGISTICS_TITLE'		=> $this->user->lang['ABC_DIVISION_TITLE'],
				'ABC_LOGISTICS_EXPLAIN'		=> $this->user->lang['ABC_DIVISION_EXPLAIN'],
				'ABC_LOGISTICS_NEW'			=> $this->user->lang['ABC_DIVISION_NEW'],
				'ABC_LOGISTICS_CREATE'		=> $division_create,
				'ABC_LOGISTICS_EXIST'		=> $this->user->lang['ABC_DIVISION_EXIST'],
				'ABC_LOGISTICS_EXISTING'	=> $this->user->lang['ABC_NONE'],
			));
			return;
		}
		
		/*Create existing division list*/
		$division_list = "";
		for($i=0; $i<count($rowset); $i++)
		{
			$division_id = $rowset[$i]['division_id'];
			$division_name = sql_abc_unclean($rowset[$i]['division_name']);
			$division_tag = sql_abc_unclean($rowset[$i]['division_tag']);
			$division_image = "";
			if($army_id < 40)
			{
				$division_image .= $this->root_path."/abc/";
			}
			$division_image .= $rowset[$i]['division_icon'];
			
			$division_list .= "<div class=\"abc_medal_edit\">";
			/*division_name*/
			$division_list .= "<dl><dt><label for=\"division_name_".$division_id."\">".$this->user->lang['ABC_DIVISION_NAME']."</label><br><span></span></dt>";
			$division_list .= "<dd><input type=\"text\" name=\"division_name_".$division_id."\" value=\"$division_name\" maxlength=\"33\" size=\"39\" /></dd></dl>";
			/*division tag*/
			$division_list .= "<dl><dt><label for=\"division_tag\">".$this->user->lang['ABC_DIVISION_TAG']."</label><br><span></span></dt>";
			$division_list .= "<dd><input type=\"text\" name=\"division_tag_".$division_id."\" value=\"$division_tag\" maxlength=\"3\" size=\"3\" /></dd></dl>";
			/*division_image*/
			$division_list .= "<dl><dt><label for=\"division_image_".$division_id."\">".$this->user->lang['ABC_DIVISION_IMAGE']."</label></dt>";
			if($rowset[$i]['division_icon'] != '')
			{
				$division_list .= "<img src=\"/$division_image\" width=\"100\">";
			}
			$division_list .= "<dd><input type=\"file\" name=\"division_image_".$division_id."\" id=\"division_image_".$division_id."\" class=\"inputbox autowidth\"/></dd></dl>";
			/*Edit this division radio button*/
			$division_list .= "<dl><dt><label for=\"".$division_id."\">".$this->user->lang['ABC_DIVISION_EDIT_THIS']."</label></dt>";
			$division_list .= "<dd><input type=\"radio\" name=\"division_radio\" value=\"".$division_id."\"></dd></dl>";
			/*Edit button*/
			$division_list .= "<dl><input type=\"submit\" name=\"edit_division\" id=\"edit_division\" value=\"".$this->user->lang['ABC_LOGISTICS_EDIT']."\" class=\"button1\"/> ";
			if($i > 1)
			{
				/*Delete button*/
				$division_list .= "<input type=\"submit\" name=\"delete_division\" id=\"delete_division\" value=\"".$this->user->lang['ABC_LOGISTICS_DELETE']."\" class=\"button1\"/></dl>";
			}
			else
			{
				$division_list .= $this->user->lang['ABC_LOGISTICS_NO_DEL'];
			}
			
			$division_list .= "</div>";
		}
		
		$this->template->assign_vars(array(
			'ABC_LOGISTICS_TITLE'		=> $this->user->lang['ABC_DIVISION_TITLE'],
			'ABC_LOGISTICS_EXPLAIN'		=> $this->user->lang['ABC_DIVISION_EXPLAIN'],
			'ABC_LOGISTICS_NEW'			=> $this->user->lang['ABC_DIVISION_NEW'],
			'ABC_LOGISTICS_CREATE'		=> $division_create,
			'ABC_LOGISTICS_EXIST'		=> $this->user->lang['ABC_DIVISION_EXIST'],
			'ABC_LOGISTICS_EXISTING'	=> $division_list,
			
		));	
		return;
	}
	
	public function add_division()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		/*Get army_id and division path*/
		$army_id = -1;
		$division_path = "";
		$this->get_division_path($division_path, $army_id);
		
		$extn = ['png', 'jpeg', 'jpg', 'tiff', 'gif'];
		$upload = $this->factory->get('files.upload');
		$upload->set_allowed_extensions($extn);
		
		/*Upload division_image*/
		if(!$file = (isset($this->factory)) ? $upload->handle_upload('files.types.form', 'division_image') : $upload->form_upload('division_image') )
		{
			trigger_error($this->user->lang['ERR_UPLOAD']);
		}
		/*Save division_image*/
		$division_image = '';
		if($file->get('uploadname'))
		{
			$file->clean_filename('uploadname');
			$division_image .= $division_path . $file->get('uploadname');
			if(!$file->move_file($division_path, true, true, 0644))
			{
				trigger_error($this->user->lang['ERR_SAVE'].'<br>'
								."division_image: "
								.$division_image);
			}
			rename($division_path.$file->get('realname'), $division_image);
		}
		
		/*Get division_id*/
		$sql = "SELECT MAX(division_id) FROM abc_divisions";
		$result = $this->db->sql_query($sql);
		$division_id = $this->db->sql_fetchfield('MAX(division_id)');
		$this->db->sql_freeresult($result);
		$division_id++;
		
		$division_name = sql_abc_clean($this->request->variable('division_name', '', true));
		$division_tag = sql_abc_clean($this->request->variable('division_tag', '', true));
		$division_time_stamp = strtotime("now");
		
		/*Add division to abc_divisions*/
		$sql = "INSERT INTO abc_divisions VALUES ($division_id, $army_id, '$division_name', 0, 0, 0, '$division_tag', $division_time_stamp, '$division_image')";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload division list*/
		$this->division_list();
		return;
	}
	
	public function edit_division()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		$division_path = "";
		$army_id = -1;
		$rowset = $this->get_division_db_row($division_path, $army_id);
		if(!$rowset)
		{
			$this->division_list();
			return;
		}
		
		$division_id = $rowset['division_id'];
		$division_image = "";
		if($army_id < 40)
		{
			$division_image .= $this->root_path."/abc/";
		}
		$division_image .= $rowset['division_icon'];
		
		$extn = ['png', 'jpeg', 'jpg', 'tiff', 'gif'];
		$upload = $this->factory->get('files.upload');
		$upload->set_allowed_extensions($extn);
		
		/*Upload division_image*/
		if(!$file = (isset($this->factory)) ? $upload->handle_upload('files.types.form', 'division_image_'.$division_id) : $upload->form_upload('division_image_'.$division_id) )
		{
			trigger_error($this->user->lang['ERR_UPLOAD']);
		}
		
		/*Save division_image*/
		if($file->get('uploadname'))
		{
			if($file->get('uploadname'))
			{
				if($rowset['division_icon'] != '')
				{
					unlink($division_image);
				}
				$file->clean_filename('uploadname');
				$division_image = $division_path . $file->get('uploadname');
				if(!$file->move_file($division_path, true, true, 0644))
				{
					trigger_error($this->user->lang['ERR_SAVE'].'<br>'
									."division_image: "
									.$division_image);
				}
				rename($division_path.$file->get('realname'), $division_image);
			}
		}
		
		$division_name = sql_abc_clean($this->request->variable('division_name_'.$division_id, '', true));
		$division_tag = sql_abc_clean($this->request->variable('division_tag_'.$division_id, '', true));
		
		/*Update division in abc_divisions*/
		$sql = "UPDATE abc_divisions SET division_name = '$division_name', division_icon = '$division_image', division_tag = '$division_tag' WHERE division_id = $division_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload division list*/
		$this->division_list();
		return;
	}
	
	public function delete_division()
	{
		$division_path = "";
		$army_id = -1;
		$rowset = $this->get_division_db_row($division_path, $army_id);
		if(!$rowset)
		{
			$this->division_list();
			return;
		}
		
		$division_id = $rowset['division_id'];
		$division_image = "";
		if($army_id < 40)
		{
			$division_image .= $this->root_path."/abc/";
		}
		$division_image .= $rowset['division_icon'];
		$division_is_default = $rowset['division_is_default'];
		$division_is_hc = $rowset['division_is_hc'];
		
		/*If division is permanant, just return*/
		if($division_is_default == 1 || $division_is_hc == 1)
		{
			$this->division_list();
			return;
		}
		
		if($rowset['division_icon'] != '')
		{
			unlink($division_image);
		}
		
		$sql = "DELETE FROM abc_divisions WHERE division_id = $division_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload division list*/
		$this->division_list();
		return;
	}
	
	public function get_division_path(&$division_path, &$army_id)
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
		
		$division_path = $root_path."ext/globalconflict/abc/images/divisions/".$campaign_id."/".$army_id."/";
		return;
	}
	
	public function get_division_db_row(&$division_path, &$army_id)
	{
		/*Get army_id and division path*/
		$army_id = -1;
		$division_path = "";
		$this->get_division_path($division_path, $army_id);
		
		/*Get division_id*/
		$sql = "SELECT * FROM abc_divisions WHERE army_id = $army_id";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		if(!$rowset)
		{
			return false;
		}
		
		$row_idx = -1;
		$to_edit = $this->request->variable('division_radio', '');
		for($i=0; $i<count($rowset); $i++)
		{
			if($to_edit == $rowset[$i]['division_id'])
			{
				return $rowset[$i];
			}
		}
		
		return false;
	}
}