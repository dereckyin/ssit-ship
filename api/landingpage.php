<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// files needed to connect to database
include_once 'config/database.php';
include_once 'objects/contact_us.php';
include_once 'config/conf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// instantiate product object
$contact_us = new ContactUs($db);
 
// get posted data
$data = json_decode(file_get_contents("php://input"));
 
// set product property values
$contact_us->gender = $data->gender;
$contact_us->customer = $data->customer;
$contact_us->emailinfo = $data->emailinfo;
$contact_us->telinfo = $data->telinfo;
$contact_us->number = $data->number;
$contact_us->way = $data->way;
 
// create the user
if(
    !empty($contact_us->customer) &&
    $contact_us->create()
){
 
    // set response code
    http_response_code(200);

    sendMail($data->gender, $data->customer, $data->emailinfo, $data->telinfo, $data->number, $data->way);
 
    // display message: user was created
    echo json_encode(array("message" => "User was created."));
}
 
// message if unable to create user
else{
 
    // set response code
    http_response_code(400);
 
    // display message: unable to create user
    echo json_encode(array("message" => "Unable to create user."));
}


function sendMail($gender, $customer,  $emailinfo, $telinfo, $number, $way) {
    $conf = new Conf();
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Mailer = "smtp";
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->SMTPDebug  = false;
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = "ssl";
    $mail->Port       = 465;
    $mail->SMTPKeepAlive = true;
    $mail->Host       = $conf::$mail_Host;
    $mail->Username   = $conf::$mail_Username;
    $mail->Password   = $conf::$mail_Password;
    $mail->do_debug   = 0;

    $tz_object = new DateTimeZone("Asia/Taipei");
    $datetime = new DateTime();
    $datetime->setTimezone($tz_object);

    $mail->IsHTML(true);
    $mail->AddAddress("jyf_lu@hotmail.com", "jyf_lu");
    $mail->AddAddress("servictoryshipment@gmail.com", "JYF System");

    if($way == 'air')
        $mail->AddAddress("kuan@feliix.com", "Kuan");
    
    $mail->SetFrom("servictoryshipment@gmail.com", "JYF System");
    $mail->AddReplyTo("servictoryshipment@gmail.com", "JYF System");

    $mail->Subject = "客戶聯絡資訊(Customer Contact) from 盛盛國際有限公司";
    $content = "<p>稱謂(Male/Female)：" . $gender . "</p>";
    $content = $content . "<p>姓名(Name)：" . $customer . "</p>";
    $content = $content . "<p>貨運方式(Ship Method)：" . ($way == 'air' ? 'air' : 'sea'). "</p>";
    $content = $content . "<p>貨品件數(Piece of Goods)：" . $number . "</p>";
    $content = $content . "<p>電子信箱(Email)：" . $emailinfo . "</p>";
    $content = $content . "<p>連絡電話(Contact Number)：" . $telinfo . "</p>";
    $content = $content . "<p>登記日期(Submitting Time)：" . $datetime->format('Y\-m\-d\ h:i:s') . "</p>";
    

    $mail->MsgHTML($content);
    if(!$mail->Send()) {
       // echo "Error while sending Email.";
       // var_dump($mail);
    } else {
       // echo "Email sent successfully";
    }
}
?>
