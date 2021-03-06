<?php

namespace wcoding\batch16\finalproject\Model;

require_once('./model/Manager.php');

use Exception;

class UserManager extends Manager
{
    public function __construct($user = 0)
    {
        parent::__construct();
        $this->_user_id = $user;
    }

    public function signIn($email, $password, $rememberMe)
    {
        // rememberMe function
        $email = htmlspecialchars($email);
        $password = htmlspecialchars($password);

        $response = $this->_connection->query("SELECT email, password, dob, first_name, id, uid, profile_img FROM users WHERE email = '$email' AND is_active = 1");
        $userInfo = $response->fetch(\PDO::FETCH_ASSOC);
        $passwordHashed = $userInfo['password'];
        $response->closeCursor();

        $check = password_verify(htmlspecialchars($password), $passwordHashed);

        if ($check) {
            if ($rememberMe) {
                setcookie('email', $email, time()+365*24*3600);
            }
            // setting rememberMe cookie
            $_SESSION['firstName'] = $userInfo['first_name'];
            $_SESSION['email'] = $email;
            $_SESSION['uid'] = $userInfo['uid'];
            $_SESSION['profile_img'] = $userInfo['profile_img'];

            if ($userInfo['dob']) {
                // checking dob when signing in. dob is mandatory submission so
                // if it's in the database user has already created a profile.                
                header("Location:index.php?action=loggedIn");
            } else {
                header("Location:index.php?action=createProfile");
            }
        } else {
            header("Location:index.php?action=wrongPassword");
        }
    }
    public function checkSignIn($email, $password)
    {

        $email = htmlspecialchars($email);
        $password = htmlspecialchars($password);

        $response = $this->_connection->query("SELECT email, password, dob, first_name FROM users WHERE email = '$email'");
        $userInfo = $response->fetch(\PDO::FETCH_ASSOC);
        $passwordHashed = $userInfo['password'];
        $response->closeCursor();

        $check = password_verify($password, $passwordHashed);

        if ($check) {
            echo 1;
        } else {
            echo '';
        }
    }

    protected function createUID()
    {
        $uid = bin2hex(random_bytes(4));
        $isUnique = $this->_connection->query("SELECT * FROM users WHERE uid='$uid'")->fetch(\PDO::FETCH_ASSOC) ? false : true;
        while (!$isUnique) {
            $uid = bin2hex(random_bytes(4));
            $isUnique = $this->_connection->query("SELECT * FROM users WHERE uid='$uid'")->fetch(\PDO::FETCH_ASSOC) ? false : true;
        }
        return $uid;
    }

    public function signUp($firstName, $lastName, $email, $password)
    {
        $firstName = addslashes(htmlspecialchars(htmlentities(trim($firstName))));
        $lastName = addslashes(htmlspecialchars(htmlentities(trim($lastName))));
        $email = addslashes(htmlspecialchars(htmlentities(trim($email))));
        $password = password_hash(htmlspecialchars($password), PASSWORD_DEFAULT);
        $uid = $this->createUID();

        $response = $this->_connection->query("SELECT email, first_name, last_name FROM users WHERE email='$email'");
        if ($response->fetch(\PDO::FETCH_ASSOC)) {
            // header('Location:index.php');
        echo "1"; 
        } else {
            $response = $this->_connection->prepare("INSERT INTO users (password, email, first_name, last_name, uid) VALUES (:password, :email, :firstName, :lastName, :uid)");
            $response->bindParam("firstName", $firstName, \PDO::PARAM_STR);
            $response->bindParam("lastName", $lastName, \PDO::PARAM_STR);
            $response->bindParam("email", $email, \PDO::PARAM_STR);
            $response->bindParam("password", $password, \PDO::PARAM_STR);
            $response->bindParam("uid", $uid, \PDO::PARAM_STR);
            $response->execute();
            header('Location:index.php');
        }
    }

    public function googleOauth($credential)
    {
        $response = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $credential)[1]))));
        if ($response->aud != "864435133244-6p5l99hhn44afncpkpifoqsefdns9biv.apps.googleusercontent.com" or $response->azp != "864435133244-6p5l99hhn44afncpkpifoqsefdns9biv.apps.googleusercontent.com" or $response->iss != 'https://accounts.google.com') {
            throw (new Exception('Google Identification went wrong'));
        }
        $res = $this->_connection->query("SELECT email, dob, uid, profile_img FROM users WHERE email='$response->email'");
        $user = $res->fetch(\PDO::FETCH_ASSOC);
        $_SESSION['firstName'] = $response->given_name;
        $_SESSION['email'] = $response->email;
        // If user had signed in before 
        if ($user) {
            // If user has a profile they are redirected to main page
            if ($user['dob']) {
                $_SESSION['uid'] = $user['uid'];
                $_SESSION['profile_img'] = $user['profile_img'];
                header('Location:index.php');
            } 
            // If user has a profile they are redirected to createProfile page
            else {
                $_SESSION['uid'] = $user['uid'];
                header('Location:index.php?action=createProfile');
            }
        // Else they are redirected to createProfile page
        } else {
            $uid = $this->createUID();
            $_SESSION['uid'] = $uid;
            $this->_connection->exec("INSERT INTO users (email, first_name, last_name, uid) VALUES ('$response->email','$response->given_name','$response->family_name', '$uid')");
            header('Location:index.php?action=createProfile');
        }
    }

    public function validateProfile()
    {
        //Check image size and file type
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($_FILES['uploadFile']['name'], PATHINFO_EXTENSION));
        if ($_FILES['uploadFile']['name']) {
            if ($_FILES['uploadFile']['size'] > 500000 or ($imageFileType != "JPG" and $imageFileType != "jpg" and $imageFileType != "png" and $imageFileType != "JPEG" and $imageFileType != "jpeg" and $imageFileType != "webp")) {
                $uploadOk = 0;
            }   
        }

        // Check phone number
        $phones = str_replace('-', '', str_replace(' ', '', $_REQUEST['phoneNum']));
        !empty($phones) and preg_match("/^\+?[0-9]{7,14}$/", $phones) ? $phoneNum = $phones : $phoneNum = null;

        // Check birthday
        $days30 = array(4, 6, 9, 11);
        $days31 = array(1, 3, 5, 7, 8, 10, 12);

        // Check year
        !empty($_REQUEST['year']) and $_REQUEST['year'] >= intval(date('Y')) - 120 and $_REQUEST['year'] <= intval(date('Y')) ? $year = ($_REQUEST['year']) : $year = null;

        //Check month
        !empty($_REQUEST['month']) and (preg_match("/[1-9]|1[0-2]/", $_REQUEST['month'])) ? $month = $_REQUEST['month'] : $month = null;

        // Check day
        if (!empty($_REQUEST['day']) and $month === 2) {
            if (($year % 100 === 0 and $year % 400 === 0) or ($year % 100 !== 0 and $year % 4 === 0)) {
                if ($_REQUEST['day'] >= 1 and $_REQUEST['day'] <= 29) {
                    $day = $_REQUEST['day'];
                }
            } else if ($_REQUEST['day'] >= 1 and $_REQUEST['day'] <= 28) {
                $day = $_REQUEST['day'];
            }
        } else if (!empty($_REQUEST['day']) and in_array($month, $days30)) {
            if ($_REQUEST['day'] >= 1 and $_REQUEST['day'] <= 30) {
                $day = $_REQUEST['day'];
            }
        } else if (!empty($_REQUEST['day']) and in_array($month, $days31)) {
            if ($_REQUEST['day'] >= 1 and $_REQUEST['day'] <= 31) {
                $day = $_REQUEST['day'];
            }
        } else {
            $day = null;
        }

        $month < 10 ? $month = "0$month" : $month = "$month";
        $dob = $year . '-' . $month . '-' . $day;

        // Check gender
        !empty($_REQUEST['gender']) and ($_REQUEST['gender'] === 'M' or $_REQUEST['gender'] === 'F' or $_REQUEST['gender'] === 'NB') ? $gender = $_REQUEST['gender'] : $gender = null;

        // Check languages (add languages to array as necessary)
        $languages = array(
            'Cantonese' => 'HK', 'Chinese(Mandarin)' => 'ZH', 'Dutch' => 'NL', 'English' => 'EN',
            'French' => 'FR', 'German' => 'DE', 'Hindi' => 'HI', 'Indonesian' => 'IN', 'Italian' => 'IT', 'Japanese' => 'JA',
            'Korean' => 'KO', 'Vietnamese' => 'VI', 'Portuguese' => 'PT', 'Russian' => 'RU', 'Spanish' => 'ES'
        );
        
        $userLang = explode(',', $_REQUEST['userLang']);

        !empty($userLang) and array_diff($userLang, $languages) === array() ? $language = implode(',', array_unique($userLang)) : $language = null;

        // Check bio
        !empty($_REQUEST['bio']) ? $bio = $_REQUEST['bio'] : $bio = null;

        //Check form
        if ($uploadOk === 1 and $phoneNum and $dob and $gender and $language and $bio) {
            $this->newProfile();
        } else {
            header('Location:index.php?action=createProfile&createAccount=error');
        }
    }

    // creates new user profile that will be inserted into users table
    public function newProfile()
    {
        $specialChar = array(' ', '-');
        $phoneNum = strval(strip_tags(str_replace($specialChar, '', $_REQUEST['phoneNum'])));
        $dob = strip_tags($_POST['year']) . '-' . strip_tags($_POST['month']) . '-' . strip_tags($_POST['day']);
        $gender = strip_tags($_POST['gender']);
        $language = strip_tags($_REQUEST['userLang']);
        $bio = strip_tags($_POST['bio']);
        if (!empty($_FILES["uploadFile"]["name"])) {
            // Get file info 
            $fileName = pathinfo($_FILES["uploadFile"]["name"]);
            $extension  = $fileName['extension'];
            $fileLocation = $_FILES["uploadFile"]["tmp_name"];
            $bytes = bin2hex(random_bytes(16)); // generates secure pseudo random bytes and bin2hex converts to hexadecimal string
            $imgName = $bytes . "." . $extension;
            move_uploaded_file($fileLocation, "./profile_images/" . $imgName);

        } else {
            $imgName = null;
        }
        $req = $this->_connection->prepare("UPDATE users SET phone_number=:phoneNum, dob=:dob, gender=:gender, languages=:lang, bio=:bio, profile_img=:userImg WHERE email='{$_SESSION['email']}'");
        $req->bindParam('phoneNum', $phoneNum, \PDO::PARAM_STR);
        $req->bindParam('dob', $dob, \PDO::PARAM_STR);
        $req->bindParam('gender', $gender, \PDO::PARAM_STR);
        $req->bindParam('lang', $language, \PDO::PARAM_STR);
        $req->bindParam('bio', $bio, \PDO::PARAM_STR);
        $req->bindParam('userImg', $imgName, \PDO::PARAM_STR);
        $req->execute();
        $_SESSION['profile_img'] = $imgName;
        header('Location:index.php');
    }

    public function signOut()
    {
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location:index.php?action=loggedOut');
    }


    public function getUserInfo()
    {
        $req = $this->_connection->prepare('SELECT * FROM users WHERE uid = ? AND is_active = 1');
        $req->execute(array($this->_user_id));
        $user = $req->fetch(\PDO::FETCH_ASSOC);
        $req->closeCursor();
        $languages = explode(',', $user['languages'] ?? "");
        foreach ($languages as &$language) {
            $language = $this->getLangauges($language);
        }
        $user['languages'] = $languages;
        return $user;
    }
    
    public function viewUserData()
    {
        //view the information of the current profile//
        $req = $this->_connection->prepare("SELECT * FROM users WHERE uid ='{$_SESSION['uid']}' AND is_active = 1");
        $req->execute();
        $data = $req->fetch(\PDO::FETCH_ASSOC);
        $languages = explode(',', $data['languages']);
        $data['languages'] = $languages;

        return $data;
    }

    public function updateUserData($data)
    {
        
        $uid = $data['uid'];
        $firstName = $data['first_name'];
        $lastName = $data['last_name'];
        $email = $data['email'];
        $password = $data['password'];
        $dob = $data['dob'];
        $gender = $data['gender'];
        $dateCreated = $data['date_created'];
        $phoneNumber = $data['phone_number'];
        $bio = $data['bio'];
        $profileImgLocation = $data['profile_img'];

        // ============Update profile photo ========//
        // Get file info
        if (!empty($_FILES["uploadFile"]["name"])) {
            $fileName = pathinfo($_FILES["uploadFile"]["name"]);
            $extension  = $fileName['extension'];
            $fileLocation = $_FILES["uploadFile"]["tmp_name"];
            $bytes = bin2hex(random_bytes(16)); // generates secure pseudo random bytes and bin2hex converts to hexadecimal string
            $imgName = $bytes . "." . $extension;
            // $folder = "./profile_images/" . $imgName;
            move_uploaded_file($fileLocation, "./profile_images/" . $imgName);
            $_SESSION['profile_img'] = $imgName;
        } 
        else if ($profileImgLocation){
            $imgName = $profileImgLocation;
        } else{
            $imgName = null;
        }    

        
        // update is_active status from 1 -> 0 =====//
        $req2 = $this->_connection->prepare("UPDATE users SET is_active = 0 WHERE uid ='{$_SESSION['uid']}' ");
        $req2->execute();
     
        //=============================================//
        //================Backend checking ============//
        //=============================================//

        // Check phone number formating
        // phone number cannot be null
        !empty($_REQUEST['phoneNumber']) and preg_match("/^\+?[0-9]{7,14}$/", $_REQUEST['phoneNumber']) ? $phoneNumber = ($_REQUEST['phoneNumber']) : $phoneNumber = null;
        
        // Check languages (add languages to array as necessary)
        $languages = array(
            'Cantonese' => 'HK', 'Chinese(Mandarin)' => 'ZH', 'Dutch' => 'NL', 'English' => 'EN',
            'French' => 'FR', 'German' => 'DE', 'Hindi' => 'HI', 'Indonesian' => 'IN', 'Italian' => 'IT', 'Japanese' => 'JA',
            'Korean' => 'KO', 'Vietnamese' => 'VI', 'Portuguese' => 'PT', 'Russian' => 'RU', 'Spanish' => 'ES'
        );
        $userLang = explode(',', $_REQUEST['userLang'] ?? "");
        !empty($userLang) and array_diff($userLang, $languages) === array() ? $language = implode(',', array_unique($userLang)) : $language = null;

        // Check bio
        !empty($_REQUEST['bio']) ? $bio = $_REQUEST['bio'] : $bio = null;

        // create a new row with the inherited and modified informaiton //
        $status = 1;
        if($phoneNumber != null){
            
            $reqInsert = $this->_connection->prepare("INSERT INTO users (uid, first_name, last_name, email, password, dob, gender, languages, bio, phone_number, profile_img, is_active, date_created)
            VALUES ( :inuid, :infirst, :inlast, :inemail, :inpassword, :indob, :ingender, :inlanguages, :inbio, :inphoneNumber, :inprofileImg, :inactiveStatus, '$dateCreated') ");

            // insert modified content
            $reqInsert->bindParam("inlanguages", $language, \PDO::PARAM_STR);
            $reqInsert->bindParam("inphoneNumber", $phoneNumber, \PDO::PARAM_STR);
            $reqInsert->bindParam("inbio", $bio, \PDO::PARAM_STR);
            $reqInsert->bindParam("inactiveStatus", $status, \PDO::PARAM_INT);
            $reqInsert->bindParam("inprofileImg", $imgName, \PDO::PARAM_STR);
            
            // insert inherited from the previous data
            $reqInsert->bindParam("inuid", $uid, \PDO::PARAM_STR);
            $reqInsert->bindParam("infirst", $firstName, \PDO::PARAM_STR);
            $reqInsert->bindParam("inlast", $lastName, \PDO::PARAM_STR);
            $reqInsert->bindParam("inemail", $email, \PDO::PARAM_STR);
            $reqInsert->bindParam("inpassword", $password, \PDO::PARAM_STR);
            $reqInsert->bindParam("indob", $dob, \PDO::PARAM_STR);
            $reqInsert->bindParam("ingender", $gender, \PDO::PARAM_STR);
    
            $reqInsert->execute();
        }
        else{
            throw(new Exception('Phone cannot be Null !'));
        }
        
    }

    public function updateLastActive()
    {
        $this->_connection->exec("UPDATE users SET last_online=NOW() WHERE email='{$_SESSION['email']}'");
    }

    public function cancelReservation($reservationNum) {
        $req = $this->_connection->prepare("UPDATE reservations SET is_active = 0 WHERE reservation_num = :reservationNum AND user_uid = '{$_SESSION ['uid']}'");
            $req->bindParam("reservationNum", $reservationNum, \PDO::PARAM_STR);
            $req->execute();
    }

    public function getReservations() {
        if ($this->_user_id == $_SESSION['uid']) {
            $req = $this->_connection->query("SELECT r.reservation_num, r.user_uid, r.property_id, r.start_date, r.end_date, r.total_payment_won, r.is_active, p.id, p.post_title, p.country, p.province_state, p.city, p.zipcode, p.address1, p.address2, pi.property_id AS pi_id, pi.img_url AS pi_img
            FROM reservations r 
            LEFT JOIN properties p
            ON r.property_id = p.id
            LEFT JOIN property_imgs pi
            ON p.id = pi.property_id
            WHERE r.user_uid='{$_SESSION['uid']}' AND r.is_active=1
            GROUP BY r.reservation_num");
            $reservations = $req->fetchAll(\PDO::FETCH_ASSOC);
        } else $reservations = [];
        
        foreach($reservations as &$reservation) {
            $reservation['country'] = $this::COUNTRIES['KR'];
            $reservation['province_state'] = $this::PROVINCES['KR'][$reservation['province_state']];
            $reservation['city'] = !empty($this::CITIES[$reservation['province_state']][$reservation['city']]) ? $this::CITIES[$reservation['province_state']][$reservation['city']] : '';
        }
        return $reservations;
    }
    
    public function getReservationCost()
    {
        $req = $this->_connection->prepare("SELECT * FROM properties WHERE property_id ='{$_SESSION['propId']}' AND is_active = 1");
    }

    public function reservations()
    {
       // calculate the number of days
       $req = $this->_connection->prepare("SELECT monthly_price_won FROM properties WHERE id =:propertyId AND is_active = 1");
       $propertyId = addslashes(htmlspecialchars(htmlentities(trim((int)$_REQUEST['propId']))));
       $req->bindParam(":propertyId", $propertyId, \PDO::PARAM_INT);
       $req->execute();
       $monthlyPrice = $req->fetch(\PDO::FETCH_ASSOC)['monthly_price_won'];
    
       $date1 = $_REQUEST['startDate'];
       $date2 = $_REQUEST['endDate'];
       $diff = strtotime($date2) - strtotime($date1);
       $numDays = abs(round($diff / 86400));

       // calculate the total price
       $total_pay = $monthlyPrice * $numDays / 30;


       $req = $this->_connection->prepare("INSERT INTO reservations (property_id, reservation_num, start_date, end_date, cardholder, credit_card_num, cvv, exp_month, exp_year, user_uid, total_payment_won, transaction_complete, is_active) 
       VALUES (:propertyId, :reservation_num, :startDate, :endDate, :cardOwner, :creditCardNum, :cvv, :expMonth, :expYear, :uid, :totalPay, :transactionStatus, :activeStatus)");
       
       $reservation_num = bin2hex(random_bytes(3));
       $propertyId = addslashes(htmlspecialchars(htmlentities(trim((int)$_REQUEST['propId']))));
       $start_date =  addslashes(htmlspecialchars(htmlentities(trim($_REQUEST['startDate']))));
       $end_date =  addslashes(htmlspecialchars(htmlentities(trim($_REQUEST['endDate']))));
       $cardholder = addslashes(htmlspecialchars(htmlentities(trim($_REQUEST['owner']))));
       $credit_card_num = password_hash(str_replace('-','',$_POST['cardNumber']), PASSWORD_DEFAULT);
       $cvv = password_hash($_REQUEST['cvv'], PASSWORD_DEFAULT);       
       $exp_month = addslashes(htmlspecialchars(htmlentities(trim($_REQUEST['month']))));
       $exp_year = addslashes(htmlspecialchars(htmlentities(trim($_REQUEST['year']))));
       $uid = addslashes(htmlspecialchars(htmlentities(trim($_SESSION['uid']))));
       $transaction = 1;
       $activeStatus = 1;
        
        
       $req->bindParam(":propertyId", $propertyId, \PDO::PARAM_INT);
       $req->bindParam("reservation_num", $reservation_num, \PDO::PARAM_STR);
       $req->bindParam("startDate", $start_date, \PDO::PARAM_STR);
       $req->bindParam("endDate", $end_date, \PDO::PARAM_STR);
       $req->bindParam("cardOwner", $cardholder, \PDO::PARAM_STR);
       $req->bindParam("creditCardNum", $credit_card_num, \PDO::PARAM_STR);
       $req->bindParam("cvv", $cvv, \PDO::PARAM_STR);
       $req->bindParam("expMonth", $exp_month, \PDO::PARAM_INT);
       $req->bindParam("expYear", $exp_year, \PDO::PARAM_INT);
       $req->bindParam("uid", $uid, \PDO::PARAM_STR);
       $req->bindParam("totalPay", $total_pay, \PDO::PARAM_INT);
       $req->bindParam("transactionStatus", $transaction, \PDO::PARAM_INT);
       $req->bindParam("activeStatus", $activeStatus, \PDO::PARAM_INT);

       $req->execute();
       header('location:index.php?action=reserveComplete');
   
    
    }

}
