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
		$data = $this->_item->find();
		\Base::instance()->set('contentAvailable', ($this->_item->count() > 0));
		\Base::instance()->set('data', $data);
		\Base::instance()->set('content', 'news.htm');
	
		echo \View::instance()->render('layout.htm');
	}
}