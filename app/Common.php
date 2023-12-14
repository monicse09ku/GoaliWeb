<?php

namespace App;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Session;
use Auth;
use DB;

/**
 * Class Common, this class is to use project common functions
 *
 * @package App
 */
class Common
{
    /*
     * Site information section.
     * This section data need to be updated
     * based on site
     * */
    const SITE_TITLE = 'Camelia';
    const DOMAIN_NAME = 'camelia.com';
    const SITE_URL = 'https://camelia.com';
    //const SITE_URL = 'http://127.0.0.1:8000';
    const FROM_EMAIL = 'camelia@gmail.com';
    const FROM_NAME = 'Camelia';
    const ADMIN_EMAIL = 'info@camelia.com';
    /*
     * Site information section ended
     * */

    const OAUTH_TOKEN = 'g123456';

    const VALID_IMAGE_EXTENSIONS = ['jpg','JPG','jpeg','JPEG','png','PNG'];
    const VALID_FILE_EXTENSIONS = ['jpg','JPG','jpeg','JPEG','png','PNG','svg','doc','docx','odt','xls','xlsx','ods','pdf','mp3','mp4','wav','zip','tar','gzip'];
    const VALID_XL_FILE_EXTENSIONS = ['xls','xlsx','ods'];

    /*
    * Get two Date difference in days
    * Return number of days (integer)
    */
    public static function getDateDiffDays($date1, $date2)
    {
        try{
            $timeDiff = strtotime($date1) - strtotime($date2);
            $daysDiff = $timeDiff/86400;  // 86400 seconds in one day
            return $daysDiff;
        }catch (\Exception $exception){
            echo $exception->getMessage();
        }
    }

    /*
    * Get Month From Date
    * Return month (integer)
    */
    public static function getMonth($time)
    {
        try{
            $date = new Carbon( $time );
            return $date->month;
        }catch (\Exception $exception){
            echo $exception->getMessage();
        }
    }

    /*
    * Get Year From Date
    * Return year (integer)
    */
    public static function getYear($time)
    {
        try{
            $date = new Carbon( $time );
            return $date->year;
        }catch (\Exception $exception){
            echo $exception->getMessage();
        }
    }

    /*
    * Get all dates from given date range
    * Return array of dates
    */
    public static function getDatesFromRange($first, $last, $step = '+1 day', $output_format = 'm/d/Y' ) {

        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while( $current <= $last ) {

            $dates[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }

    /*
     * Generate random number with length
     * Return a random number (integer)
     * */
    public static function generaterandomNumber($length){
        $min = pow(10,$length);
        $max = pow(10,$length+1);
        $value = rand($min, $max);
        return $value;
    }

    /*
     * Generate random string
     * Return a random string (varchar)
     * */
    public static function generaterandomString($length){
        $string = substr(md5(uniqid(mt_rand(), true)), 0, $length);
        return $string;
    }

    /*
     * Generate unique number
     * Return a unique number (integer)
     * */
    public static function generateUniqueNumber($number){
        $number_length = strlen($number);
        $leading_zero = 9-$number_length;
        for($i=$leading_zero; $i>0; $i--){
            $number = '0'.$number;
        }

        $insertion = "-";
        $index1 = 6;
        $index2 = 3;
        $number = substr_replace($number, $insertion, $index1, 0);
        $number = substr_replace($number, $insertion, $index2, 0);

        return $number;
    }

    /*
     * Generate unique number
     * Return a unique number (integer)
     * */
    public static function addLeadingZero($number,$length){
        $number = sprintf('%0'.$length.'d', $number);
        return $number;
    }

    /*
     * Check if uploaded image has valid image file extension
     * Return 0 or 1
     * */
    public static function isValidImageExtension($image_file){
        $extension = $image_file->getClientOriginalExtension();
        if(in_array($extension, Common::VALID_IMAGE_EXTENSIONS)){
            return 1;
        }
        else{
            return 0;
        }
    }

    /*
     * Check if uploaded file has valid excel file extension
     * Return 0 or 1
     * */
    public static function isValidXlExtension($image_file){
        $extension = $image_file->getClientOriginalExtension();
        if(in_array($extension, Common::VALID_XL_FILE_EXTENSIONS)){
            return 1;
        }
        else{
            return 0;
        }
    }

    /*
     * Format a phone number
     * Return formatted phone number (varchar)
     * */
    public static function formatPhoneNumber($phone){
        $phone = str_replace(" ","",$phone);
        $phone = str_replace("-","",$phone);
        $phone = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $phone);
        return $phone;
    }

}//End
