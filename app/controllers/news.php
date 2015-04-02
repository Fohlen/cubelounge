<?php
namespace Controllers;

class News
{
	public function __construct()
	{
		
	}
	
	public function index()
	{
		$f3 = \Base::instance();
		$f3->set('content','welcome.htm');
		echo \View::instance()->render('layout.htm');
	}
}