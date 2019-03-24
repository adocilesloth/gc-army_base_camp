<?php
/**
*
* @package phpBB Extension - Army Base Camp
* @copyright (c) 2019 Will Pearson
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	//ABC Home Page
	'ABC_PAGE'		=> 'ABC Home',
	'ABC_HOME'		=> 'Army Base Camp',
	'ABC_WELCOME'	=> 'Welcome to your Army Base Camp. It\'s currently a bit of a mess. But feel free to pitch your own tent.',

	//ACP ABC
	'ACP_ABC_TITLE'			=> 'Army Base Camp',
	'ACP_ABC'				=> 'Settings',
	'ACP_ABC_GOODBYE'		=> 'Should say goodbye?',
	'ACP_ABC_START_PERM'	=> 'ABC Administrator Groups',
	'ACP_ABC_START_EXPL'	=> 'Groups that can start and finish a campaign. Separate with commas (,).<br>Tournament Administrator group will be added and removed automatically.',
	'ACP_ABC_UNNEEDED'		=> 'Un-needed',
	'ACP_ABC_UNNEEDED_EXPL'	=> 'The below properties do not need to be changed. Just here in case something goes wrong.',
	'ACP_ABC_STATE'			=> 'Campaign State',
	'ACP_ABC_STATE_EXPL'	=> '0 = No campaign, 1 = active campaign',
	
	'ACP_ABC_SETTING_SAVED'	=> 'Settings have been saved successfully!',
	
	//ABC Links
	'ABC_MENU'			=> 'Menu',
	'ABC_START'			=> 'Start Campaign',
	'ABC_FINISH'		=> 'End Campaign',
	'ABC_DRAFT'			=> 'Player Draft',
	'ABC_DRAFT_LIST'	=> 'Draft List',
	'ABC_ARMY'			=> 'Army Home',
	'ABC_FORUM'			=> 'Army Forums',
	'ABC_LOGISTICS'		=> 'Army Logistics',
	'ABC_MEDAL'			=> 'Medals',
	'ABC_RANK'			=> 'Ranks',
	'ABC_DIVISION'		=> 'Divisions',
	
	//ABC User
	'ABC_USER_NOCAMP'	=> 'There is currently no campaign running.<br>Please check back soon.',
	'ABC_USER_INDRAFT'	=> 'You are in the player draft and will join an army soon!',
	'ABC_USER_INARMY'	=> 'You are part of ',
	'ABC_USER_TAG'		=> 'Your tag: ',
	'ABC_USER_NODRAFT'	=> 'There is a campaign running but you are not in it!',
	
	//ABC Start Page
	'ABC_START_TITLE' 		=> 'Campaign Setup',
	'ABC_START_NAME'		=> 'Campaign Name:',
	'ABC_START_DIV'			=> 'Divisions:',
	'ABC_START_DIV_EXPL'	=> 'Divisions for the player draft. Separate with commas (,).',
	'ABC_START_ARMY1_SEC'	=> 'Army 1 Setup',
	'ABC_START_ARMY1'		=> 'Army 1 Name:',
	'ABC_START_TAG1'		=> 'Army 1 Tag:',
	'ABC_START_COL1'		=> 'Army 1 Colour:',
	'ABC_START_GEN1'		=> 'Army 1 General:',
	'ABC_START_PW1'			=> 'Army 1 Password:',
	'ABC_START_ARMYB_SEC'	=> 'Army A Setup',
	'ABC_START_ARMYB'		=> 'Army A Name:',
	'ABC_START_TAGB'		=> 'Army A Tag:',
	'ABC_START_COLB'		=> 'Army A Colour:',
	'ABC_START_GENB'		=> 'Army A General:',
	'ABC_START_PWB'			=> 'Army A Password:',
	'ABC_START_TA_SEC'		=> 'Tournament Administrator Setup',
	'ABC_START_TA'			=> 'Tournament Administrator Name:',
	'ABC_START_TAGTA'		=> 'Tournament Administrator Tag:',
	'ABC_START_COLTA'		=> 'Tournament Administrator Colour:',
	'ABC_START_GENTA'		=> 'Head Tournament Administrator:',
	'ABC_START_PWTA'		=> 'Tournament Administrator Password:',
	'ABC_START_CREATE'		=> 'Create Campaign',
	'ABC_START_FAILED'		=> 'Creation Failed!',
	'ABC_START_FAILED_EXP'	=> 'Make sure all boxes are filled and the Generals exist.',
	'ABC_START_SUCCESS'		=> 'Campaign Created!',
	
	//ABC Finish Page
	'ABC_FINISH_TITLE'			=> 'End Campaign',
	'ABC_FINISH_WARN'			=> 'Pressing \'<b>End Campaign</b>\' will <b><i>close the campaign</i></b>. This <b><i>cannot</i></b> be automatically <b><i>be undone</i></b>.<br>
									If you press this in error, someone willl have to fix the mess you <i>will</i> make.<br>
									They will not be happy.',
	'ABC_FINISH_DONE'			=> 'Campaign has ended. Groups have been deleted. Forums have been archived.<br>Hope you meant to do that.',
	'ABC_FINISH_FAILED'			=> 'Failed to end campaign. Make sure archive forums exist and usergroups exist.',
	'ABC_FINISH_ARCH'			=> 'Archive Forum:',
	'ABC_FINISH_ARCH_EXPL'		=> 'Forum to archive the campaign forums to.',
	'ABC_FINISH_H_ARCH'			=> 'Hidden Archive Forum:',
	'ABC_FINISH_H_ARCH_EXPL'	=> 'Forum to archive Tournament Administrator and Tournament Administrator + HC forums to.',
	'ABC_FINISH_ARCH_G'			=> 'Archivist Group:',
	'ABC_FINISH_ARCH_G_EXPL'	=> 'Groups that can see the unhidden archives. Separate with commas (,). If there is only one group, put a comma (,) at the end.',
	
	//ABC Draft Page
	'ABC_DRAFT_TITLE'		=> 'Player Draft',
	'ABC_DRAFT_EXPLAIN'		=> 'Enter the player draft here. Select your prefered division and press "Join Draft".',
	'ABC_DRAFT_PW_EXPLAIN'	=> 'If you have an army password, enter it below and press "Join Draft".',
	'ABC_DRAFT_CHOOSE'		=> 'Choose division:',
	'ABC_DRAFT_AVAIL'		=> 'Availability:',
	'ABC_DRAFT_AVAIL_EXP'	=> 'Your expected availability. E.g. All 6 hours, once every two weeks, etc.',
	'ABC_DRAFT_LOCAL'		=> 'Location:',
	'ABC_DRAFT_LOCAL_EXP'	=> 'Roughly where you live. E.g. UK, West Coast US, Netherlands, etc.',
	'ABC_DRAFT_NOTES'		=> 'Other notes:',
	'ABC_DRAFT_NOTES_EXP'	=> 'Anything else you want to add.',
	'ABC_DRAFT_PW'			=> 'Army Password:',
	'ABC_DRAFT_PW_EXPL'		=> 'If you don\'t have one, leave this blank.',
	'ABC_DRAFT_JOIN'		=> 'Join Draft',
	'ABC_DRAFT_FUNNY'		=> 'You are absolutly fucking halarious...',
	'ABC_DRAFT_JOIN_ARMY'	=> 'You have joined ',
	'ABC_DRAFT_IN_DRAFT'	=> 'You are currently in the player draft. To leave the draft, press "Leave Draft" below.',
	'ABC_DRAFT_LEAVE'		=> 'Leave Draft',
	'ABC_DRAFT_LEFT'		=> 'You have left the player draft.',
	//ABC Draft List Page
	'ABC_DRAFT_LIST_TITLE'		=> 'Player Draft List',
	'ABC_DRAFT_LIST_EXPLAIN'	=> 'Below is a list, by division, of all players in the Player Draft.',
	'ABC_DRAFT_LIST_NAME'		=> 'Name:',
	'ABC_DRAFT_LIST_ARMY'		=> 'Assign to:',
	'ABC_DRAFT_LIST_RUN'		=> 'Run Draft',
	//ABC Draft Errors
	'ABC_DRAFT_ERR_GID'			=> 'Failed to find requested group',
	'ABC_DRAFT_ERR_WRONGNUM'	=> 'Number of army groups is not 3',
	
	//ABC Army Page
	'ABC_ARMY_TITLE'			=> 'Army Home',
	'ABC_ARMY_EXPLAIN'			=> 'Here, the army can be managed. Awarding medals and promotions and moving divisions happens here.',
	'ABC_ARMY_GENERAL'			=> 'General',
	'ABC_ARMY_MEDAL'			=> 'Award Medal',
	'ABC_ARMY_MEDAL_REASON'		=> 'Reason the medal was awarded',
	'ABC_ARMY_MEDAL_SUCCESS'	=> '<h2>Medal(s) awarded successfully!</h2>',
	'ABC_ARMY_RANK'				=> 'Award Rank',
	'ABC_ARMY_RANK_SUCCESS'		=> '<h2>Rank(s) awarded successfully!</h2>',
	'ABC_ARMY_DIVISION'			=> 'Move Division',
	'ABC_ARMY_DIVISION_SUCCESS'		=> '<h2>Division(s) moved successfully!</h2>',
	//ABC Army Errors
	'ABC_ARMY_ERR_MEDAL_NONE'		=> 'Awarding Medal Failed: No medal selected.',
	'ABC_ARMY_ERR_MEDAL_DATA'		=> 'Awarding Medal Failed: No army data.',
	'ABC_ARMY_ERR_MEDAL_USER'		=> 'Awarding Medal Failed: No soldiers were selected.',
	'ABC_ARMY_ERR_RANK_NONE'		=> 'Awarding Rank Failed: No rank selected.',
	'ABC_ARMY_ERR_RANK_DATA'		=> 'Awarding Rank Failed: No army data.',
	'ABC_ARMY_ERR_RANK_USER'		=> 'Awarding Rank Failed: No soldiers were selected.',
	'ABC_ARMY_ERR_DIVISION_NONE'	=> 'Move Division Failed: No division selected.',
	'ABC_ARMY_ERR_DIVISION_DATA'	=> 'Move Division Failed: No army data.',
	'ABC_ARMY_ERR_DIVISION_USER'	=> 'Move Division Failed: No soldiers were selected.',
	
	//ABC Forums
	'ABC_FORUM_CRRI'	=> 'Campaign Rules, Results and Information',
	'ABC_FORUM_FAILED'	=> 'But forum creation failed: ',
	//ABC Forum Start Errors
	'ABC_FORUM_ERR_GRP'	=> 'Error finding Army and TA groups!',
	'ABC_FORUM_ERR_GID'	=> 'Incorret Army and TA group_id!',
	'ABC_FORUM_ERR_CAT'	=> 'Unable to count forum catagories!',
	'ABC_FORUM_ERR_MOV'	=> 'Unable to move campaign category!',
	//ABC Forum Finish Errors
	'ABC_FORUM_ERR_ARK'	=> 'But forum archiving failed: ',
	'ABC_FORUM_ERR_CMP'	=> 'Unable to find campaign category!',
	'ABC_FORUM_ERR_CMP'	=> 'Unable to find campaign category!',
	'ABC_FORUM_ERR_HID'	=> 'Unable to find hidden forums!',
	'ABC_FORUM_ERR_HDT'	=> 'Unable to get hidden forums\'s data!',
	'ABC_FORUM_ERR_ACH'	=> 'Unable to find archive forums!',
	'ABC_FORUM_ERR_ADT'	=> 'Unable to get archive forums\'s data!',
	'ABC_FORUM_ERR_NEY'	=> '<br>Archiving will have to be done manually.',
	'ABC_FORUM_ERR_HAG'	=> 'Unable to find hidden archive groups!',
	'ABC_FORUM_ERR_AKG'	=> 'Unable to find archive groups!',
	'ABC_FORUM_ERR_AKP'	=> 'Unable to set archive permissions!',
	'ABC_FORUM_ERR_PER'	=> '<br>Permissions will have to be done manually.',
	
	//ABC Forums Page
	'ABC_FORUM_TITLE'		=> 'Army Forums',
	'ABC_FORUM_EXPLAIN'		=> 'Create forums for your army here. Officer forums cannot be seen by squaddies.',
	'ABC_FORUM_NAME'		=> 'Forum name:',
	'ABC_FORUM_NAME_EXPL'	=> 'Cannot contain underscores (_).',
	'ABC_FORUM_OFFICER'		=> 'Is officer forum:',
	'ABC_FORUM_CREATE'		=> 'Create Forum',
	'ABC_FORUM_SUBFORUM'	=> 'Is subforum:',
	'ABC_FORUM_PARENT'		=> 'Parent forum:',
	'ABC_FORUM_PARENT_EXPL'	=> 'If forum will be subforum',
	//ABC Forums Page Errors
	'ABC_FORUM_ERR_PAGE'	=> 'Forum creation failed: ',
	'ABC_FORUM_ERR_PARENT'	=> 'Unable to find (sub)forum parent!',
	'ABC_FORUM_ERR_ADMIN'	=> 'Unable to find admin groups!',
	'ABC_FORUM_ERR_STD'		=> 'Unable to find standard groups!',
	'ABC_FORUM_ERR_HCG'		=> 'Unable to find group_id for ',
	'ABC_FORUM_ERR_MOVENEW'	=> 'Unable to move new forum!',
	
	//ABC Logistics
	'ABC_LOGISTICS_CREATE'	=> 'Create',
	'ABC_LOGISTICS_EDIT'	=> 'Edit',
	'ABC_LOGISTICS_DELETE'	=> 'Delete',
	'ABC_LOGISTICS_NO_DEL'	=> 'Cannot Be Deleted',
	
	//ABC Medals Page
	'ABC_MEDAL_TITLE'		=> 'Army Medals',
	'ABC_MEDAL_EXPLAIN'		=> 'Create and edit medals here.',
	'ABC_MEDAL_NEW'			=> 'New Medal',
	'ABC_MEDAL_EXIST'		=> 'Edit Medal',
	'ABC_MEDAL_NAME'		=> 'Medal Name:',
	'ABC_MEDAL_NAME_EXIST'	=> 'Name',
	'ABC_MEDAL_DESC'		=> 'Medal Description:',
	'ABC_MEDAL_DESC_EXIST'	=> 'Description',
	'ABC_MEDAL_IMAGE'		=> 'Medal Image:',
	'ABC_MEDAL_IMAGE_EXIST'	=> 'Medal',
	'ABC_MEDAL_RIBBON'		=> 'Ribbon Image:',
	'ABC_MEDIAL_EDIT_THIS'	=> 'Edit This Medal:',
	
	//ABC Divisions Page
	'ABC_DIVISION_TITLE'		=> 'Army Divisions',
	'ABC_DIVISION_EXPLAIN'		=> 'Create and edit divisions here.',
	'ABC_DIVISION_NEW'			=> 'New Division',
	'ABC_DIVISION_EXIST'		=> 'Edit Division',
	'ABC_DIVISION_NAME'			=> 'Division Name:',
	'ABC_DIVISION_IMAGE'		=> 'Division Image:',
	'ABC_DIVISION_TAG'			=> 'Division Tag:',
	'ABC_DIVISION_EDIT_THIS'	=> 'Edit This Division:',
	
	//ABC Ranks Page
	'ABC_RANK_TITLE'		=> 'Army Ranks',
	'ABC_RANK_EXPLAIN'		=> 'Create and edit ranks here.',
	'ABC_RANK_NEW'			=> 'New Rank',
	'ABC_RANK_EXIST'		=> 'Edit Rank',
	'ABC_RANK_NAME'			=> 'Rank Name:',
	'ABC_RANK_SHORT'		=> 'Rank Abreviation:',
	'ABC_RANK_ORDER'		=> 'Rank Order:',
	'ABC_RANK_ORDER_EXPL'	=> 'The lower the order, the lower the rank.<br>1 is reserved for New Recruits and 99 for General.',
	'ABC_RANK_OFFICER'		=> 'Is Officer Rank:',
	'ABC_RANK_IMAGE'		=> 'Rank Image:',
	'ABC_RANK_TAG'			=> 'Rank Tag:',
	'ABC_RANK_EDIT_THIS'	=> 'Edit This Rank:',
	'ABC_RANK_NUMERIC'		=> 'Rank Order must be numeric and cannot be 1 or 99. Go back an try again.',
));
