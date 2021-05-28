<?php


class IndexController extends BaseApiController
{
	public function indexAction()
	{
		$response = new Yaf_Response_Http();
		$response->setHeader('HTTP/1.1', '404 Not Found');
		$response->setHeader('Content-Type', 'application/json');
		$response->setBody('你好，开发者！');
		$response->response();
	}
}