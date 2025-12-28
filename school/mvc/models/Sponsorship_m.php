<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Sponsorship_m extends MY_Model
{

    protected $_table_name     = 'sponsorship';
    protected $_primary_key    = 'sponsorshipID';
    protected $_primary_filter = 'intval';
    protected $_order_by       = "sponsorshipID desc";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_sponsorship_with_student()
    {
        $this->db->select('sponsorship.*, student.name as studentname, sponsor.name as sponsorname');
        $this->db->from('sponsorship');
        $this->db->join('student', 'sponsorship.studentID=student.studentID', 'LEFT');
        $this->db->join('sponsor', 'sponsorship.sponsorID=sponsor.sponsorID', 'LEFT');
        $this->db->order_by('sponsorship.sponsorshipID desc');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_sponsorship_for_report($id)
    {
        if ($id == 1) {
            $this->db->select('sponsor.*, sponsorship.start_date, sponsorship.end_date, student.name as cname, student.email as cemail, student.phone as cphone');
            $this->db->from('sponsorship');
            $this->db->join('student', 'sponsorship.studentID = student.studentID', 'LEFT');
            $this->db->join('sponsor', 'sponsorship.sponsorID=sponsor.sponsorID', 'LEFT');
            $this->db->where('sponsorship.payment_date', null);
            $this->db->order_by('sponsorship.sponsorshipID desc');
            $query = $this->db->get();
            return $query->result();
        } else if ($id == 2) {
            $array = [];
            $this->db->select('sponsor.*, sponsorship.start_date, sponsorship.end_date, student.name as cname, student.email as cemail, student.phone as cphone');
            $this->db->from('sponsorship');
            $this->db->join('student', 'sponsorship.studentID = student.studentID', 'LEFT');
            $this->db->join('sponsor', 'sponsorship.sponsorID=sponsor.sponsorID', 'LEFT');
            $this->db->where('sponsorship.payment_date != ', null);
            $this->db->order_by('sponsorship.sponsorshipID desc');
            $query     = $this->db->get();
            $responses = $query->result();

            if (is_array($responses)) {
                $i = 0;
                foreach ($responses as $response) {
                    if (strtotime($response->end_date) > strtotime(date('Y-m-d'))) {
                        $date = date_diff(date_create($response->end_date), date_create(date('y-m-d')));
                        if ($date->days < 90) {
                            $array[$i] = $response;
                            $i++;
                        }

                    }
                }
            }

            return $array;
        } else {
            $array = [];
            $this->db->select('sponsor.*, sponsorship.start_date, sponsorship.end_date, student.name as cname, student.email as cemail, student.phone as cphone');
            $this->db->from('sponsorship');
            $this->db->join('student', 'sponsorship.studentID = student.studentID', 'LEFT');
            $this->db->join('sponsor', 'sponsorship.sponsorID=sponsor.sponsorID', 'LEFT');
            $this->db->where('sponsorship.payment_date != ', null);
            $this->db->order_by('sponsorship.sponsorshipID desc');
            $query     = $this->db->get();
            $responses = $query->result();

            if (is_array($responses)) {
                $i = 0;
                foreach ($responses as $response) {
                    if (strtotime($response->end_date) < strtotime(date('Y-m-d'))) {
                        $date = date_diff(date_create($response->end_date), date_create(date('y-m-d')));

                        $array[$i] = $response;
                        $i++;

                    }
                }
            }

            return $array;
        }
    }

    public function get_sponsorship($array = null, $signal = false)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_sponsorship($array = null)
    {
        return parent::get_order_by($array);
    }

    public function get_single_sponsorship($array = null)
    {
        return parent::get_single($array);
    }

    public function insert_sponsorship($array)
    {
        return parent::insert($array);
    }

    public function update_sponsorship($data, $id = null)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_sponsorship($id)
    {
        return parent::delete($id);
    }
}
