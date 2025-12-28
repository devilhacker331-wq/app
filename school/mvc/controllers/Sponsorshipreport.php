<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Sponsorshipreport extends Admin_Controller
{
public $load;
    public $session;
    public $lang;
    public $data;
    public $form_validation;
    public $uri;
    public $input;
    public $phpspreadsheet;
    public $sponsorship_m;
    /*
| -----------------------------------------------------
| PRODUCT NAME:     INILABS SCHOOL MANAGEMENT SYSTEM
| -----------------------------------------------------
| AUTHOR:            INILABS TEAM
| -----------------------------------------------------
| EMAIL:            info@inilabs.net
| -----------------------------------------------------
| COPYRIGHT:        RESERVED BY INILABS IT
| -----------------------------------------------------
| WEBSITE:            http://inilabs.net
| -----------------------------------------------------
 */

    public function __construct()
    {
        parent::__construct();
        $this->load->model("sponsorship_m");
        $language = $this->session->userdata('lang');
        $this->lang->load('sponsorshipreport', $language);

        $this->data['types'] = [
            0 => $this->lang->line("sponsorshipreport_please_select"),
            1 => $this->lang->line("sponsorshipreport_pending"),
            2 => $this->lang->line("sponsorshipreport_expring"),
            3 => $this->lang->line("sponsorshipreport_expried"),
        ];
    }

    public function index()
    {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js'  => array(
                'assets/select2/select2.js',
            ),
        );
        $this->data["subview"] = "report/sponsorship/SponsorshipReportView";
        $this->load->view('_layout_main', $this->data);
    }

    public function getsponsorshipreport()
    {
        $retArray['status'] = false;
        $retArray['render'] = '';
        if (permissionChecker('sponsorshipreport')) {
            if ($_POST !== []) {
                $rules = $this->rules();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == false) {
                    $retArray           = $this->form_validation->error_array();
                    $retArray['status'] = false;
                } else {
                    $this->data['typeId']       = $this->input->post('type_id');
                    $this->data['sponsorships'] = $this->sponsorship_m->get_sponsorship_for_report($this->data['typeId']);
                    $retArray['render']         = $this->load->view('report/sponsorship/SponsorshipReport', $this->data, true);
                    $retArray['status']         = true;
                }
            } else {
                $retArray['status'] = true;
                $retArray['render'] = $this->load->view('report/reporterror', $this->data, true);
            }
        } else {
            $retArray['status'] = true;
            $retArray['render'] = $this->load->view('report/reporterror', $this->data, true);
        }
        echo json_encode($retArray);
        exit;
    }

    public function pdf()
    {
        if (permissionChecker('sponsorshipreport')) {
            $typeId = htmlentities((string) escapeString($this->uri->segment(3)));
            if (((int) $typeId > 0)) {
                $this->data['typeId']       = $typeId;
                $this->data['sponsorships'] = $this->sponsorship_m->get_sponsorship_for_report($typeId);

                $this->reportPDF('sponsorshipreport.css', $this->data, 'report/sponsorship/SponsorshipReportPDF');
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function send_pdf_to_mail()
    {
        $retArray['status']  = false;
        $retArray['message'] = '';

        if (permissionChecker('sponsorshipreport')) {
            if ($_POST !== []) {
                $to      = $this->input->post('to');
                $subject = $this->input->post('subject');
                $message = $this->input->post('message');
                $typeID  = $this->input->post('typeID');

                $rules = $this->send_pdf_to_mail_rules();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == false) {
                    $retArray           = $this->form_validation->error_array();
                    $retArray['status'] = false;
                } else {
                    $this->data['typeId']       = $typeID;
                    $this->data['sponsorships'] = $this->sponsorship_m->get_sponsorship_for_report($typeID);

                    $this->reportSendToMail('sponsorshipreport.css', $this->data, 'report/sponsorship/SponsorshipReportPDF', $to, $subject, $message);
                    $retArray['status'] = true;
                }
            } else {
                $retArray['message'] = $this->lang->line('sponsorshipreport_permissionmethod');
            }
        } else {
            $retArray['message'] = $this->lang->line('sponsorshipreport_permission');
        }
        echo json_encode($retArray);
        exit;
    }

    public function xlsx()
    {
        if (permissionChecker('sponsorshipreport')) {
            $this->load->library('phpspreadsheet');

            $sheet = $this->phpspreadsheet->spreadsheet->getActiveSheet();
            $sheet->getDefaultColumnDimension()->setWidth(25);
            $sheet->getDefaultRowDimension()->setRowHeight(25);
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getRowDimension('1')->setRowHeight(25);
            $sheet->getRowDimension('2')->setRowHeight(25);

            $data = $this->xmlData();

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="sponsorshipreport.xlsx"');
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
        if (permissionChecker('sponsorshipreport')) {
            $typeId = htmlentities((string) escapeString($this->uri->segment(3)));
            if (((int) $typeId > 0)) {
                $sponsorships = $this->sponsorship_m->get_sponsorship_for_report($typeId);

                $i                = 0;
                $sponsorshipArray = [];
                if (customCompute($sponsorships)) {
                    foreach ($sponsorships as $sponsorship) {
                        $i++;
                        $sponsorshipArray[$i]['cname']      = $sponsorship->cname;
                        $sponsorshipArray[$i]['cphone']     = $sponsorship->cphone;
                        $sponsorshipArray[$i]['cemail']     = $sponsorship->cemail;
                        $sponsorshipArray[$i]['name']       = $sponsorship->name;
                        $sponsorshipArray[$i]['start_date'] = $sponsorship->start_date;
                        $sponsorshipArray[$i]['end_date']   = $sponsorship->end_date;
                    }
                }

                $this->data['typeId']       = $typeId;
                $this->data['sponsorships'] = $sponsorshipArray;
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

    private function generateXML($data)
    {
        extract($data);
        if (customCompute($sponsorships)) {
            $sheet          = $this->phpspreadsheet->spreadsheet->getActiveSheet();
            $maxColumnCount = 7;

            $headerColumn = "A";
            for ($i = 1; $i < $maxColumnCount; $i++) {
                $headerColumn++;
            }

            $row    = 1;
            $column = 'A';

            $reportLabel = $this->lang->line('sponsorshipreport_report_for') . " - " . $this->lang->line('sponsorshipreport_sponsorship');
            $sheet->setCellValue($column . $row, $reportLabel);

            //Make Header Data Array
            $headers['slno']       = $this->lang->line('sponsorshipreport_slno');
            $headers['cname']      = $this->lang->line('sponsorshipreport_candidate_name');
            $headers['cphone']     = $this->lang->line('sponsorshipreport_candidate_phone');
            $headers['cemail']     = $this->lang->line('sponsorshipreport_candidate_email');
            $headers['name']       = $this->lang->line('sponsorshipreport_sponsor_name');
            $headers['start_date'] = $this->lang->line('sponsorshipreport_start_date');
            $headers['end_date']   = $this->lang->line('sponsorshipreport_end_date');

            //Make Xml Header Array
            $column = 'A';
            $row    = 2;
            foreach ($headers as $header) {
                $sheet->setCellValue($column . $row, $header);
                $column++;
            }

            //Make Body Array
            $i    = 0;
            $body = [];
            foreach ($sponsorships as $sponsorship) {
                $i++;
                $body[$i][] = $i;
                $body[$i][] = $sponsorship['cname'];
                $body[$i][] = $sponsorship['cphone'];
                $body[$i][] = $sponsorship['cemail'];
                $body[$i][] = $sponsorship['name'];
                $body[$i][] = date('d M Y', strtotime((string) $sponsorship['start_date']));
                $body[$i][] = date('d M Y', strtotime((string) $sponsorship['end_date']));

            }

            //Make Here Xml Body
            $row = 3;
            if (customCompute($body)) {
                foreach ($body as $rows) {
                    $column = "A";
                    foreach ($rows as $value) {
                        $sheet->setCellValue($column . $row, $value);
                        $column++;
                    }
                    $row++;
                }
            }

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

            $sheet->getStyle('A1:' . $headerColumn . '2')->applyFromArray($styleArray);

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
            $row--;
            $sheet->getStyle('A3:' . $headerColumn . $row)->applyFromArray($styleArray);

            $sheet->mergeCells("B1:G1");
        } else {
            redirect(base_url('sponsorshipreport'));
        }
    }

    protected function send_pdf_to_mail_rules()
    {
        return array(
            array(
                'field' => 'typeID',
                'label' => $this->lang->line("sponsorshipreport_type"),
                'rules' => 'trim|required|xss_clean|callback_unique_data',
            ),
            array(
                'field' => 'to',
                'label' => $this->lang->line("sponsorshipreport_to"),
                'rules' => 'trim|required|xss_clean|valid_email',
            ),
            array(
                'field' => 'subject',
                'label' => $this->lang->line("sponsorshipreport_subject"),
                'rules' => 'trim|required|xss_clean',
            ),
            array(
                'field' => 'message',
                'label' => $this->lang->line("sponsorshipreport_message"),
                'rules' => 'trim|xss_clean',
            ),
        );
    }

    protected function rules()
    {
        return [
            [
                'field' => 'type_id',
                'label' => $this->lang->line("sponsorshipreport_type"),
                'rules' => 'trim|required|xss_clean|callback_unique_data',
            ],
        ];
    }

    public function unique_data($data)
    {
        if ($data != "" && $data === "0") {
            $this->form_validation->set_message('unique_data', 'The %s field is required.');
            return false;
        }
        return true;
    }
}
