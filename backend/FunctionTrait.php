<?php
date_default_timezone_set('Asia/Kolkata');
header("Content-Type: application/json; charset=UTF-8");

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
if (($_SERVER['REQUEST_METHOD'] ?? "") == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE, PATCH, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}



trait FunctionTrait
{
    use DatabaseTrait, RequestTrait, AuthTrait, ResponseTrait, OTPTrait, NotificationTrait;


    protected $validationSuccessStatusCode = 200;
    protected $validationFailureStatusCode = 400;


    private function setConfigVar()
    {
        $name = $this->allPostRequestValues["name"];
        $value = $this->allPostRequestValues["value"];
        $description = $this->allPostRequestValues["description"];

        $sql = "SELECT id FROM config WHERE name=?";
        $stmt = $this->runSQL($sql, [$name]);
        if (!$stmt) {
            return 0;
        }
        if ($stmt->num_rows == 0) {
            $sql = "INSERT INTO config(name, value, description)
            values(?, ?, ?)";
            $stmt = $this->runSQL($sql, [$name, $value, $description]);
        } else {
            $sql = "UPDATE config SET value=?, description=? WHERE name=?";
            $stmt = $this->runSQL($sql, [$value, $description, $name]);
        }
        return 1;
    }

    private function getConfigVar($name)
    {
        $sql = "SELECT value FROM config WHERE name=?";
        $stmt = $this->runSQL($sql, [$name]);
        if (!$stmt) {
            return "";
        }
        if ($stmt->num_rows == 0) {
            return "";
        }
        $stmt->bind_result($value);
        $stmt->fetch();

        return $value;
    }

    function generateRandomString($length)
    {
        $all = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random = '';
        for ($i = 0; $i < $length; $i++)
            $random = $random . $all[rand(0, strlen($all) - 1)];
        return $random;
    }

    function generateRandomStringAllCaps($length)
    {
        $all = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random = '';
        for ($i = 0; $i < $length; $i++)
            $random = $random . $all[rand(0, strlen($all) - 1)];
        return $random;
    }

    private function deleteFile($file)
    {
        if (!empty($file) && file_exists($file)) {
            unlink($file);
        }
    }

    function CallAPI($method, $url, $data = false)
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data && is_array($data)) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    function checkIfArrayHasDuplicateValues($array)
    {
        $dupe_array = array();
        foreach ($array as $val) {
            if (!empty($dupe_array[$val])) {
                return true;
            } else {
                $dupe_array[$val] = 1;
            }
        }
        return false;
    }


    function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }
        return $mixed;
    }

    function setTemplateVeriables(array $veriables, string &$template)
    {
        if (empty($template)) {
            return;
        }

        foreach ($veriables as $veriable => $value) {
            if (empty($value)) {
                $value = "";
            }
            $template = str_replace($veriable, $value, $template);
        }
    }

    function rrmdir($dir) { 
        if (is_dir($dir)) { 
          $objects = scandir($dir);
          foreach ($objects as $object) { 
            if ($object != "." && $object != "..") { 
              if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                $this->rrmdir($dir. DIRECTORY_SEPARATOR .$object);
              else
                unlink($dir. DIRECTORY_SEPARATOR .$object); 
            } 
          }
          rmdir($dir); 
        } 
      }
     
}
