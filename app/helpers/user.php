<?php 
namespace Helpers;

/*
 * A user helper
 * isLoggedIn returns true/false because either 0 or 1 items can be found (id is unique)
 */

class User extends \Prefab
{
	private $_user; // User database object
	
	public function __construct()
	{
		$this->_user = new \DB\SQL\Mapper(\Base::instance()->get('DB'), 'users');
		$this->_user->load(array("id=?", \Base::instance()->get('SESSION.uid')));
	}
	
	public function __get($key)
	{
		if (in_array($key, $this->_user->fields()))
			return $this->_user->$key;
	}
	
	public function isLoggedIn()
	{
		return $this->_user->count(array("id=?", $this->_user->id));
	}
}

?>