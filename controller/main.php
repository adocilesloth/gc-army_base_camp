<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\controller;

class main
{
	/* @var \phpbb\controller\helper */
	protected $helper;
	
	/* @var \phpbb\template\template */
	protected $template;
	
	/** @var request_interface */
	protected $request;
	
	/** @var \phpbb\user */
	protected $user;
	
	/* @var \globalconflict\abc\core\abc_start */
	protected $abc_start;
	
	/* @var \globalconflict\abc\core\abc_finish */
	protected $abc_finish;
	
	/* @var \globalconflict\abc\core\abc_draft */
	protected $abc_draft;
	
	/* @var \globalconflict\abc\core\abc_army */
	protected $abc_army;
	
	/* @var \globalconflict\abc\core\abc_forum */
	protected $abc_forum;
	
	/* @var \globalconflict\abc\core\abc_medal */
	protected $abc_medal;
	
	/* @var \globalconflict\abc\core\abc_division */
	protected $abc_division;
	
	/* @var \globalconflict\abc\core\abc_rank */
	protected $abc_rank;
	
	/* @var \globalconflict\abc\core\abc_history */
	protected $abc_history;
	
	/* @var \globalconflict\abc\core\abc_soldier */
	protected $abc_soldier;
	
	/* @var \globalconflict\abc\core\abc_battleday */
	protected $abc_battleday;
	
	/* @var \globalconflict\abc\core\abc_menu */
	protected $abc_menu;
	
	protected $root_path;

	public function __construct(
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\request\request $request,
		\phpbb\user $user,
		\globalconflict\abc\core\abc_start $abc_start,
		\globalconflict\abc\core\abc_finish $abc_finish,
		\globalconflict\abc\core\abc_draft $abc_draft,
		\globalconflict\abc\core\abc_army $abc_army,
		\globalconflict\abc\core\abc_forum $abc_forum,
		\globalconflict\abc\core\abc_medal $abc_medal,
		\globalconflict\abc\core\abc_division $abc_division,
		\globalconflict\abc\core\abc_rank $abc_rank,
		\globalconflict\abc\core\abc_history $abc_history,
		\globalconflict\abc\core\abc_soldier $abc_soldier,
		\globalconflict\abc\core\abc_battleday $abc_battleday,
		\globalconflict\abc\core\abc_menu $abc_menu,
		$root_path)
	{
		$this->helper			= $helper;
		$this->template			= $template;
		$this->request			= $request;
		$this->user				= $user;
		$this->abc_start		= $abc_start;
		$this->abc_finish		= $abc_finish;
		$this->abc_draft		= $abc_draft;
		$this->abc_army			= $abc_army;
		$this->abc_forum		= $abc_forum;
		$this->abc_medal		= $abc_medal;
		$this->abc_division		= $abc_division;
		$this->abc_rank			= $abc_rank;
		$this->abc_history		= $abc_history;
		$this->abc_soldier		= $abc_soldier;
		$this->abc_battleday	= $abc_battleday;
		$this->abc_menu			= $abc_menu;
		$this->root_path		= $root_path;
	}
	
	public function handle($name)
	{	
		/**Pages**/
		/*GC History*/
		if($this->request->is_set_post('history'))
		{
			$this->abc_history->show_history();
		}
		/*Soldier History*/
		elseif($this->request->is_set_post('soldier'))
		{
			$this->abc_soldier->show_soldier();
		}
		/*Draft Page*/
		elseif($this->request->is_set_post('draft'))
		{
			$this->abc_draft->draft_page();
		}
		/*Draft List*/
		elseif($this->request->is_set_post('draft_list'))
		{
			$this->abc_draft->draft_list();		
		}
		/*Army Home*/
		elseif($this->request->is_set_post('army_list'))
		{
			$this->abc_army->army_list();
		}
		/*Army Logistics*/
		elseif($this->request->is_set_post('logistics_list'))
		{
			$this->abc_medal->medal_list();
		}
		/*Army Logistics -> Medals*/
		elseif($this->request->is_set_post('medal_list'))
		{
			$this->abc_medal->medal_list();
		}
		/*Army Logistics -> Ranks*/
		elseif($this->request->is_set_post('rank_list'))
		{
			$this->abc_rank->rank_list();
		}
		/*Army Logistics -> Divisions*/
		elseif($this->request->is_set_post('division_list'))
		{
			$this->abc_division->division_list();
		}
		/*Army Forums*/
		elseif($this->request->is_set_post('forum_list'))
		{
			$this->abc_forum->find_forums();
		}
		/*Battleday Signup*/
		elseif($this->request->is_set_post('battle_signup'))
		{
			$this->abc_battleday->battleday_signup();
		}
		/*Battleday Logistics*/
		elseif($this->request->is_set_post('battle_list'))
		{
			$this->abc_battleday->battleday_list();
		}
		
		/*Start Campaign*/
		elseif($this->request->is_set_post('start'))
		{
			$this->abc_start->start_page();
		}
		/*End Campaign*/
		elseif($this->request->is_set_post('finish'))
		{
			$this->abc_finish->finish_page();
		}
		
		/**Actions**/
		/*GC History -> Army Select */
		elseif($this->request->is_set_post('select_army_history'))
		{
			$this->abc_history->show_selected_history();
		}
		/*Soldier History -> Soldier Search*/
		elseif($this->request->is_set_post('soldier_search'))
		{
			$this->abc_soldier->show_selected_soldier();
		}
		/*Draft Page -> Join Draft*/
		elseif($this->request->is_set_post('draft_submit'))
		{
			$this->abc_draft->join_draft();
		}
		/*Draft Page -> Leave Draft*/
		elseif($this->request->is_set_post('draft_leave'))
		{
			$this->abc_draft->leave_draft();	
		}
		/*Draft List -> Run Draft*/
		elseif($this->request->is_set_post('run_draft'))
		{
			$this->abc_draft->run_draft();		
		}
		/*Army Home -> Award Medal*/
		elseif($this->request->is_set_post('award_medal'))
		{
			$this->abc_army->award_medal();
		}
		/*Army Home -> Award Rank*/
		elseif($this->request->is_set_post('award_rank'))
		{
			$this->abc_army->award_rank();
		}
		/*Army Home -> Move Division*/
		elseif($this->request->is_set_post('award_division'))
		{
			$this->abc_army->award_division();
		}
		/*Army Logistics -> Medals -> Create*/
		elseif($this->request->is_set_post('create_medal'))
		{
			$this->abc_medal->add_medal();
		}
		/*Army Logistics -> Medals -> Edit*/
		elseif($this->request->is_set_post('edit_medal'))
		{
			$this->abc_medal->edit_medal();
		}
		/*Army Logistics -> Medals -> Delete*/
		elseif($this->request->is_set_post('delete_medal'))
		{
			$this->abc_medal->delete_medal();
		}
		/*Army Logistics -> Ranks -> Create*/
		elseif($this->request->is_set_post('create_rank'))
		{
			$this->abc_rank->add_rank();
		}
		/*Army Logistics -> Ranks -> Edit*/
		elseif($this->request->is_set_post('edit_rank'))
		{
			$this->abc_rank->edit_rank();
		}
		/*Army Logistics -> Ranks -> Delete*/
		elseif($this->request->is_set_post('delete_rank'))
		{
			$this->abc_rank->delete_rank();
		}
		/*Army Logistics -> Divisions -> Create*/
		elseif($this->request->is_set_post('create_division'))
		{
			$this->abc_division->add_division();
		}
		/*Army Logistics -> Divisions -> Edit*/
		elseif($this->request->is_set_post('edit_division'))
		{
			$this->abc_division->edit_division();
		}
		/*Army Logistics -> Divisions -> Delete*/
		elseif($this->request->is_set_post('delete_division'))
		{
			$this->abc_division->delete_division();
		}
		/*Army Forums -> Create Forum*/
		elseif($this->request->is_set_post('create_forum'))
		{
			$this->abc_forum->add_forum();
		}
		/*Battleday Signup -> Battleday Select*/
		elseif($this->request->is_set_post('select_battle_signup'))
		{
			$this->abc_battleday->select_battleday_signup();
		}
		/*Battleday Signup -> Sign Up*/
		elseif($this->request->is_set_post('sign_up'))
		{
			$this->abc_battleday->signup_to_battleday();
		}
		/*Battleday Logistics -> Create*/
		elseif($this->request->is_set_post('create_battle'))
		{
			$this->abc_battleday->add_battleday();
		}
		/*Battleday Logistics -> Edit*/
		elseif($this->request->is_set_post('edit_battle'))
		{
			$this->abc_battleday->edit_battleday();
		}
		/*Battleday Logistics -> Delete*/
		elseif($this->request->is_set_post('delete_battle'))
		{
			$this->abc_battleday->delete_battleday();
		}
		
		/*Start Campaign -> Create Campaign*/
		elseif($this->request->is_set_post('start_submit'))
		{
			$worked = $this->abc_start->start_campaign();
			if($worked)
			{
				$this->abc_forum->start_forum();
			}
		}
		/*End Campaign -> End Campaign*/
		elseif($this->request->is_set_post('finish_submit'))
		{
			if($this->abc_forum->finish_forum())
			{
				$this->abc_finish->finish_campaign();	
			}
		}
		
		/*ABC Home*/
		else
		{
			$abc_content = "<h2>".$this->user->lang['ABC_HOME']."</h2>";
			$abc_content .= "<p>".$this->user->lang['ABC_WELCOME']."</p>";
			$this->template->assign_var('ABC_PAGE_CONTENT', $abc_content);
		}
		
		$this->abc_menu->generate_menu();
		
		return $this->helper->render('abc_page.html', $name);
	}
}