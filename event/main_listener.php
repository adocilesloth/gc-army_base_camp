<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'	=> 'load_language_on_setup',
			'core.page_header'	=> 'add_page_header_link',
		);
	}
	
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

	/**
	* Constructor
	*
	* @param \phpbb\controller\helper	$helper		Controller helper object
	* @param \phpbb\template\template	$template	Template object
	*/
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

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'globalconflict/abc',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link($event)
	{
		/*Don't allow randoms to see ABC*/
		$allowed = $this->user->data['user_id'] != ANONYMOUS;
		
		/*Campaign is running?*/
		$start = true;
		if($this->config['campaign_state'] != '0')
		{
			$start = false;
		}
		
		$nav_buttons = '<input type="submit" name="submit" id="submit" value="'.$this->user->lang['ABC_HOME'].'" class="button1"/>';			
		
		/*If user can enter draft*/
		$draftable = false;
		if(!$start)
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
				$nav_buttons .= ' <input type="submit" name="draft" id="draft" value="'.$this->user->lang['ABC_DRAFT'].'" class="button1"/>';
			}
			
			/*get if in draft list - they can still enter page but get a special message*/
			//$in_draft = false;
			$sql = "SELECT MAX(campaign_id) FROM abc_campaigns";
			$result = $this->db->sql_query($sql);
			$campaign_id = $this->db->sql_fetchfield('MAX(campaign_id)');
			$this->db->sql_freeresult($result);
			//$sql = 'SELECT user_id FROM abc_draft WHERE user_id = ';
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
		$can_see_draft = false;
		if(!$start)
		{
			$see_draft_list = [];
			$see_draft_list[] = $this->config['ta_name'];
			$see_draft_list[] = $this->config['army1_name'].' HC';
			$see_draft_list[] = $this->config['armyb_name'].' HC';
			
			$can_see_draft = $this->permissions->whitelist($see_draft_list);
			if($can_see_draft)
			{
				$nav_buttons .= ' <input type="submit" name="draft_list" id="draft_list" value="'.$this->user->lang['ABC_DRAFT_LIST'].'" class="button1"/>';
			}
		}
		/*If user can see army list*/
		$can_see_army = false;
		if(!$start)
		{
			$see_army_list = [];
			$see_army_list[] = $this->config['ta_name'];
			$see_army_list[] = $this->config['army1_name'];
			$see_army_list[] = $this->config['armyb_name'];
			
			$can_see_army = $this->permissions->whitelist($see_army_list);
			if($can_see_army)
			{
				$nav_buttons .= ' <input type="submit" name="army_list" id="army_list" value="'.$this->user->lang['ABC_ARMY'].'" class="button1"/>';
			}
		}
		/*If user can create medals/ranks/divisions - HC*/
		$can_see_logistics = false;
		if(!$start)
		{
			$logistics_nav_buttons = '';
			$see_logistics = [];
			$see_logistics[] = $this->config['army1_name'].' HC';
			$see_logistics[] = $this->config['armyb_name'].' HC';
			$can_see_logistics = $this->permissions->whitelist($see_logistics);
			if($can_see_logistics)
			{
				$nav_buttons .= ' <input type="submit" name="logistics_list" id="logistics_list" value="'.$this->user->lang['ABC_LOGISTICS'].'" class="button1"/>';
				$logistics_nav_buttons .= '<div class="panel"><div class="inner"><fieldset class="submit-buttons">';
				$logistics_nav_buttons .= ' <input type="submit" name="medal_list" id="medal_list" value="'.$this->user->lang['ABC_MEDAL'].'" class="button1"/>';
				$logistics_nav_buttons .= ' <input type="submit" name="rank_list" id="rank_list" value="'.$this->user->lang['ABC_RANK'].'" class="button1"/>';
				$logistics_nav_buttons .= ' <input type="submit" name="division_list" id="division_list" value="'.$this->user->lang['ABC_DIVISION'].'" class="button1"/>';
				$logistics_nav_buttons .= '</fieldset></div></div>';
			}
			$this->template->assign_var('ABC_LOGISTICS_NAV_BUTTONS', $logistics_nav_buttons);
		}
		/*If user can create forums - Generals*/
		$can_create_forum = false;
		if(!$start)
		{
			$see_forums = [];
			$see_forums[] = $this->config['army1_name'].' General';
			$see_forums[] = $this->config['armyb_name'].' General';
			
			$can_create_forum = $this->permissions->whitelist($see_forums);
			if($can_create_forum)
			{
				$nav_buttons .= ' <input type="submit" name="forum_list" id="forum_list" value="'.$this->user->lang['ABC_FORUM'].'" class="button1"/>';
			}
		}
		
		/*Start/Stop campaign permissions*/
		$start_string = $this->config['start_perm_groups'];
		$start_array = explode(",", $start_string);
		$admin = $this->permissions->whitelist($start_array);
		
		if($admin)
		{
			if($start)
			{
				$nav_buttons .= ' <input type="submit" name="start" id="start" value="'.$this->user->lang['ABC_START'].'" class="button1"/>';
			}
			else
			{
				$nav_buttons .= ' <input type="submit" name="finish" id="finish" value="'.$this->user->lang['ABC_FINISH'].'" class="button1"/>';
			}
		}
		
		$this->template->assign_vars(array(
			'ACP_ALLOWED'		=> $allowed,
			'ABC_NAV_BUTTONS'	=> $nav_buttons,
			
			'U_ABC_PAGE'	=> $this->helper->route('globalconflict_abc_controller', array('name' => 'Army Base Camp')),
		));
	}
}
