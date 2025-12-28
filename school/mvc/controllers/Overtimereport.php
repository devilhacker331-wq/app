<?php

class Overtimereport extends Admin_Controller
{

    public $load;
    public $session;
    public $lang;
    public $data;
    public $usertype_m;
    public $form_validation;
    public $uri;
    public $phpspreadsheet;
    public $input;
    public $user_m;
    public $manage_salary_m;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usertype_m');
        $this->load->model('overtime_m');
        $this->load->model('systemadmin_m');
        $this->load->model('teacher_m');
        $this->load->model('user_m');
        $this->load->model('manage_salary_m');

        $language = $this->session->userdata('lang');
        $this->lang->load('overtimereport', $language);
    }

    public function index()
    {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/datepicker/datepicker.css',
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js'  => array(
                'assets/datepicker/datepicker.js',
                'assets/select2/select2.js',
            ),
        );
        $usertypeData = [];
        $usertypes    = $this->usertype_m->get_usertype();
        if (customCompute($usertypes)) {
            foreach ($usertypes as $usertypeID => $usertype) {
                if (($usertype->usertypeID == 3) || ($usertype->usertypeID == 4)) {
                    continue;
                }
                $usertypeData[$usertypeID] = $usertype;
            }
        }

        $this->data['usertypes'] = $usertypeData;
        $this->data["subview"]   = "report/overtime/OvertimeReportView";
        $this->load->view('_layout_main', $this->data);
    }

    public function getOvertimeReport()
    {
        $retArray['status'] = false;
        $retArray['render'] = '';
        if (permissionChecker('overtimereport')) {
            if ($_POST !== []) {
                $rules = $this->rules();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == false) {
                    $retArray           = $this->form_validation->error_array();
                    $retArray['status'] = false;
                } else {

                    $this->data['usertypeID'] = $this->input->post('usertypeID');
                    $this->data['userID']     = $this->input->post('userID');
                    $this->data['fromdate']   = empty($this->input->post('fromdate')) ? '0' : strtotime((string) $this->input->post('fromdate'));
                    $this->data['todate']     = empty($this->input->post('todate')) ? '0' : strtotime((string) $this->input->post('todate'));
                    $this->data['allUsers']   = getAllUserObjectWithoutStudent();
                    $this->data['usertypes']  = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');

                    $this->data['overtimes'] = $this->overtime_m->get_overtime_for_report($this->input->post());

                    $retArray['render'] = $this->load->view('report/overtime/OvertimeReport', $this->data, true);
                    $retArray['status'] = true;
                }
            }
        } else {
            $retArray['render'] = $this->load->view('report/reporterror', $this->data, true);
            $retArray['status'] = true;
        }
        echo json_encode($retArray);
        exit;
    }

    public function pdf()
    {
        if (permissionChecker('overtimereport')) {
            $usertypeID = htmlentities((string) escapeString($this->uri->segment(3)));
            $userID     = htmlentities((string) escapeString($this->uri->segment(4)));
            $fromdate   = htmlentities((string) escapeString($this->uri->segment(5)));
            $todate     = htmlentities((string) escapeString($this->uri->segment(6)));

            if ((int) ($usertypeID >= 0) && (int) ($userID >= 0) && (int) ($fromdate >= 0) && (int) ($todate >= 0)) {
                $postArray['usertypeID'] = $usertypeID;
                $postArray['userID']     = $userID;

                if ($fromdate != '0' && $todate != '0') {
                    $postArray['fromdate'] = date('d-m-Y', $fromdate);
                    $postArray['todate']   = date('d-m-Y', $todate);
                }

                $this->data['usertypeID'] = $usertypeID;
                $this->data['userID']     = $userID;
                $this->data['fromdate']   = $fromdate;
                $this->data['todate']     = $todate;

                $this->data['allUsers']  = getAllUserObjectWithoutStudent();
                $this->data['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');

                $this->data['overtimes'] = $this->overtime_m->get_overtime_for_report($postArray);

                $this->reportPDF('overtimereport.css', $this->data, 'report/overtime/OvertimeReportPDF');
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function xlsx()
    {
        if (permissionChecker('overtimereport')) {
            $this->load->library('phpspreadsheet');

            $sheet = $this->phpspreadsheet->spreadsheet->getActiveSheet();
            $sheet->getDefaultColumnDimension()->setWidth(25);
            $sheet->getDefaultRowDimension()->setRowHeight(25);
            $sheet->getColumnDimension('A')->setWidth(25);
            $sheet->getRowDimension('1')->setRowHeight(25);
            $sheet->getRowDimension('2')->setRowHeight(25);

            $data = $this->xmlData();

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="overtimereport.xlsx"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0

            $this->phpspreadsheet->output($this->phpspreadsheet->spreadsheet);
        } else {
            $this->data["subview"] = "errorpermission";
            $this->load->view('_layout_main', $this->data);
        }
    }

    private function xmlData()
    {
        if (permissionChecker('overtimereport')) {
            $usertypeID = htmlentities((string) escapeString($this->uri->segment(3)));
            $userID     = htmlentities((string) escapeString($this->uri->segment(4)));
            $fromdate   = htmlentities((string) escapeString($this->uri->segment(5)));
            $todate     = htmlentities((string) escapeString($this->uri->segment(6)));

            if ((int) ($usertypeID >= 0) && (int) ($userID >= 0) && (int) ($fromdate >= 0) && (int) ($todate >= 0)) {

                $postArray['usertypeID'] = $usertypeID;
                $postArray['userID']     = $userID;

                if ($fromdate != '0' && $todate != '0') {
                    $postArray['fromdate'] = date('d-m-Y', $fromdate);
                    $postArray['todate']   = date('d-m-Y', $todate);
                }

                $this->data['fromdate']   = $fromdate;
                $this->data['todate']     = $todate;
                $this->data['usertypeID'] = $usertypeID;
                $this->data['userID']     = $userID;

                $this->data['allUsers']  = getAllUserObjectWithoutStudent();
                $this->data['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');

                $this->data['overtimes'] = $this->overtime_m->get_overtime_for_report($postArray);

                return $this->generateXML($this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    private function generateXML($array)
    {
        extract($array);
        if (customCompute($overtimes)) {
            $sheet        = $this->phpspreadsheet->spreadsheet->getActiveSheet();
            $row          = 1;
            $topCellMerge = true;

            if ($fromdate != 0 && $todate != 0) {
                $datefrom = $this->lang->line('overtimereport_fromdate') . " : ";
                $datefrom .= date('d M Y', $fromdate);
                $dateto = $this->lang->line('overtimereport_todate') . " : ";
                $dateto .= date('d M Y', $todate);

                $sheet->setCellValue('A' . $row, $datefrom);
                $sheet->setCellValue('G' . $row, $dateto);
            } elseif ($usertypeID != 0 && $userID != 0) {
                $usertype = $this->lang->line('overtimereport_role') . " : ";
                $usertype .= $usertypes[$usertypeID];
                $username = $this->lang->line('overtimereport_user_name') . " : ";
                $username .= $allUsers[$usertypeID][$userID]->name;

                $sheet->setCellValue('A' . $row, $usertype);
                $sheet->setCellValue('G' . $row, $username);
            } elseif ($usertypeID != 0) {
                $topCellMerge = false;
                $usertype     = $this->lang->line('overtimereport_role') . " : ";
                $usertype .= $usertypes[$usertypeID];

                $sheet->setCellValue('A' . $row, $usertype);
            } elseif ($usertypeID == 0) {
                $topCellMerge = false;
                $usertype     = $this->lang->line('overtimereport_role') . " : ";
                $usertype .= $this->lang->line('overtimereport_alluser');

                $sheet->setCellValue('A' . $row, $usertype);
            }

            $headers[] = $this->lang->line('slno');
            $headers[] = $this->lang->line('overtimereport_role');
            $headers[] = $this->lang->line('overtimereport_user');
            $headers[] = $this->lang->line('overtimereport_date');
            $headers[] = $this->lang->line('overtimereport_hours');
            $headers[] = $this->lang->line('overtimereport_amount');
            $headers[] = $this->lang->line('overtimereport_total_amount');

            if (customCompute($headers)) {
                $column = "A";
                $row    = 2;
                foreach ($headers as $header) {
                    $sheet->setCellValue($column . $row, $header);
                    $column++;
                }
            }

            $i                   = 0;
            $bodys               = [];
            $totalOvertimeAmount = 0;
            foreach ($overtimes as $overtime) {
                $bodys[$i][] = $i + 1;
                $bodys[$i][] = isset($usertypes[$overtime->usertypeID]) ? $usertypes[$overtime->usertypeID] : '';
                $bodys[$i][] = isset($allUsers[$overtime->usertypeID][$overtime->userID]) ? $allUsers[$overtime->usertypeID][$overtime->userID]->name : '';
                $bodys[$i][] = date('d-M-Y h:i A', strtotime((string) $overtime->date));
                $bodys[$i][] = $overtime->hours;
                $bodys[$i][] = number_format($overtime->amount, 2);
                $bodys[$i][] = number_format($overtime->total_amount, 2);

                $totalOvertimeAmount = $overtime->total_amount;
                $i++;
            }
            $bodys[$i][] = "";
            $bodys[$i][] = "";
            $bodys[$i][] = "";
            $bodys[$i][] = "";
            $bodys[$i][] = "";
            $bodys[$i][] = "";
            $bodys[$i][] = number_format($totalOvertimeAmount, 2);

            if (customCompute($bodys)) {
                $row = 3;
                foreach ($bodys as $single_rows) {
                    $column = 'A';
                    foreach ($single_rows as $value) {
                        $sheet->setCellValue($column . $row, $value);
                        $column++;
                    }
                    $row++;
                }
            }

            $grandTotalValue = $this->lang->line('overtimereport_grand_total') . (empty($siteinfos->currency_code) ? '' : "(" . $siteinfos->currency_code . ")");

            $sheet->setCellValue('A' . ($row - 1), $grandTotalValue);

            $styleArray = [
                'font'      => [
                    'bold' => true,
                ],
                'alignment' => [
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];
            $sheet->getStyle('A1:G2')->applyFromArray($styleArray);

            $styleArray = [
                'font'      => [
                    'bold' => false,
                ],
                'alignment' => [
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            $styleColumn = "G" . ($row - 2);
            $sheet->getStyle('A3:' . $styleColumn)->applyFromArray($styleArray);

            $styleArray = [
                'font'      => [
                    'bold' => true,
                ],
                'alignment' => [
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            $styleColumn = $row - 1;
            $sheet->getStyle('A' . $styleColumn . ':' . 'G' . $styleColumn)->applyFromArray($styleArray);

            if ($topCellMerge) {
                $sheet->mergeCells("B1:F1");
            } else {
                $sheet->mergeCells("B1:G1");
            }

            $startmerge = "A" . $styleColumn;
            $endmerge   = "F" . $styleColumn;
            $sheet->mergeCells("$startmerge:$endmerge");
        } else {
            redirect(base_url('overtimereport'));
        }
    }

    public function send_pdf_to_mail()
    {
        $retArray['status']  = false;
        $retArray['message'] = '';

        if (permissionChecker('overtimereport')) {
            if ($_POST !== []) {
                $rules = $this->send_pdf_to_mail_rules();
                $this->form_validation->set_rules($rules);

                if ($this->form_validation->run() == false) {
                    $retArray           = $this->form_validation->error_array();
                    $retArray['status'] = false;
                } else {
                    $to      = $this->input->post('to');
                    $subject = $this->input->post('subject');
                    $message = $this->input->post('message');

                    $usertypeID = $this->input->post('usertypeID');
                    $userID     = $this->input->post('userID');
                    $fromdate   = $this->input->post('fromdate');
                    $todate     = $this->input->post('todate');

                    $postArray['usertypeID'] = $usertypeID;
                    $postArray['userID']     = $userID;
                    if ($fromdate != '' && $todate != '') {
                        $postArray['fromdate'] = date('d-m-Y', strtotime((string) $fromdate));
                        $postArray['todate']   = date('d-m-Y', strtotime((string) $todate));
                    }

                    $this->data['usertypeID'] = $usertypeID;
                    $this->data['userID']     = $userID;
                    $this->data['fromdate']   = strtotime((string) $fromdate);
                    $this->data['todate']     = strtotime((string) $todate);

                    $this->data['allUsers']  = getAllUserObjectWithoutStudent();
                    $this->data['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');

                    $this->data['overtimes'] = $this->overtime_m->get_overtime_for_report($postArray);

                    $this->reportSendToMail('overtimereport.css', $this->data, 'report/overtime/OvertimeReportPDF', $to, $subject, $message);
                    $retArray['status']  = true;
                    $retArray['message'] = 'Success';
                }
            } else {
                $retArray['status']  = false;
                $retArray['message'] = $this->lang->line('overtimereport_permissionmethod');
            }
        } else {
            $retArray['status']  = false;
            $retArray['message'] = $this->lang->line('overtimereport_permission');
        }
        echo json_encode($retArray);
        exit;
    }

    public function getUser()
    {
        $roleId = $this->input->post('usertypeID');
        echo "<option value='0'>" . $this->lang->line("overtimereport_please_select") . "</option>";
        if ($roleId) {
            $table   = '';
            $tableID = '';
            if ($roleId == 1) {
                $table   = 'systemadmin';
                $tableID = 'systemadminID';
            } elseif ($roleId == 2) {
                $table   = 'teacher';
                $tableID = 'teacherID';
            } else {
                $table   = 'user';
                $tableID = 'userID';
            }

            $getUsers     = $this->user_m->get_all_user($table, array('usertypeID' => $roleId));
            $manageSalary = pluck_multi_array_key($this->manage_salary_m->get_order_by_manage_salary(['salary' => 1]), 'obj', 'usertypeID', 'userID');

            if (customCompute($getUsers)) {
                foreach ($getUsers as $key => $user) {
                    if (isset($manageSalary[$user->usertypeID][$user->$tableID])) {
                        echo "<option value='" . $user->$tableID . "'>" . $user->name . "</option>";
                    }
                }
            }
        }
    }

    public function date_valid($date)
    {
        if ($date) {
            if (strlen((string) $date) < 10) {
                $this->form_validation->set_message("date_valid", "The %s is not valid dd-mm-yyyy.");
                return false;
            } else {
                $arr  = explode("-", (string) $date);
                $dd   = $arr[0];
                $mm   = $arr[1];
                $yyyy = $arr[2];
                if (checkdate($mm, $dd, $yyyy)) {
                    return true;
                } else {
                    $this->form_validation->set_message("date_valid", "The %s is not valid dd-mm-yyyy.");
                    return false;
                }
            }
        }
        return true;
    }

    public function unique_date()
    {
        $fromdate = $this->input->post('fromdate');
        $todate   = $this->input->post('todate');

        $startingdate = $this->data['schoolyearsessionobj']->startingdate;
        $endingdate   = $this->data['schoolyearsessionobj']->endingdate;

        if ($fromdate != '' && $todate == '') {
            $this->form_validation->set_message("unique_date", "The to date field not be empty .");
            return false;
        }

        if ($fromdate == '' && $todate != '') {
            $this->form_validation->set_message("unique_date", "The from date field not be empty .");
            return false;
        }

        if ($fromdate != '' && $todate != '') {
            if (strtotime((string) $fromdate) > strtotime((string) $todate)) {
                $this->form_validation->set_message("unique_date", "The from date can not be upper than todate .");
                return false;
            }

            if ((strtotime((string) $fromdate) < strtotime((string) $startingdate)) || (strtotime((string) $fromdate) > strtotime((string) $endingdate))) {
                $this->form_validation->set_message("unique_date", "The from date are invalid .");
                return false;
            }

            if ((strtotime((string) $todate) < strtotime((string) $startingdate)) || (strtotime((string) $todate) > strtotime((string) $endingdate))) {
                $this->form_validation->set_message("unique_date", "The to date are invalid .");
                return false;
            }
            return true;
        }

        return true;
    }

    public function rules()
    {
        return array(
            array(
                'field' => 'usertypeID',
                'label' => $this->lang->line('overtimereport_user_type'),
                'rules' => 'trim|xss_clean|numeric',
            ),
            array(
                'field' => 'userID',
                'label' => $this->lang->line('overtimereport_user_name'),
                'rules' => 'trim|xss_clean|numeric',
            ),
            array(
                'field' => 'fromdate',
                'label' => $this->lang->line('overtimereport_fromdate'),
                'rules' => 'trim|xss_clean|callback_date_valid|callback_unique_date',
            ),
            array(
                'field' => 'todate',
                'label' => $this->lang->line('overtimereport_todate'),
                'rules' => 'trim|xss_clean|callback_date_valid',
            ),
        );
    }

    public function send_pdf_to_mail_rules()
    {
        return array(
            array(
                'field' => 'to',
                'label' => $this->lang->line('overtimereport_to'),
                'rules' => 'trim|required|xss_clean|valid_email',
            ),
            array(
                'field' => 'subject',
                'label' => $this->lang->line('overtimereport_subject'),
                'rules' => 'trim|required|xss_clean',
            ),
            array(
                'field' => 'message',
                'label' => $this->lang->line('overtimereport_message'),
                'rules' => 'trim|xss_clean',
            ),
            array(
                'field' => 'usertypeID',
                'label' => $this->lang->line('overtimereport_user_type'),
                'rules' => 'trim|xss_clean|numeric',
            ),
            array(
                'field' => 'userID',
                'label' => $this->lang->line('overtimereport_user_name'),
                'rules' => 'trim|xss_clean|numeric',
            ),
            array(
                'field' => 'fromdate',
                'label' => $this->lang->line('overtimereport_fromdate'),
                'rules' => 'trim|xss_clean|callback_date_valid|callback_unique_date',
            ),
            array(
                'field' => 'todate',
                'label' => $this->lang->line('overtimereport_todate'),
                'rules' => 'trim|xss_clean|callback_date_valid',
            ),
        );
    }
}
