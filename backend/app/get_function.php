<?php
class GetFunction
{
    use FunctionTrait;

    function __construct()
    {
    }
    function __destruct()
    {
    }
    
    function setReferralId() {
        $sql = "SELECT id FROM user WHERE referral_id IS NULL";
        $stmt = $this->runSQL($sql);
        if (!$stmt) {
            $response = ["error" => "Failed to connect at this moment."];
            return $this->sendResponse($response, 500);
        }
        if ($stmt->num_rows == 0) {
            $response = ["error" => "No user found."];
            return $this->sendResponse($response, 404);
        }
        $stmt->bind_result($user_id);
        while($stmt->fetch()){
            $sql2 = "UPDATE user SET referral_id='".$this->generateReferralId()."' WHERE id='$user_id'";
            $stmt2 = $this->runSQL($sql2);
            if (!$stmt2) {
                $response = ["error" => "Failed to connect at this moment."];
                return $this->sendResponse($response, 500);
            }

        }
    }
}
