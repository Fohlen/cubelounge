<?php
namespace Controllers;

Class Test
{
	public function index()
	{
		\Base::instance()->set('content','welcome.htm');
		\Base::instance()->set('page', get_class($this));

		$template = new \Template();
		echo $template->render('layout.htm');
	}
}
