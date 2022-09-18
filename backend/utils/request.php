<?php
trait RequestTrait
{
    public $allPostRequestValues = [];

   

    function sanitizeInput($input)
    {
        try {
            $input = $this->getExistingDBConnectionObject()->real_escape_string($input);
            $input = htmlspecialchars($input, ENT_QUOTES);
            return $input;
        } catch (Exception $e) {
            echo __FUNCTION__ .": Server connection error";
        }
    }

    function sanitizeInputArray($input)
    {
        try {
            foreach ($input as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $arrayKey => $arrayValues) {
                        if (!is_object($arrayValues)) {
                            $value[$arrayKey] = $this->getExistingDBConnectionObject()->real_escape_string($arrayValues);
                            $value[$arrayKey] = htmlspecialchars($value[$arrayKey], ENT_QUOTES);
                        }
                    }
                    $this->allPostRequestValues[$key] = $value;
                } else {
                    if (!is_object($value)) {
                        $value = $this->getExistingDBConnectionObject()->real_escape_string($value);
                        $value = htmlspecialchars($value, ENT_QUOTES);
                    }
                    $this->allPostRequestValues[$key] = $value;
                }
            }
        } catch (Exception $e) {
            echo __FUNCTION__ .": Server connection error";
        }
    }

    function processFullFeaturedEditorText($data)
    {
        $data = preg_replace('/-webkit-font-.*?;/i', "", $data);
        $data = preg_replace('/font-.*?;/i', "", $data);
        $data = preg_replace('/color:.*?;/i', "", $data);
        $data = preg_replace('/color=".*?"/i', "", $data);
        $data = preg_replace('/face=".*?"/i', "", $data);

        return $data;
    }

    function setPostRequestValues($getColName)
    {
        foreach ($getColName as $col) {
            if (isset($_POST[$col]) && $_POST[$col] != null) {
                // $this->allPostRequestValues[$col] = $_POST[$col];
                if (is_string($_POST[$col])) {
                    $this->allPostRequestValues[$col] = trim($_POST[$col]);
                } else {
                    $this->allPostRequestValues[$col] = $_POST[$col];
                }
            } else {
                $this->allPostRequestValues[$col] = "";
            }
        }
        return 1;
    }

    function setPostRequestValuesAllRequired($getColName)
    {
        foreach ($getColName as $col) {
            if (isset($_POST[$col]) && $_POST[$col] != null) {
                // $this->allPostRequestValues[$col] = $_POST[$col];
                if (is_string($_POST[$col])) {
                    $this->allPostRequestValues[$col] = trim($_POST[$col]);
                } else {
                    $this->allPostRequestValues[$col] = $_POST[$col];
                }
            } else {
                return [
                    "code" => 0,
                    "message" => ucfirst($col) . " is required.",
                    "statusCode" => 400
                ];
            }
        }
        return [
            "code" => 1,
            "message" => "",
        ];
    }

    function setRawPostRequestValues($getColName)
    {
        $_RAW_REQUEST_BODY  = file_get_contents('php://input');
        $_RAW_REQUEST_BODY = json_decode($_RAW_REQUEST_BODY);

        foreach ($getColName as $col) {
            if (isset($_RAW_REQUEST_BODY->$col) && $_RAW_REQUEST_BODY->$col != null) {
                // $this->allPostRequestValues[$col] = $_RAW_REQUEST_BODY->$col;
                // $this->allPostRequestValues[$col] = trim($_RAW_REQUEST_BODY->$col);
                if (is_string($_RAW_REQUEST_BODY->$col)) {
                    $this->allPostRequestValues[$col] = trim($_RAW_REQUEST_BODY->$col);
                } else {
                    $this->allPostRequestValues[$col] = $_RAW_REQUEST_BODY->$col;
                }
            } else {
                $this->allPostRequestValues[$col] = "";
            }
        }
        return 1;
    }

    function setRawPostRequestValuesAllRequired($getColName)
    {
        $_RAW_REQUEST_BODY  = file_get_contents('php://input');
        $_RAW_REQUEST_BODY = json_decode($_RAW_REQUEST_BODY);

        foreach ($getColName as $col) {
            if (isset($_RAW_REQUEST_BODY->$col) && $_RAW_REQUEST_BODY->$col != null) {
                // $this->allPostRequestValues[$col] = $_RAW_REQUEST_BODY->$col;
                if (is_string($_RAW_REQUEST_BODY->$col)) {
                    $this->allPostRequestValues[$col] = trim($_RAW_REQUEST_BODY->$col);
                } else {
                    $this->allPostRequestValues[$col] = $_RAW_REQUEST_BODY->$col;
                }
            } else {
                return [
                    "code" => 0,
                    "message" => ucfirst($col) . " is required.",
                    "statusCode" => 400
                ];
            }
        }
        return [
            "code" => 1,
            "message" => "",
        ];
    }

    function setGetRequestValuesAllRequired($getColName)
    {
        foreach ($getColName as $col) {
            if (isset($_GET[$col]) && $_GET[$col] != null) {
                // $this->allPostRequestValues[$col] = $_GET[$col];
                $this->allPostRequestValues[$col] = trim($_GET[$col]);
            } else {
                return [
                    "code" => 0,
                    "message" => ucfirst($col) . " is required.",
                    "statusCode" => 400
                ];
            }
        }
        return [
            "code" => 1,
            "message" => "",
        ];
    }
}
