<?php


class UserController extends BaseApiController
{
	public function indexAction()
	{
		$this->response('/auth/user/index');
	}
}