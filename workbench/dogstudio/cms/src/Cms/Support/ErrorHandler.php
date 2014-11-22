<?php  namespace Cms\Support;

use Whoops\Exception\ErrorException;

class ErrorHandler
{
    public function json()
    {
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                throw new ErrorException("Json parse error :  Maximum stack depth exceeded");
                break;
            case JSON_ERROR_CTRL_CHAR:
                throw new ErrorException("Json parse error :  Unexpected control character found");
                break;
            case JSON_ERROR_SYNTAX:
                throw new ErrorException("Json parse error :  Syntax error, malformed JSON, often due to a bad comma");
                break;
            case JSON_ERROR_NONE:
                return true;
                break;
        }

        return true;
    }
}
