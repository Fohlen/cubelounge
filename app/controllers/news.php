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
		//$data = \Base::instance()->get('DB')->exec('SELECT * FROM `feed_items` WHERE `pubDate` > NOW() - INTERVAL 4 WEEK ORDER BY `pubDate` LIMIT ?,10', 0, 60);
		$data = $this->_item->find("", array("order" => "pubDate DESC", "offset" => 0, "limit" => 10), 60);
		
		\Base::instance()->set('data', $data);
		\Base::instance()->set('page', 0);
		\Base::instance()->set('content', 'news.htm');
		
		echo \View::instance()->render('layout.htm');
	}
	
	public function page()
	{
		$param = \Base::instance()->get('PARAMS.param');
		$data = $this->_item->find("", array("order" => "pubDate DESC", "offset" => ($param * 10), "limit" => 10), 60);
		
		\Base::instance()->set('data', $data);
		\Base::instance()->set('page', $param);
		\Base::instance()->set('content', 'news.htm');
		
		echo \View::instance()->render('layout.htm');
	}
	
	public function id()
	{
		$id = \Base::instance()->clean(\Base::instance()->get('PARAMS.param'));
		$data = $this->_item->load(array("id=?", $id));
		
		// We could combine $this->_item->cast() tables with it's properties but this is probably faster (dirty)
		\Base::instance()->set('data', array($this->_item));
		\Base::instance()->set('paginate', false);
		\Base::instance()->set('content', 'news.htm');
		
		echo \View::instance()->render('layout.htm');
	}
}