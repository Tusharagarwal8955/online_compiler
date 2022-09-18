<?php
trait ResponseTrait
{
    function sendResponse($response, $status_code = 200)
    {
        http_response_code($status_code);
        return json_encode($response);
    }

    function escapeString(String $input)
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}
