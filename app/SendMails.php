<?php

namespace App;
use Illuminate\Support\Facades\Mail;
use DOMDocument;
use App\Models\Application;
use App\ErrorLog;
use App\Common;
use App\MailWithAws;
use DB;
use Config;
use Artisan;
use View;
use Image;

/**
 * Class SendMails, this class is to send various types of mails
 *
 * @package App
 */
class SendMails
{
    const FROM_ADDRESS = Common::FROM_EMAIL;
    const FROM_NAME = Common::FROM_NAME;

    public static function sendMail(array $data, $view)
    {
        try{
            $from_email = self::FROM_ADDRESS;
            $from_name = self::FROM_NAME;
            if(isset($data['email'])) {
                $to_email = $data['email'];
            }
            else{
                $to_email = SendMails::FROM_ADDRESS;
            }
            if(isset($data['email_cc'])) {
                $cc_email = $data['email_cc'];
            }
            else{
                $cc_email = [];
            }
            if(isset($data['email_bcc'])) {
                $bcc_email = $data['email_bcc'];
            }
            else{
                $bcc_email = [];
            }
            if(isset($data['attachments'])) {
                $attachments = $data['attachments'];
            }
            else{
                $attachments = [];
            }

            if(isset($data['subject'])) {
                $subject = $data['subject'];
            }
            else{
                $subject = "Welcome To ".SendMails::FROM_NAME;
            }


            Mail::send($view, $data, function ($message) use ($to_email,$from_email,$from_name,$cc_email,$bcc_email,$attachments,$subject) {

                // dd($from_email);
                $message->from($from_email, $from_name);

                if(count($to_email)!=0){
                    $message->to($to_email);
                }
                if(count($cc_email)!=0){
                    $message->cc($cc_email);
                }
                if(count($bcc_email)!=0){
                    $message->bcc($bcc_email);
                }
                if(count($attachments)!=0){
                    foreach ($attachments as $attach) {
                        if($attach != ''){
                            $message->attach($attach);
                        }
                    }
                }
                $message->subject($subject);
            });

            return 'ok';
        }catch (\Exception $exception){
            return $exception->getMessage();
        }
    }


    /*public static function sendMail(array $data, $view, $application_id='')
    {
        try{
            if(isset($data['email'])) {
                $to_email = $data['email'];
            }
            else{
                $to_email = SendMails::FROM_ADDRESS;
            }
            if(isset($data['email_cc'])) {
                $cc_email = $data['email_cc'];
            }
            else{
                $cc_email = [];
            }
            if(isset($data['email_bcc'])) {
                $bcc_email = $data['email_bcc'];
            }
            else{
                $bcc_email = [];
            }
            if(isset($data['attachments'])) {
                $attachments = $data['attachments'];
            }
            else{
                $attachments = [];
            }
            if(isset($data['from_email'])){
                $from_email = $data['from_email'];
            }
            else{
                $from_email = Common::FROM_EMAIL;
            }
            if(isset($data['from_name'])){
                $from_name = $data['from_name'];
            }
            else{
                $from_name = Common::SITE_TITLE;
            }
            if(isset($data['subject'])) {
                $subject = $data['subject'];
            }
            else{
                $subject = "Welcome To ".SendMails::FROM_NAME;
            }

            Mail::send($view, $data, function ($message) use ($to_email,$from_email,$from_name,$cc_email,$bcc_email,$attachments,$subject) {

               // dd($from_email);
                $message->from($from_email, $from_name);

                if(count($to_email)!=0){
                    $message->to($to_email);
                }
                if(count($cc_email)!=0){
                    $message->cc($cc_email);
                }
                if(count($bcc_email)!=0){
                    $message->bcc($bcc_email);
                }
                if(count($attachments)!=0){
                    foreach ($attachments as $attach) {
                        if($attach != ''){
                            $message->attach($attach);
                        }
                    }
                }
                $message->subject($subject);
            });

            return 'ok';
        }catch (\Exception $exception){
            //self::smtpWarningEmailToAdmin($community_id);
            return $exception->getMessage();
        }
    }*/

    public static function sendErrorMail($message, $view=NULL, $controller, $method, $line_number=NULL, $file_path=NULL, $object=NULL,$type=NULL, $argument=NULL, $email=NULL)
    {
        // Temporarily error mail sending made off. Because sendgrid has lots of error mail.
        $url = $_SERVER['HTTP_HOST'];
        $c_url = $_SERVER['REQUEST_URI'];
        $current_url = explode('?',$c_url);
        $current_url = str_replace('http://', '', $current_url[0]);
        $current_url = str_replace('https://', '', $current_url);
        $full_domain = explode('/',$current_url);
        if($full_domain[0]=='localhost'){
            $prefix = '';
        }
        else{
            $prefix = $full_domain[1];
        }
        $domain = $url;


        $data = array(
            'exception_message' => $message,
            'method_name'       => $method,
            'line_number'       => $line_number,
            'file_path'         => $file_path,
            'class'             => $controller,
            'object'            => $object,
            'type'              => $type,
            'argument'          => $argument,
            'email'             => $email,
            'client'            => Common::SITE_TITLE,
            'subject'           => 'Error Notification',
            'prefix'            => $prefix,
            'domain'            => $domain
        );
        $view = 'emails.error_exception_email';
        $email = 'muhin.diu092@gmail.com';
        $email = array($email, "shahinkazi1@gmail.com");
        $subject = $data['subject'];
        Mail::send($view, $data, function ($message) use ($email, $subject) {
            $message->from(SendMails::FROM_ADDRESS, SendMails::FROM_NAME);

            //$message->to('dcarta@gmail.com');
            $message->to('muhin.diu092@gmail.com');
            //$message->cc('shahinkazi1@gmail.com');
            //$message->bcc('mail2technerd@gmail.com');
            $message->subject($subject);
        });

        /*Save error to database*/
        $screenshot = '';
        $page_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $result = Common::saveErrorLog($method,$line_number,$file_path,$message,$object,$type,$screenshot,$page_url,$argument,$prefix,$domain);
        return $result;
    }

    /*
     * Convert base64 image data to plain image inside message string
     * */
    public static function imageSendToMail($submitted_text)
    {
        // create new DOMDocument
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $submitted_text = self::removeBS($submitted_text);

        $dom->loadHtml($submitted_text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $images = $dom->getElementsByTagName('img');

        // foreach <img> in the submited message
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            // if the img source is 'data-url'
            if (preg_match('/data:image/', $src)) {
                // get the mimetype
                preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                $mimetype = $groups['mime'];
                // Generating a random filename
                $filename = uniqid();
                $image_dir = public_path("email_image");

                if (!file_exists($image_dir)) {
                    mkdir($image_dir, 0755, true);
                }
                $filepath = "/email_image/$filename.$mimetype";
                // @see http://image.intervention.io/api/
                $image = Image::make($src)
                    // resize if required
                    ->resize(300, 200)
                    ->encode($mimetype, 100)  // encode file to the specified mimetype
                    ->save(public_path($filepath));
                $new_src = asset('/public'.$filepath);
                $img->removeAttribute('src');
                $img->setAttribute('src', $new_src);
            }

        }

        $submitted_text = $dom->saveHTML();
        $submitted_text = self::removeBS($submitted_text);
        return $submitted_text;
    }

    public static function removeBS($string){
        $string = str_replace("Â", "", $string);
        $string = str_replace("â€™", "'", $string);
        $string = str_replace("’", "'", $string);
        $string = str_replace('“', '"', $string);
        $string = str_replace('”', '"', $string);
        $string = str_replace("â€", "'", $string);
        $string = str_replace("â", "'", $string);
        $string = str_replace("â€œ", '"', $string);
        $string = str_replace('â€“', '-', $string);
        $string = str_replace('â€', '"', $string);
        $string = utf8_decode($string);
        return $string;
    }


}
