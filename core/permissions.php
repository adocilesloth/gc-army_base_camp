<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace globalconflict\abc\core;

class permissions
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user)
	{
		$this->db = $db;
		$this->user = $user;
	}
	
	/*Use whitelist*/
	public function whitelist($white_list)
	{
		foreach($white_list as $group)
		{
			$sql = 'SELECT group_id
				FROM ' . GROUPS_TABLE .
				" WHERE group_name = '".$group."'";
			$result = $this->db->sql_query($sql);
			$group_id = $this->db->sql_fetchfield('group_id');
			$this->db->sql_freeresult($result);
			
			if(!$group_id)
			{
				continue;
			}
			
			$sql = 'SELECT group_id
				FROM ' . USER_GROUP_TABLE . '
				WHERE group_id = ' . (int) $group_id . ' AND user_id = ' . $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$group_id = $this->db->sql_fetchfield('group_id');
			$this->db->sql_freeresult($result);
			
			if($group_id)
			{
				return true;
			}
		}
		return false;
	}
	
	/*Use blacklist*/
	public function blacklist($black_list)
	{
		foreach($black_list as $group)
		{
			$sql = 'SELECT group_id
				FROM ' . GROUPS_TABLE .
				" WHERE group_name = '".$group."'";
			$result = $this->db->sql_query($sql);
			$group_id = $this->db->sql_fetchfield('group_id');
			$this->db->sql_freeresult($result);
			
			if(!$group_id)
			{
				continue;
			}
			
			$sql = 'SELECT group_id
				FROM ' . USER_GROUP_TABLE . '
				WHERE group_id = ' . (int) $group_id . ' AND user_id = ' . $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$group_id = $this->db->sql_fetchfield('group_id');
			$this->db->sql_freeresult($result);
			
			if($group_id)
			{
				return false;
			}
		}
		return true;
	}
}