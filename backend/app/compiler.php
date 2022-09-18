<?php
require_once __DIR__ . "/validation/compiler.php";
class Compiler
{
    use FunctionTrait, CompilerValidationTrait;

    function __construct()
    {
        $this->connectToDB();
    }
    function __destruct()
    {
        $this->disconnectFromDB();
    }

    function getSupportedLanguage()
    {
        return $this->sendResponse(array_column(LanguagesEnum::cases(), "name"));
    }

    function compileCode()
    {
        $validationRespones = $this->compileCode_Validation();
        if ($validationRespones["code"] != 1) {
            $response = [
                "status" => 0,
                "message" => $validationRespones["message"]
            ];
            return $this->sendResponse($response, $validationRespones["statusCode"]);
        }

        $language = $this->allPostRequestValues["language"] ?? "";
        $code = $this->allPostRequestValues["code"] ?? "";
        // $file_key_name = $this->allPostRequestValues["file_key_name"] ?? "";
        $input_var_list = $this->allPostRequestValues["input_var_list"] ?? "";

        $languageObject = LanguagesEnum::getObject($language);
        $random_file_name = $this->generateRandomString(10) . "_" . time();
        $fileUploadPath = $languageObject->getCodeFileUploadPath() . $random_file_name . "/";
        if ($languageObject->name == "JAVA") {
            $random_file_name = "Main";
        }
        if (!file_exists($fileUploadPath)) {
            $output = mkdir($fileUploadPath, 0777, true);
            if (empty($output)) {
                $response = [
                    "status" => 0,
                    "message" => "Unable to create directory for code file upload"
                ];
                return $this->sendResponse($response, 400);
            }
        }
        $filePath = $fileUploadPath . $random_file_name . $languageObject->getFileExtension();;
        // if (!move_uploaded_file($_FILES[$file_key_name]['tmp_name'], $filePath)) {
        //     $response = ["error" => "Unable to process the code file"];
        //     return $this->sendResponse($response, 500);
        // }

        //  $code = str_replace("\n", PHP_EOL, $code);
        // echo $code = str_replace("\\n", PHP_EOL, $code);

        $file = fopen($filePath, "w");
        fwrite($file, $code);
        fclose($file);


        $variables = [
            "{{FILE_PATH}}" => $filePath,
            "{{FILE_UPLOAD_PATH}}" => $fileUploadPath,
            "{{FILE_NAME}}" => $random_file_name,
        ];
        $shellCommandsList = $languageObject->getCommand();
        $outputList = [];
        $executedCommandsList = [];
        $working_dir = __DIR__;
        $start_time = microtime(true);
        $sleep_time = 0;
        for ($i = 0; $i < count($shellCommandsList); $i++) {
            $shellCommand = $shellCommandsList[$i];
            if ($shellCommand == 'chdir') {
                chdir($fileUploadPath);
            } else if ($shellCommand == 'rechdir') {
                chdir($working_dir);
            } else {
                $this->setTemplateVeriables($variables, $shellCommand);
                if ($i == count($shellCommandsList) - 1) {
                    $process = proc_open(
                        $shellCommand,
                        [
                            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
                            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
                        ],
                        $pipes
                    );

                    $processStatus = proc_get_status($process);
                    $isRunning = $processStatus['running'] ?? false;
                    while ($isRunning) {
                        $sleep_time += 0.1;
                        usleep(100000);
                        $processStatus = proc_get_status($process);
                        $isRunning = $processStatus['running'] ?? false;
                        if ($sleep_time > 3) {
                            fclose($pipes[0]);
                            fclose($pipes[1]);
                            // proc_terminate($process, 9);
                            $this->kill($process);
                            proc_close($process);

                            $this->rrmdir($fileUploadPath);
                            
                            $response = [
                                "status" => 0,
                                "message" => "TLE: Code is taking too long to execute.",
                            ];
                            echo $this->sendResponse($response, 400);
                            exit(0);
                        }
                    }

                    if (!empty($input_var_list)) {
                        foreach ($input_var_list as $input) {
                            fwrite($pipes[0], $input . "\n");
                            // if (is_numeric($input)) {
                            //     $input = (double)$input;
                            // } else {
                            //     fwrite($pipes[0], $input . "\n");
                            // }
                        }
                    }
                    fclose($pipes[0]);
                    $output = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    proc_close($process);
                } else {
                    $output = shell_exec($shellCommand);
                }
                if (!empty($output)) {
                    array_push($outputList, $output);
                }
                array_push($executedCommandsList, $shellCommand);
            }
        }

        $this->rrmdir($fileUploadPath);

        $execution_time = microtime(true) - ($start_time + $sleep_time);
        $response = [
            "status" => 1,
            "message" => "Code compiled successfully",
            "output" => $outputList,
            "executed_commands" => $executedCommandsList,
            "execution_time" => $execution_time,
        ];
        return $this->sendResponse($response);
    }

    function kill($process)
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            $status = proc_get_status($process);
            return exec('taskkill /F /T /PID ' . $status['pid']);
        } else {
            return proc_terminate($process);
        }
    }
}
