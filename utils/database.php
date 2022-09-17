<?php

trait DatabaseTrait
{
    private $_DATABASE_CONNECTION_VARIABLE;

    private $db_host = DB_HOST;
    private $db_user = DB_USER;
    private $db_password = DB_PASSWORD;
    private $db_name = DB_NAME;

    private function establishConnection(): mysqli | false
    {
        $conn = mysqli_connect($this->db_host, $this->db_user, $this->db_password, $this->db_name);
        if (!$conn) {
            $this->databaseConnectionError();
        }
        return $conn;
    }

    private function getExistingDBConnectionObject(): mysqli | false | null
    {
        return $this->_DATABASE_CONNECTION_VARIABLE;
    }

    private function connectToDB()
    {
        $this->_DATABASE_CONNECTION_VARIABLE = $this->establishConnection();
    }

    function databaseConnectionError()
    {
        $response = array(
            "error" => "Database connection error."
        );
        http_response_code(500);
        echo json_encode($response);
        exit();
    }

    function disconnectFromDB()
    {
        $this->getExistingDBConnectionObject()->close();
    }

    function runSQL($sql, array $args = null, array $log_data = [])
    {
        if (!empty($args)) {
            $count = count($args);
            $strings = array_fill(0, $count, "s");
            $strings = implode("", $strings);
        }

        try {
            $conn = $this->getExistingDBConnectionObject(0);
            if ($conn) {
                $stmt = $conn->stmt_init();
                if (!$stmt->prepare($sql)) {
                    if (!empty($log_data)) {
                        $this->sqlQueryExecutionLog($sql, $args, "Failed", "Prepared statement failed", $log_data);
                    }
                    return 0;
                } else {
                    if (!empty($args))
                        $stmt->bind_param($strings, ...$args);
                    $result = $stmt->execute();
                    //print_r($stmt->error);
                    if ($result) {
                        $stmt->store_result();
                        if (!empty($log_data)) {
                            $this->sqlQueryExecutionLog($sql, $args, "Success", "Query executed successfully", $log_data);
                        }
                        return $stmt;
                    } else {
                        $error = $stmt->error ?? "";
                        if (!empty($log_data)) {
                            $this->sqlQueryExecutionLog($sql, $args, "Failed", "Unexpected failure: $error.", $log_data);
                        }
                        return 0;
                    }
                }
            } else {
                if (!empty($log_data)) {
                    $this->sqlQueryExecutionLog($sql, $args, "Failed", "Failed to connect with database.", $log_data);
                }
                return 0;
            }
        } catch (Exception $e) {
            echo __FUNCTION__ .": Server connection error";
        }
    }

    function sqlQueryExecutionLog(string $sqlQuery, array $args, string $status, string $message, array $log_data)
    {
        $panel = $this->CURRENT_PANEL_NAME ?? "";
        $module = $this->CURRENT_MODULE_NAME ?? "";
        $function = $log_data["function"] ?? "";
        $operation = $log_data["operation"] ?? "";
        $user_id = $log_data["user_id"] ?? "0";

        $conn = $this->establishConnection();

        $args = json_encode($args);
        $timestamp = time();
        $datetime = date("Y-m-d H:i:s");
        $sql = "INSERT INTO logs_sql(timestamp, datetime, panel, module, `function`, operation, user_id, query, args, status, message)
        VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";


        $result = false;
        $stmt = $conn->stmt_init();

        if ($stmt->prepare($sql)) {
            $stmt->bind_param("sssssssssss", $timestamp, $datetime, $panel, $module, $function, $operation, $user_id, $sqlQuery, $args, $status, $message);
            $result = $stmt->execute();
            // print_r($stmt->error);
        }

        $conn->close();
        if (empty($result)) {
            $response = [
                "code" => 0,
                "message" =>  "Failed to connect at this moment.",
                "statusCode" => 500
            ];
            return $response;
        }

        $response = [
            "code" => 1,
            "message" => "",
            "statusCode" => 200
        ];
        return $response;
    }

    private function convertArrayToSQLInTypeQuery($array)
    {
        $SQLInTypeQuery = "";
        foreach ($array as $value) {
            $SQLInTypeQuery = $SQLInTypeQuery . "'" . $value . "', ";
        }
        return substr($SQLInTypeQuery, 0, -2);
    }
}
