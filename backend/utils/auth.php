<?php
trait AuthTrait
{
    function encryptAuthString($string, $old_timestamp = 0)
    {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'ASfas45gAjdf721spodADFXCZB3867safWQ';
        $secret_iv = 'sdf123ASDcsad334Asd21A';

        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $data["data"] = $string;
        $data["timestamp"] = empty($old_timestamp) ? time() : $old_timestamp;
        $output = openssl_encrypt(json_encode($data), $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);

        return $output;
    }

    function verifyAuthToken(string $token)
    {
        $data = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'ASfas45gAjdf721spodADFXCZB3867safWQ';
        $secret_iv = 'sdf123ASDcsad334Asd21A';

        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $data = openssl_decrypt(base64_decode($token), $encrypt_method, $key, 0, $iv);
        if (!$data) {
            $response = [
                "code" => 0,
                "message" => "Invalid Token.",
                "statusCode" => 403
            ];
            return $response;
        }
        $response = [
            "code" => 1,
            "message" => json_decode($data),
            "statusCode" => 200
        ];
        return $response;
    }

    function getBearerToken()
    {
        $headers = false;

        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return false;
    }

    function getDecryptedAuthToken()
    {
        $encryptedToken = $this->getBearerToken();

        $decryptedResponse =  $this->verifyAuthToken($encryptedToken);
        if (isset($decryptedResponse["code"]) && $decryptedResponse["code"] != 1) {
            return $decryptedResponse;
        }
        $decryptedToken = $decryptedResponse["message"] ?? json_decode("{}");
        $decryptedToken = $decryptedToken->data ?? "";
        $response = [
            "code" => 1,
            "message" => $decryptedToken,
            "statusCode" => 200,
        ];
        return $response;
    }

    private function googleAuthInitFunction()
    {
        require_once(__DIR__ . "/../PHPLibrary/googleOAuth/autoload.php");

        $google_client = new Google_Client();
        $google_client->setClientId(GOOGLE_AUTH_CLIENT_ID);
        $google_client->setClientSecret(GOOGLE_AUTH_CLIENT_SECRET);
        // $google_client->setRedirectUri(GOOGLE_AUTH_REDIRECT_URI);
        $google_client->addScope('email');
        $google_client->addScope('profile');
        return $google_client;
    }

    function getGoogleAuthUrl($redirectURL)
    {
        $google_client = $this->googleAuthInitFunction();
        $google_client->setRedirectUri($redirectURL);
        $url = $google_client->createAuthUrl();
        $response = ["authURL" => $url];
        return $this->sendResponse($response, 200);
    }
}
