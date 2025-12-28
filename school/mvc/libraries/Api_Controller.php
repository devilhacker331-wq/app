<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Api_Controller extends REST_Controller
{
    public $load;
    public $session;
    public $studentrelation_m;
    public $setting_m;
    public $db;
    public $usertype_m;
    public $permission_m;
    public $uri;
    public $holiday_m;
    /*
    | -----------------------------------------------------
    | PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
    | -----------------------------------------------------
    | AUTHOR:			INILABS TEAM
    | -----------------------------------------------------
    | EMAIL:			info@inilabs.net
    | -----------------------------------------------------
    | COPYRIGHT:		RESERVED BY INILABS IT
    | -----------------------------------------------------
    | WEBSITE:			http://iNilabs.net
    | -----------------------------------------------------
    */

    public $data = [];
    protected $retdata = [];
    protected $_REST_Controller;

    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('setting_m');
        $this->load->model("site_m");
        $this->load->model('site_m');
        $this->load->model('holiday_m');
        $this->load->model('schoolyear_m');
        $this->load->model('usertype_m');
        $this->load->model('permission_m');

        if(is_array($this->tokeChecking()) && !isset($this->tokeChecking()['userdata'])) {
            $this->response([
                'status'  => false,
                'message' => 'Invalid token'
            ], self::HTTP_UNAUTHORIZED);
        }

        $this->data["siteinfos"]            = $this->site_m->get_site();
        $schoolyearID                       = $this->data['siteinfos']->school_year;
        $this->data['schoolyearobj']        = $this->schoolyear_m->get_obj_schoolyear($schoolyearID);
        $this->data['schoolyearsessionobj'] = $this->schoolyear_m->get_obj_schoolyear($this->session->userdata('defaultschoolyearID'));

        if($this->session->userdata('usertypeID') == 3) {
            $this->load->model('studentrelation_m');
            $student = $this->studentrelation_m->get_single_student([
                'srstudentID'    => $this->session->userdata('loginuserID'),
                'srschoolyearID' => $this->session->userdata('defaultschoolyearID')
            ]);
            $this->data['myclass'] = customCompute($student) ? $student->srclassesID : 0;
        } else {
            $this->data['myclass'] = 0;
        }

        $this->data['permission'] = $this->session->userdata('master_permission_set');
        $this->data["language"]   = $this->data["siteinfos"]->language;

        $this->permissionControl();
    }

    protected function tokeChecking()
    {
        $token       = $this->jwt_token();
        $tokenDecode = $this->jwt_decode($token);

        if(isset($tokenDecode['userdata'])) {
            $userInfoArray = [
                'username' => $tokenDecode['userdata']->username,
                'password' => $tokenDecode['userdata']->password
            ];
            if($this->session->userdata('master_permission_set') == null) {
                $this->userInfo($userInfoArray);
            }
        }
        return $tokenDecode;
    }

    private function userInfo( $array )
    {
        $username = $array['username'];
        $password = $array['password'];
        $tables   = [
            'student'     => 'student',
            'parents'     => 'parents',
            'teacher'     => 'teacher',
            'user'        => 'user',
            'systemadmin' => 'systemadmin',
        ];

        $setting       = $this->setting_m->get_setting();
        $userFoundInfo = [];
        $tableID       = 0;

        foreach($tables as $table) {
            $user     = $this->db->get_where($table, ["username" => $username, "password" => $password, 'active' => 1]);
            $userInfo = $user->row();
            if(customCompute($userInfo)) {
                $tableID       = $table . 'ID';
                $userFoundInfo = $userInfo;
            }
        }

        if(customCompute($userFoundInfo)) {
            $usertype     = $this->usertype_m->get_single_usertype(['usertypeID' => $userFoundInfo->usertypeID]);
            $sessionArray = [
                'loginuserID'         => $userFoundInfo->$tableID,
                'name'                => $userFoundInfo->name,
                'email'               => $userFoundInfo->email,
                'usertypeID'          => $userFoundInfo->usertypeID,
                'usertype'            => $usertype->usertype,
                'username'            => $userFoundInfo->username,
                'password'            => $password,
                'photo'               => $userFoundInfo->photo,
                'lang'                => $setting->language,
                'defaultschoolyearID' => $setting->school_year,
                "loggedin"            => true,
                "varifyvaliduser"     => true,
            ];

            $this->session->set_userdata($sessionArray);

            $permissionSet = [];
            $session       = $this->session->userdata;
            if ($this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1) {
                if(isset($session['loginuserID'])) {
                    $features = $this->permission_m->get_permission();
                    if(customCompute($features)) {
                        foreach($features as $featureKey => $feature) {
                            $permissionSet['master_permission_set'][trim((string) $feature->name)] = $feature->active;
                        }
                        $permissionSet['master_permission_set']['take_exam'] = 'yes';
                        $this->session->set_userdata($permissionSet);
                    }
                }
            } elseif (isset($session['loginuserID'])) {
                $features = $this->permission_m->get_modules_with_permission($session['usertypeID']);
                foreach($features as $feature) {
                    $permissionSet['master_permission_set'][$feature->name] = $feature->active;
                }
                if($session['usertypeID'] == 3) {
                    $permissionSet['master_permission_set']['take_exam'] = 'yes';
                }
                $this->session->set_userdata($permissionSet);
            }
        }
    }

    private function permissionControl()
    {
        if(!empty($this->uri->segment(3))) {
            $feature = $this->uri->segment(3);
            $mode    = $this->uri->segment(4);

            if ($mode == '') {
                $mode = $feature;
            } elseif ($mode == 'index') {
                $mode = $feature;
            } else {
                $mode = $feature . '_' . $mode;
            }

            if(!empty($mode)) {
                $permissionSet = $this->session->userdata('master_permission_set');
                if(isset($permissionSet[$mode]) && ($permissionSet[$mode] == 'no')) {
                    $this->response([
                        'status'  => false,
                        'message' => 'Permission Deny'
                    ], self::HTTP_UNAUTHORIZED);
                }
            } else {
                $this->response([
                    'status'  => false,
                    'message' => 'Feature Option Not Found'
                ], self::HTTP_UNAUTHORIZED);
            }
        } else {
            $this->response([
                'status'  => false,
                'message' => 'Feature Not Found'
            ], self::HTTP_UNAUTHORIZED);
        }
    }

    public function getHolidays()
    {
        $schoolyearID   = $this->data['siteinfos']->school_year;
        $holidays       = $this->holiday_m->get_order_by_holiday(['schoolyearID' => $schoolyearID]);
        $allHolidayList = [];
        if(customCompute($holidays)) {
            foreach($holidays as $holiday) {
                $from_date = strtotime((string) $holiday->fdate);
                $to_date   = strtotime((string) $holiday->tdate);
                $oneday    = 60 * 60 * 24;
                for($i = $from_date; $i <= $to_date; $i += $oneday) {
                    $allHolidayList[] = date('d-m-Y', $i);
                }
            }
        }
        return array_unique($allHolidayList);
    }

    public function getHolidaysSession( $key = true )
    {
        $schoolyearID   = $this->session->userdata('defaultschoolyearID');
        $holidays       = $this->holiday_m->get_order_by_holiday(['schoolyearID' => $schoolyearID]);
        $allHolidayList = [];
        if(customCompute($holidays)) {
            foreach($holidays as $holiday) {
                $from_date = strtotime((string) $holiday->fdate);
                $to_date   = strtotime((string) $holiday->tdate);
                $oneday    = 60 * 60 * 24;
                $j         = 0;
                for($i = $from_date; $i <= $to_date; $i += $oneday) {
                    if($key) {
                        $allHolidayList[] = date('d-m-Y', $i);
                    } else {
                        $allHolidayList[$j] = date('m-d-Y', $i);
                        $j++;
                    }

                }
            }
        }
        return array_unique($allHolidayList);
    }

    public function getWeekendDays()
    {
        $date_from = strtotime((string) $this->data['schoolyearobj']->startingdate);
        $date_to   = strtotime((string) $this->data['schoolyearobj']->endingdate);
        $oneDay    = 60 * 60 * 24;

        $allDays = [
            '0' => 'Sunday',
            '1' => 'Monday',
            '2' => 'Tuesday',
            '3' => 'Wednesday',
            '4' => 'Thursday',
            '5' => 'Friday',
            '6' => 'Saturday'
        ];

        $weekendDay    = $this->data['siteinfos']->weekends;
        $weekendArrays = explode(',', (string) $weekendDay);

        $weekendDateArrays = [];

        for($i = $date_from; $i <= $date_to; $i += $oneDay) {
            if($weekendDay != "") {
                foreach($weekendArrays as $weekendValue) {
                    if($weekendValue >= 0 && $weekendValue <= 6 && date('l', $i) == $allDays[$weekendValue]) {
                        $weekendDateArrays[] = date('d-m-Y', $i);
                    }
                }
            }
        }
        return $weekendDateArrays;
    }

    public function getWeekendDaysSession()
    {
        $date_from = strtotime((string) $this->data['schoolyearsessionobj']->startingdate);
        $date_to   = strtotime((string) $this->data['schoolyearsessionobj']->endingdate);
        $oneDay    = 60 * 60 * 24;

        $allDays = [
            '0' => 'Sunday',
            '1' => 'Monday',
            '2' => 'Tuesday',
            '3' => 'Wednesday',
            '4' => 'Thursday',
            '5' => 'Friday',
            '6' => 'Saturday'
        ];

        $weekendDay    = $this->data['siteinfos']->weekends;
        $weekendArrays = explode(',', (string) $weekendDay);

        $weekendDateArrays = [];

        for($i = $date_from; $i <= $date_to; $i += $oneDay) {
            if($weekendDay != "") {
                foreach($weekendArrays as $weekendValue) {
                    if($weekendValue >= 0 && $weekendValue <= 6 && date('l', $i) == $allDays[$weekendValue]) {
                        $weekendDateArrays[] = date('d-m-Y', $i);
                    }
                }
            }
        }
        return $weekendDateArrays;
    }

    public function getAllCountry()
    {
        return [
            "AF" => "Afghanistan",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AQ" => "Antarctica",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BV" => "Bouvet Island",
            "BR" => "Brazil",
            "BQ" => "British Antarctic Territory",
            "IO" => "British Indian Ocean Territory",
            "VG" => "British Virgin Islands",
            "BN" => "Brunei",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CT" => "Canton and Enderbury Islands",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "CN" => "China",
            "CX" => "Christmas Island",
            "CC" => "Cocos [Keeling] Islands",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CG" => "Congo - Brazzaville",
            "CD" => "Congo - Kinshasa",
            "CK" => "Cook Islands",
            "CR" => "Costa Rica",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "CI" => "Côte d’Ivoire",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "NQ" => "Dronning Maud Land",
            "DD" => "East Germany",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FK" => "Falkland Islands",
            "FO" => "Faroe Islands",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "FR" => "France",
            "GF" => "French Guiana",
            "PF" => "French Polynesia",
            "TF" => "French Southern Territories",
            "FQ" => "French Southern and Antarctic Territories",
            "GA" => "Gabon",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "DE" => "Germany",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GP" => "Guadeloupe",
            "GU" => "Guam",
            "GT" => "Guatemala",
            "GG" => "Guernsey",
            "GN" => "Guinea",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HM" => "Heard Island and McDonald Islands",
            "HN" => "Honduras",
            "HK" => "Hong Kong SAR China",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IM" => "Isle of Man",
            "IL" => "Israel",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JE" => "Jersey",
            "JT" => "Johnston Island",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Laos",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MO" => "Macau SAR China",
            "MK" => "Macedonia",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "YT" => "Mayotte",
            "FX" => "Metropolitan France",
            "MX" => "Mexico",
            "FM" => "Micronesia",
            "MI" => "Midway Islands",
            "MD" => "Moldova",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "ME" => "Montenegro",
            "MS" => "Montserrat",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar [Burma]",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "NL" => "Netherlands",
            "AN" => "Netherlands Antilles",
            "NT" => "Neutral Zone",
            "NC" => "New Caledonia",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "KP" => "North Korea",
            "VD" => "North Vietnam",
            "MP" => "Northern Mariana Islands",
            "NO" => "Norway",
            "OM" => "Oman",
            "PC" => "Pacific Islands Trust Territory",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PS" => "Palestinian Territories",
            "PA" => "Panama",
            "PZ" => "Panama Canal Zone",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "YD" => "People's Democratic Republic of Yemen",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PN" => "Pitcairn Islands",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RO" => "Romania",
            "RU" => "Russia",
            "RW" => "Rwanda",
            "RE" => "Réunion",
            "BL" => "Saint Barthélemy",
            "SH" => "Saint Helena",
            "KN" => "Saint Kitts and Nevis",
            "LC" => "Saint Lucia",
            "MF" => "Saint Martin",
            "PM" => "Saint Pierre and Miquelon",
            "VC" => "Saint Vincent and the Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "RS" => "Serbia",
            "CS" => "Serbia and Montenegro",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "GS" => "South Georgia and the South Sandwich Islands",
            "KR" => "South Korea",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SD" => "Sudan",
            "SR" => "Suriname",
            "SJ" => "Svalbard and Jan Mayen",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syria",
            "ST" => "São Tomé and Príncipe",
            "TW" => "Taiwan",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania",
            "TH" => "Thailand",
            "TL" => "Timor-Leste",
            "TG" => "Togo",
            "TK" => "Tokelau",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TC" => "Turks and Caicos Islands",
            "TV" => "Tuvalu",
            "UM" => "U.S. Minor Outlying Islands",
            "PU" => "U.S. Miscellaneous Pacific Islands",
            "VI" => "U.S. Virgin Islands",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "SU" => "Union of Soviet Socialist Republics",
            "AE" => "United Arab Emirates",
            "GB" => "United Kingdom",
            "US" => "United States",
            "ZZ" => "Unknown or Invalid Region",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VA" => "Vatican City",
            "VE" => "Venezuela",
            "VN" => "Vietnam",
            "WK" => "Wake Island",
            "WF" => "Wallis and Futuna",
            "EH" => "Western Sahara",
            "YE" => "Yemen",
            "ZM" => "Zambia",
            "ZW" => "Zimbabwe",
            "AX" => "Åland Islands",
        ];
    }
}

