<?php


class ErrorController extends BaseApiController
{
    public function errorAction($exception)
    {
        /* error occurs */
        switch ($exception->getCode()) {
            case YAF_ERR_NOTFOUND_MODULE:
            case YAF_ERR_NOTFOUND_CONTROLLER:
            case YAF_ERR_NOTFOUND_ACTION:
            case YAF_ERR_NOTFOUND_VIEW:
                echo 404, ":", $exception->getMessage();
                break;
            default:
                echo 0, ":", $exception->getMessage();
                break;
        }
    }

}