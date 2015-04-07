<?php
namespace Controllers;

class News
{
	private $_item;
	
	public function __construct()
	{	
		// Load a database mapper
		$this->_item = new \DB\SQL\Mapper(\Base::instance()->get('DB'), 'feed_items');
	}
	
	public function index()
	{
		$data = \Base::instance()->get('DB')->exec('SELECT * FROM `feed_items` WHERE `pubDate` > NOW() - INTERVAL 4 WEEK', null, 60);
		
		\Base::instance()->set('data', $data);
		\Base::instance()->set('content', 'news.htm');
		\Base::instance()->set('time', round(1e3*(microtime(TRUE) - \Base::instance()->get('TIME')),2));
		
		echo \View::instance()->render('layout.htm');
	}
}