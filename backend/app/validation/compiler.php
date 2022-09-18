<?php

trait CompilerValidationTrait
{
    function compileCode_Validation() 
    {
        $requiredFields = array("language", "code");
        $optionalFields = array("input_var");
        $requestDataResponse = $this->setPostRequestValuesAllRequired($requiredFields);
        if ($requestDataResponse["code"] != 1) {
            return $requestDataResponse;
        }
        // $this->sanitizeInputArray($this->allPostRequestValues);
        $this->setPostRequestValues($optionalFields);
        
        $file_key_name = "code";
        $language = $this->allPostRequestValues["language"] ?? "";
        
        $input_var = $this->allPostRequestValues["input_var"] ?? "";
        $input_var_list = [];
        if(!empty($input_var)){
            $input_var = str_replace("\r", "", $input_var);
            $input_var = str_replace("\\r", "", $input_var);
            $input_var_list = explode("\n", $input_var);
        }
        $this->allPostRequestValues["input_var_list"] = $input_var_list;
        
        $this->allPostRequestValues["file_key_name"] = $file_key_name;        
        
        if(!in_array($language, array_column(LanguagesEnum::cases(), "name"))){ 
            $response = [
                "code" => 0,
                "message" => "Language is not supported by our compiler",
                "statusCode" => $this->validationFailureStatusCode
            ];
            return $response;
        }
        if(!empty($input_var_list) && !is_array($input_var_list)){
            $response = [
                "code" => 0,
                "message" => "Input var must be an array",
                "statusCode" => $this->validationFailureStatusCode
            ];
            return $response;
        }

        //code file validation
        // if (!isset($_FILES[$file_key_name]) || $_FILES[$file_key_name]["size"] < 1) {
        //     $response = [
        //         "code" => 0,
        //         "message" => "No code provided to compile.",
        //         "statusCode" => $this->validationFailureStatusCode
        //     ];
        //     return $response;
        // }
        // if (!in_array($_FILES[$file_key_name]['type'], ["text/plain"])) {
        //     $response = [
        //         "code" => 0,
        //         "message" => "Invalid code file, Only txt files are allowed!",
        //         "statusCode" => 400
        //     ];
        //     return $response;
        // }
        $response = [
            "code" => 1,
            "message" => "Validation Successful.",
            "statusCode" => $this->validationSuccessStatusCode
        ];
        return $response;
        
    }
}
