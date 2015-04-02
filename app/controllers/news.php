<?php
namespace Controllers;

class News
{
	private $_item;
	
	public function __construct()
	{	
		// Tiny macro to retrieve the class name
		$className = function($obj) {
			$classname = get_class($obj);
				
			if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
				$classname = $matches[1];
			}
				
			return $classname;
		};
		
		$this->_item = new \DB\SQL\Mapper(\Base::instance()->get('DB'), 'feed_items');
		\Base::instance()->set('page', $className($this));
	}
	
	public function index()
	{
		\Base::instance()->set('content', 'welcome.htm');
				
		echo \View::instance()->render('layout.htm');
	}
}