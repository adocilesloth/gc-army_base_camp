<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class abc_rank
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
	
	public function rank_list()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		$abc_content = "<fieldset class=\"submit-buttons\">";
		$abc_content .= " <input type=\"submit\" name=\"medal_list\" id=\"medal_list\" value=\"".$this->user->lang['ABC_MEDAL']."\" class=\"button1\"/>";
		$abc_content .= " <input type=\"submit\" name=\"rank_list\" id=\"rank_list\" value=\"".$this->user->lang['ABC_RANK']."\" class=\"button1\"/>";
		$abc_content .= " <input type=\"submit\" name=\"division_list\" id=\"division_list\" value=\"".$this->user->lang['ABC_DIVISION']."\" class=\"button1\"/>";
		$abc_content .= "</fieldset></div></div>";
		$abc_content .= "<div class=\"panel\"><div class=\"inner\">";
		
		/*Create new rank*/
		$rank_create = "<dl><dt><label for=\"rank_name\">".$this->user->lang['ABC_RANK_NAME']."</label><br><span></span></dt>";
		$rank_create .= "<dd><input type=\"text\" class=\"inputbox\" name=\"rank_name\" value=\"\" maxlength=\"31\" size=\"39\" /></dd></dl>";
		/*rank_short*/
		$rank_create .= "<dl><dt><label for=\"rank_short\">".$this->user->lang['ABC_RANK_SHORT']."</label><br><span></span></dt>";
		$rank_create .= "<dd><input type=\"text\" class=\"inputbox\" name=\"rank_short\" value=\"\" maxlength=\"14\" size=\"39\" /></dd></dl>";
		/*rank tag*/
		$rank_create .= "<dl><dt><label for=\"rank_tag\">".$this->user->lang['ABC_RANK_TAG']."</label><br><span></span></dt>";
		$rank_create .= "<dd><input type=\"text\" class=\"inputbox\" name=\"rank_tag\" value=\"\" maxlength=\"3\" size=\"3\" /></dd></dl>";
		/*rank_order*/
		$rank_create .= "<dl><dt><label for=\"rank_order\">".$this->user->lang['ABC_RANK_ORDER']."</label><br><span>".$this->user->lang['ABC_RANK_ORDER_EXPL']."</span></dt>";
		$rank_create .= "<dd><input type=\"text\" class=\"inputbox\" name=\"rank_order\" value=\"\" maxlength=\"2\" size=\"3\" /></dd></dl>";
		/*rank_image*/
		$rank_create .= "<dl><dt><label for=\"rank_image\">".$this->user->lang['ABC_RANK_IMAGE']."</label></dt>";
		$rank_create .= "<dd><input type=\"file\" name=\"rank_image\" id=\"rank_image\" class=\"inputbox autowidth\"/></dd></dl>";
		/*rank_is_officer*/
		$rank_create .= "<dl><dt><label for=\"rank_is_officer\">".$this->user->lang['ABC_RANK_OFFICER']."</label></dt>";
		$rank_create .= "<input type=\"checkbox\" id=\"rank_is_officer\" name=\"rank_is_officer\"></dd></dl>";
		
		$rank_create .= "<dl><input type=\"submit\" name=\"create_rank\" id=\"create_rank\" value=\"".$this->user->lang['ABC_LOGISTICS_CREATE']."\" class=\"button1\"/></dl>";
		
		$army_id = -1;
		$rank_path = "";
		$this->get_rank_path($rank_path, $army_id);
		
		/*Get existing ranks*/
		$sql = "SELECT rank_id, rank_name, rank_short, rank_order, rank_is_officer, rank_img, rank_tag FROM abc_ranks WHERE army_id = $army_id ORDER BY rank_order ASC";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		if(!$rowset)
		{
			$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_RANK_TITLE']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_RANK_EXPLAIN']."</p>";
			$abc_content .= "</fieldset>";
			
			$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_RANK_NEW']."</h2>";
			$abc_content .= $rank_create;
			$abc_content .= "</fieldset>";
			
			$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
			$abc_content .= "<h2>".$this->user->lang['ABC_RANK_EXIST']."</h2>";
			$abc_content .= $this->user->lang['ABC_NONE'];
			$abc_content .= "</fieldset>";
			
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
			return;
		}
		
		/*Create existing rank list*/
		$rank_list = "";
		for($i=0; $i<count($rowset); $i++)
		{
			$rank_id = $rowset[$i]['rank_id'];
			$rank_name = sql_abc_unclean($rowset[$i]['rank_name']);
			$rank_short = sql_abc_unclean($rowset[$i]['rank_short']);
			$rank_tag = sql_abc_unclean($rowset[$i]['rank_tag']);
			$rank_order = $rowset[$i]['rank_order'];
			$disabled = '';
			if($rank_order == 1 || $rank_order == 99)
			{
				$disabled = 'disabled';
			}
			$rank_is_officer = (bool)$rowset[$i]['rank_is_officer'];
			$checked = '';
			if($rank_is_officer)
			{
				$checked = 'checked';
			}
			$rank_image = "";
			if($army_id < 40)
			{
				$rank_image .= $this->root_path."/abc/";
			}
			$rank_image .= $rowset[$i]['rank_img'];
			
			$rank_list .= "<div class=\"abc_medal_edit\">";
			/*rank_name*/
			$rank_list .= "<dl><dt><label for=\"rank_name_".$rank_id."\">".$this->user->lang['ABC_RANK_NAME']."</label><br><span></span></dt>";
			$rank_list .= "<dd><input type=\"text\" class=\"inputbox\" name=\"rank_name_".$rank_id."\" value=\"$rank_name\" maxlength=\"31\" size=\"39\" /></dd></dl>";
			/*rank_short*/
			$rank_list .= "<dl><dt><label for=\"rank_short\">".$this->user->lang['ABC_RANK_SHORT']."</label><br><span></span></dt>";
			$rank_list .= "<dd><input type=\"text\" class=\"inputbox\" name=\"rank_short_".$rank_id."\" value=\"$rank_short\" maxlength=\"14\" size=\"39\" /></dd></dl>";
			/*rank tag*/
			$rank_list .= "<dl><dt><label for=\"rank_tag\">".$this->user->lang['ABC_RANK_TAG']."</label><br><span></span></dt>";
			$rank_list .= "<dd><input type=\"text\" class=\"inputbox\" name=\"rank_tag_".$rank_id."\" value=\"$rank_tag\" maxlength=\"3\" size=\"3\" /></dd></dl>";
			/*rank_order*/
			$rank_list .= "<dl><dt><label for=\"rank_order\">".$this->user->lang['ABC_RANK_ORDER']."</label><br><span>".$this->user->lang['ABC_RANK_ORDER_EXPL']."</span></dt>";
			$rank_list .= "<dd><input type=\"text\" class=\"inputbox\" name=\"rank_order_".$rank_id."\" value=\"$rank_order\" maxlength=\"2\" size=\"3\" $disabled /></dd></dl>";
			/*rank_image*/
			$rank_list .= "<dl><dt><label for=\"rank_image_".$rank_id."\">".$this->user->lang['ABC_RANK_IMAGE']."</label></dt>";
			if($rowset[$i]['rank_img'] != '')
			{
				$rank_list .= "<img src=\"/$rank_image\" width=\"100\">";
			}
			$rank_list .= "<dd><input type=\"file\" name=\"rank_image_".$rank_id."\" id=\"rank_image_".$rank_id."\" class=\"inputbox autowidth\"/></dd></dl>";
			/*rank_is_officer*/
			$rank_list .= "<dl><dt><label for=\"rank_is_officer\">".$this->user->lang['ABC_RANK_OFFICER']."</label></dt>";
			$rank_list .= "<input type=\"checkbox\" id=\"rank_is_officer_".$rank_id."\" name=\"rank_is_officer_".$rank_id."\" $checked></dd></dl>";
			/*Edit this rank radio button*/
			$rank_list .= "<dl><dt><label for=\"".$rank_id."\">".$this->user->lang['ABC_RANK_EDIT_THIS']."</label></dt>";
			$rank_list .= "<dd><input type=\"radio\" name=\"rank_radio\" value=\"".$rank_id."\"></dd></dl>";
			/*Edit button*/
			$rank_list .= "<dl><input type=\"submit\" name=\"edit_rank\" id=\"edit_rank\" value=\"".$this->user->lang['ABC_LOGISTICS_EDIT']."\" class=\"button1\"/> ";
			if($rank_order == 1 || $rank_order == 99)
			{
				$rank_list .= $this->user->lang['ABC_LOGISTICS_NO_DEL'];
			}
			else
			{
				/*Delete button*/
				$rank_list .= "<input type=\"submit\" name=\"delete_rank\" id=\"delete_rank\" value=\"".$this->user->lang['ABC_LOGISTICS_DELETE']."\" class=\"button1\"/></dl>";
			}
			
			$rank_list .= "</div>";
		}
		
		$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= "<h2>".$this->user->lang['ABC_RANK_TITLE']."</h2>";
		$abc_content .= "<p>".$this->user->lang['ABC_RANK_EXPLAIN']."</p>";
		$abc_content .= "</fieldset>";
		
		$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= "<h2>".$this->user->lang['ABC_RANK_NEW']."</h2>";
		$abc_content .= $rank_create;
		$abc_content .= "</fieldset>";
		
		$abc_content .= "<fieldset class=\"fields2\" id=\"attach-panel-basic\">";
		$abc_content .= "<h2>".$this->user->lang['ABC_RANK_EXIST']."</h2>";
		$abc_content .= $rank_list;
		$abc_content .= "</fieldset>";
		
		$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		return;
	}
	
	public function add_rank()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		$rank_order = sql_abc_clean($this->request->variable('rank_order', 'poop'));
		if(!is_numeric($rank_order) || $rank_order < 2 || $rank_order > 98)
		{
			$this->template->assign_vars(array(
				'ABC_LOGISTICS_TITLE'		=> $this->user->lang['ABC_RANK_TITLE'],
				'ABC_LOGISTICS_EXPLAIN'		=> $this->user->lang['ABC_RANK_EXPLAIN'],
				'ABC_LOGISTICS_NEW'			=> $this->user->lang['ABC_RANK_NEW'],
				'ABC_LOGISTICS_CREATE'		=> $this->user->lang['ABC_RANK_NUMERIC'],
				'ABC_LOGISTICS_EXIST'		=> $this->user->lang['ABC_RANK_EXIST'],
			));	
			return;
		}
		
		/*Get army_id and rank path*/
		$army_id = -1;
		$rank_path = "";
		$this->get_rank_path($rank_path, $army_id);
		
		$extn = ['png', 'jpeg', 'jpg', 'tiff', 'gif'];
		$upload = $this->factory->get('files.upload');
		$upload->set_allowed_extensions($extn);
		
		/*Upload rank_image*/
		if(!$file = (isset($this->factory)) ? $upload->handle_upload('files.types.form', 'rank_image') : $upload->form_upload('rank_image') )
		{
			trigger_error($this->user->lang['ERR_UPLOAD']);
		}
		/*Save rank_image*/
		$rank_image = '';
		if($file->get('uploadname'))
		{
			$file->clean_filename('uploadname');
			$rank_image .= $rank_path . $file->get('uploadname');
			if(!$file->move_file($rank_path, true, true, 0644))
			{
				trigger_error($this->user->lang['ERR_SAVE'].'<br>'
								."rank_image: "
								.$rank_image);
			}
			rename($rank_path.$file->get('realname'), $rank_image);
		}
			
		$rank_name = sql_abc_clean($this->request->variable('rank_name', '', true));
		$rank_short = sql_abc_clean($this->request->variable('rank_short', '', true));
		$rank_tag = sql_abc_clean($this->request->variable('rank_tag', '', true));
		$rank_is_officer = (int)$this->request->variable('rank_is_officer', false);
		$rank_time_stamp = strtotime("now");
		
		/*Add rank to phpbb_ranks*/
		/*rank_id in phpbb_ranks auto increments, so we need to make the rank THEN find rank_phpbb_id*/
		$sql = "INSERT INTO phpbb_ranks (rank_title, rank_min, rank_special, rank_image) VALUES ('$army_id. $rank_name', 0, 1, '../../$rank_image')";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*get rank_phpbb_id*/
		$sql = "SELECT MAX(rank_id) FROM phpbb_ranks";
		$result = $this->db->sql_query($sql);
		$rank_phpbb_id = $this->db->sql_fetchfield('MAX(rank_id)');
		$this->db->sql_freeresult($result);
		
		/*Get rank_id*/
		$sql = "SELECT MAX(rank_id) FROM abc_ranks";
		$result = $this->db->sql_query($sql);
		$rank_id = $this->db->sql_fetchfield('MAX(rank_id)');
		$this->db->sql_freeresult($result);
		$rank_id++;
		
		/*Add rank to abc_ranks*/
		$sql = "INSERT INTO abc_ranks VALUES ($rank_id, $rank_phpbb_id, $army_id, '$rank_name', '$rank_short', $rank_order, $rank_is_officer, '$rank_image', '$rank_tag', $rank_time_stamp)";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload rank list*/
		$this->rank_list();
		return;
	}
	
	public function edit_rank()
	{
		if(!function_exists('sql_abc_clean'))
		{
			include $this->root_path . '/ext/globalconflict/abc/include/abc_sql_clean.php';
		}
		
		$rank_path = "";
		$army_id = -1;
		$rowset = $this->get_rank_db_row($rank_path, $army_id);
		if(!$rowset)
		{
			$this->rank_list();
			return;
		}
		
		$rank_id = $rowset['rank_id'];
		$rank_order = -1;
		if($rowset['rank_order'] == 1 || $rowset['rank_order'] == 99)
		{
			$rank_order = $rowset['rank_order'];
		}
		else
		{
			$rank_order = sql_abc_clean($this->request->variable('rank_order_'.$rank_id, 'poop'));
			if(!is_numeric($rank_order) || $rank_order < 2 || $rank_order > 98)
			{
				$this->template->assign_vars(array(
					'ABC_LOGISTICS_TITLE'		=> $this->user->lang['ABC_RANK_TITLE'],
					'ABC_LOGISTICS_EXPLAIN'		=> $this->user->lang['ABC_RANK_EXPLAIN'],
					'ABC_LOGISTICS_NEW'			=> $this->user->lang['ABC_RANK_NEW'],
					'ABC_LOGISTICS_EXIST'		=> $this->user->lang['ABC_RANK_EXIST'],
					'ABC_LOGISTICS_EXISTING'	=> $this->user->lang['ABC_RANK_NUMERIC'],
				));	
				return;
			}
		}
		
		$rank_image = "";
		if($army_id < 40)
		{
			$rank_image .= $this->root_path."/abc/";
		}
		$rank_image .= $rowset['rank_img'];
		
		$extn = ['png', 'jpeg', 'jpg', 'tiff', 'gif'];
		$upload = $this->factory->get('files.upload');
		$upload->set_allowed_extensions($extn);
		
		/*Upload rank_image*/
		if(!$file = (isset($this->factory)) ? $upload->handle_upload('files.types.form', 'rank_image_'.$rank_id) : $upload->form_upload('rank_image_'.$rank_id) )
		{
			trigger_error($this->user->lang['ERR_UPLOAD']);
		}
		
		/*Save rank_image*/
		if($file->get('uploadname'))
		{
			if($file->get('uploadname'))
			{
				if($rowset['rank_icon'] != '')
				{
					unlink($rank_image);
				}
				$file->clean_filename('uploadname');
				$rank_image = $rank_path . $file->get('uploadname');
				if(!$file->move_file($rank_path, true, true, 0644))
				{
					trigger_error($this->user->lang['ERR_SAVE'].'<br>'
									."rank_image: "
									.$rank_image);
				}
				rename($rank_path.$file->get('realname'), $rank_image);
			}
		}
		
		$rank_name = sql_abc_clean($this->request->variable('rank_name_'.$rank_id, '', true));
		$rank_short = sql_abc_clean($this->request->variable('rank_short_'.$rank_id, '', true));
		$rank_tag = sql_abc_clean($this->request->variable('rank_tag_'.$rank_id, '', true));
		$rank_is_officer = (int)$this->request->variable('rank_is_officer_'.$rank_id, false);
		
		/*Update rank in abc_ranks*/
		$sql = "UPDATE abc_ranks 
				SET rank_name = '$rank_name', rank_short = '$rank_short', rank_order = $rank_order, rank_is_officer = $rank_is_officer, rank_img = '$rank_image', rank_tag = '$rank_tag' 
				WHERE rank_id = $rank_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		$rank_phpbb_id = $rowset['rank_phpbb_id'];
		/*Update rank in phpbb_ranks*/
		$sql = "UPDATE phpbb_ranks SET rank_title = '$army_id. $rank_name', rank_image = '../../$rank_image' WHERE rank_id = $rank_phpbb_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload rank list*/
		$this->rank_list();
		return;
	}
	
	public function delete_rank()
	{
		$rank_path = "";
		$army_id = -1;
		$rowset = $this->get_rank_db_row($rank_path, $army_id);
		if(!$rowset)
		{
			$this->rank_list();
			return;
		}
		
		$rank_id = $rowset['rank_id'];
		$rank_image = "";
		if($army_id < 40)
		{
			$rank_image .= $this->root_path."/abc/";
		}
		$rank_image .= $rowset['rank_img'];
		$rank_order = $rowset['rank_order'];
		
		/*If rank is permanant, just return*/
		if($rank_order == 1 || $rank_order == 99)
		{
			$this->rank_list();
			return;
		}
		
		if($rowset['rank_img'] != '')
		{
			unlink($rank_image);
		}
		
		$sql = "DELETE FROM abc_ranks WHERE rank_id = $rank_id";
		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);
		
		/*Reload rank list*/
		$this->rank_list();
		return;
	}
	
	public function get_rank_path(&$rank_path, &$army_id)
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
		
		$rank_path = $root_path."ext/globalconflict/abc/images/ranks/".$campaign_id."/".$army_id."/";
		return;
	}
	
	public function get_rank_db_row(&$rank_path, &$army_id)
	{
		/*Get army_id and rank path*/
		$army_id = -1;
		$rank_path = "";
		$this->get_rank_path($rank_path, $army_id);
		
		/*Get rank_id*/
		$sql = "SELECT * FROM abc_ranks WHERE army_id = $army_id";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult($result);
		
		if(!$rowset)
		{
			return false;
		}
		
		$row_idx = -1;
		$to_edit = $this->request->variable('rank_radio', '');
		for($i=0; $i<count($rowset); $i++)
		{
			if($to_edit == $rowset[$i]['rank_id'])
			{
				return $rowset[$i];
			}
		}
		
		return false;
	}
}