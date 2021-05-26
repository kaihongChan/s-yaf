<?php


class IndexController extends BaseController
{
	public function indexAction()
	{
		var_dump(Yaf_Registry::get('REQUEST_HEADER')['token']);
		echo json_encode([
			'code' => 200,
			'status' => true,
			'msg' => '你好，开发者！',
			'data' => [],
		]);
	}
}