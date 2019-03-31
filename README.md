Army Base Camp
==============
This is a scratch rewrite of the Army Base Camp used my [Global Conflict](http://global-conflict.org/)
to administrate campaigns. The rewrite was undertaken as the origional ABC 
integrates with a 3.0.x version of phpbb and may rely on an old and unsupported
php; the phpbb version is unsupported.

I do not know how Rob wrote the origional ABC and felt it would be easier to 
rewrite from scratch than try to update older code.

To Do
-----

Partially Implimented
---------------------

Implimented
-----------
* Access to ABC (denied to the Anonymous user) `./event/main_listener.php`

* Start Campaign
	* Create TA and Army Groups `./core/abc_start.php`
	* Create Draft `./core/abc_start.php`
	* Create Common Forums `./core/abc_forum.php`

* Finish Campaign
	* Delete TA and Army Groups `./core/abc_finish.php`
	* Delete Draft `./core/abc_finish.php`
	* Archive Forums `./core/abc_forum.php`
	* Reset config `./core/abc_finish.php`

* Draft  `./core/abc_draft.php`

* Draft List `./core/abc_draft.php`

* Forums `./core/abc_forum.php`

* Army Medals `./core/abc_medal.php`

* Army Divisions `./core/abc_division.php`

* Army Ranks `./core/abc_rank.php`

* Army Management
	* Award medal `./core/abc_army.php`
	* Assign division `./core/abc_army.php`
	* Promote/demote army members `./core/abc_army.php`
	
* Battleday Signup `./core/abc_battleday.php`
