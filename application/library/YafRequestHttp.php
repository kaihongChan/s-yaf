<?php


class YafRequestHttp extends Yaf_Request_Http
{
	protected static $server;
	protected static $get;
	protected static $post;
	protected static $files;
	protected static $rawContent;

	public function setServer($server): YafRequestHttp
	{
		self::$server = $server;

		return $this;
	}

	public function getServer($name = null, $default = null)
	{

	}

	public function setFiles($files): YafRequestHttp
	{
		self::$files = $files;

		return $this;
	}

	public function getFiles($name = null, $default = null)
	{

	}

	public function setGet($get): YafRequestHttp
	{
		self::$get = $get;

		return $this;
	}

	public function get($name, $default = null)
	{

	}

	public function setPost($post): YafRequestHttp
	{
		self::$post = $post;

		return $this;
	}

	public function setRawContent($rawContent): YafRequestHttp
	{
		self::$rawContent = $rawContent;

		return $this;
	}


}