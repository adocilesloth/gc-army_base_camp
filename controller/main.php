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
	
	/** @var request_interface */
	protected $request;
	
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
	
	protected $root_path;

	public function __construct(
		\phpbb\controller\helper $helper,
		\phpbb\request\request $request,
		\globalconflict\abc\core\abc_start $abc_start,
		\globalconflict\abc\core\abc_finish $abc_finish,
		\globalconflict\abc\core\abc_draft $abc_draft,
		\globalconflict\abc\core\abc_army $abc_army,
		\globalconflict\abc\core\abc_forum $abc_forum,
		$root_path)
	{
		$this->helper		= $helper;
		$this->request		= $request;
		$this->abc_start	= $abc_start;
		$this->abc_finish	= $abc_finish;
		$this->abc_draft	= $abc_draft;
		$this->abc_army		= $abc_army;
		$this->abc_forum	= $abc_forum;
		$this->root_path	= $root_path;
	}
	
	public function handle($name)
	{
		/*Start Page*/
		if($this->request->is_set_post('start'))
		{
			$this->abc_start->start_page();
			return $this->helper->render('abc_start.html', $name);
		}
		/*Finish Page*/
		if($this->request->is_set_post('finish'))
		{
			$this->abc_finish->finish_page();
			return $this->helper->render('abc_finish.html', $name);
		}
		/*Draft Page*/
		if($this->request->is_set_post('draft'))
		{
			$this->abc_draft->draft_page();
			return $this->helper->render('abc_draft.html', $name);
		}
		/*Draft List*/
		if($this->request->is_set_post('draft_list'))
		{
			$this->abc_draft->draft_list();		
			return $this->helper->render('abc_draft_list.html', $name);
		}
		/*Army List*/
		if($this->request->is_set_post('army_list'))
		{
			$this->abc_army->army_list();
			return $this->helper->render('abc_army.html', $name);
		}
		/*Forum List*/
		if($this->request->is_set_post('forum_list'))
		{
			$this->abc_forum->find_forums();
			return $this->helper->render('abc_forum.html', $name);
		}
		/*Logistics*/
		if($this->request->is_set_post('logistics_list'))
		{
			return $this->helper->render('abc_medal.html', $name);
		}
		/*Medal Edit*/
		if($this->request->is_set_post('medal_edit'))
		{
			return $this->helper->render('abc_medal.html', $name);
		}
		/*Rank Edit*/
		if($this->request->is_set_post('rank_edit'))
		{
			return $this->helper->render('abc_medal.html', $name);
		}
		/*Division Edit*/
		if($this->request->is_set_post('division_edit'))
		{
			return $this->helper->render('abc_medal.html', $name);
		}
		
		/*Start Campaign*/
		if($this->request->is_set_post('start_submit'))
		{
			$worked = $this->abc_start->start_campaign();
			if($worked)
			{
				$this->abc_forum->start_forum();
			}
			return $this->helper->render('abc_start.html', $name);
		}
		/*End Campaign*/
		if($this->request->is_set_post('finish_submit'))
		{
			if($this->abc_forum->finish_forum())
			{
				$this->abc_finish->finish_campaign();	
			}
			return $this->helper->render('abc_finish.html', $name);
		}
		/*Join Draft*/
		if($this->request->is_set_post('draft_submit'))
		{
			$this->abc_draft->join_draft();
			return $this->helper->render('abc_draft.html', $name);
		}
		/*Leave Draft*/
		if($this->request->is_set_post('draft_leave'))
		{
			$this->abc_draft->leave_draft();	
			return $this->helper->render('abc_draft.html', $name);
		}
		/*Run Draft*/
		if($this->request->is_set_post('run_draft'))
		{
			$this->abc_draft->run_draft();		
			return $this->helper->render('abc_draft_list.html', $name);
		}
		/*Set Army List*/
		if($this->request->is_set_post('army_set'))
		{
			$this->abc_army->set_group();
			return $this->helper->render('abc_army.html', $name);
		}
		/*Create Forum*/
		if($this->request->is_set_post('create_forum'))
		{
			$this->abc_forum->add_forum();
			return $this->helper->render('abc_forum.html', $name);
		}

		return $this->helper->render('abc_home.html', $name);
	}
}