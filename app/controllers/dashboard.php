<?php
namespace Controllers;

class Dashboard
{
	private $_user;
	
	public function __construct()
	{
		// Load a user object
		$this->_user = new \DB\SQL\Mapper(\Base::instance()->get('DB'), 'users');
		$this->_user->load(array("id=?", \Base::instance()->get('SESSION.uid')));
	}
	
	public function index()
	{
		// Show the user's dashboard
		// TODO: There is lots of work to do (usability)!
		\Base::instance()->set('content', 'dashboard/board.htm');
	
		echo \View::instance()->render('layout.htm');
	}
	
	public function update()
	{
		$alias = \Base::instance()->get('POST.alias');
		$pubkey = \Base::instance()->get('POST.pubkey');
			
		if (!empty($alias))
			$this->_user->alias = \Base::instance()->clean($alias);
			
		if (!empty($pubkey))
			$this->_user->pubkey = \Base::instance()->clean($pubkey);
			
		$this->_user->save();
		\Base::instance()->reroute('/dashboard');
	}
	
	public function submit()
	{
		
	}
}