<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Candidate_m extends MY_Model
{

    protected $_table_name     = 'candidate';
    protected $_primary_key    = 'candidateID';
    protected $_primary_filter = 'intval';
    protected $_order_by       = "candidateID desc";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_candidate_with_student($array = [])
    {
        $this->db->select('candidate.*, student.name, student.registerNO');
        $this->db->from('candidate');
        $this->db->join('student', 'candidate.studentID = student.studentID', 'LEFT');
        if (customCompute($array)) {
            $this->db->where($array);
        }
        $this->db->order_by('candidate.candidateID desc');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_candidate_with_student_sponsorship($array = [])
    {
        $this->db->select('candidate.*, studentrelation.srname, studentrelation.srregisterNO, student.photo, studentrelation.srclasses, sponsorship.sponsorID');
        $this->db->from('candidate');
        $this->db->join('student', 'candidate.studentID = student.studentID', 'LEFT');
        $this->db->join('studentrelation', 'candidate.studentID = studentrelation.srstudentID', 'LEFT');
        $this->db->join('sponsorship', 'candidate.candidateID = sponsorship.candidateID', 'LEFT');
        if (customCompute($array)) {
            $this->db->where($array);
        }
        $this->db->order_by('candidate.candidateID desc');
        $query = $this->db->get();
        return $query->result();
    }


    public function get_candidate($array = null, $signal = false)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_candidate($array = null)
    {
        return parent::get_order_by($array);
    }

    public function get_single_candidate($array = null)
    {
        return parent::get_single($array);
    }

    public function insert_candidate($array)
    {
        return parent::insert($array);
    }

    public function update_candidate($data, $id = null)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_candidate($id)
    {
        return parent::delete($id);
    }
}
