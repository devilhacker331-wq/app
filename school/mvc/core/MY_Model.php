<?php if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

class MY_Model extends CI_Model
{

    public $rules = [];
    protected $_table_name = '';
    protected $_primary_key = '';
    protected $_primary_filter = 'intval';
    protected $_order_by = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function order( $order )
    {
        $this->_order_by = $order;
    }

    public function get_query( $query, $single = false )
    {
        $query = $this->db->query($query);

        if ( $single ) {
            if ( $query->row() == null ) {
                return [];
            }
            return $query->row();
        } else {
            if ( $query->result() == null ) {
                return [];
            }
            return $query->result();
        }
    }

    public function get( $id = null, $single = false )
    {
        if ( $id != null ) {
            $filter = $this->_primary_filter;
            $id     = $filter($id);
            $this->db->where($this->_primary_key, $id);
            $method = 'row';
        } elseif ( $single == true ) {
            $method = 'row';
        } else {
            $method = 'result';
        }

        if ( !customCompute($this->db->order_by($this->_order_by)) ) {
            $this->db->order_by($this->_order_by);
        }
        return $this->db->get($this->_table_name)->$method();
    }

    function get_order_by( $array = null )
    {
        if ( $array != null ) {
            $this->db->from($this->_table_name)->where($array)->order_by($this->_order_by);
            $query = $this->db->get();
            if ( $query ) {
                return $query->result();
            } else {
                return $query;
            }
        } else {
            $this->db->from($this->_table_name)->order_by($this->_order_by);
            $query = $this->db->get();
            if ( $query ) {
                return $query->result();
            } else {
                return $query;
            }
        }
    }

    function get_single( $array = null )
    {
        if ( $array != null ) {
            $this->db->select()->from($this->_table_name)->where($array);
            $query = $this->db->get();
            return $query->row();
        } else {
            $this->db->select()->from($this->_table_name)->order_by($this->_order_by);
            $query = $this->db->get();
            return $query->result();
        }
    }

    function get_where_in( $arrays, $key = null, $whereArray = null )
    {
        if ( customCompute($arrays) ) {
            if ( $key == null ) {
                $this->db->where_in($this->_primary_key, $arrays);
            } else {
                $this->db->where_in($key, $arrays);
            }

            if ( $whereArray != null ) {
                $this->db->where($whereArray);
            }

            $query = $this->db->get($this->_table_name);
            return $query->result();
        } else {
            return [];
        }
    }

    function get_sum( $clmn, $array = [] )
    {
        $this->db->select_sum($clmn);
        if ( customCompute($array) ) {
            $this->db->where($array);
        }
        $query = $this->db->get($this->_table_name);
        return $query->row();
    }

    function get_where_sum( $clmn, $wherein, $arrays, $groupbyID = null )
    {
        $wherestring = '';
        $clmnstring  = '';
        if ( customCompute($arrays) ) {
            $count       = customCompute($arrays);
            $wherestring .= '(';
            $i           = 1;
            foreach ( $arrays as $array ) {
                if ( $i == $count ) {
                    $wherestring .= "'" . $array . "'";
                } else {
                    $wherestring .= "'" . $array . "',";
                }
                $i++;
            }
            $wherestring .= ')';
        }

        if ( is_array($clmn) ) {
            $count = customCompute($clmn);
            $i     = 1;
            foreach ( $clmn as $clm ) {
                if ( $i == $count ) {
                    $clmnstring .= 'SUM(' . $clm . ') AS ' . $clm;
                } else {
                    $clmnstring .= 'SUM(' . $clm . ') AS ' . $clm . ', ';
                }
                $i++;
            }
        } else {
            $clmnstring .= 'SUM(' . $clmn . ') AS ' . $clmn;
        }

        if ( $wherein == null || $wherein == '' ) {
            $wherein = $this->_primary_key;
        }

        if ( !empty($wherestring) ) {
            if ( $groupbyID == null ) {
                $query = "SELECT " . $clmnstring . " FROM " . $this->_table_name . " WHERE " . $wherein . " IN " . $wherestring;
            } else {
                $query = "SELECT " . $clmnstring . ", " . $groupbyID . " FROM " . $this->_table_name . " WHERE " . $wherein . " IN " . $wherestring . " GROUP BY " . $groupbyID;
            }
        } else {
            if ( $groupbyID == null ) {
                $query = "SELECT " . $clmnstring . " FROM " . $this->_table_name;
            } else {
                $query = "SELECT " . $clmnstring . ", " . $groupbyID . " FROM " . $this->_table_name . " GROUP BY " . $groupbyID;
            }
        }

        $query = $this->db->query($query);
        if ( $groupbyID == null ) {
            return $query->row();
        } else {
            return $query->result();
        }
    }

    function insert( $array )
    {
        $this->db->insert($this->_table_name, $array);
        $id = $this->db->insert_id();
        return $id;
    }

    function insert_batch( $array )
    {
        $this->db->insert_batch($this->_table_name, $array);
        $id = $this->db->insert_id();
        return $id;
    }

    function update_batch( $array, $id )
    {
        $this->db->update_batch($this->_table_name, $array, $id);
        return true;;
    }

    function update( $data, $id = null )
    {
        $filter = $this->_primary_filter;
        $id     = $filter($id);
        $this->db->set($data);
        $this->db->where($this->_primary_key, $id);
        $this->db->update($this->_table_name);
    }

    public function delete_batch( $arrays )
    {
        if ( customCompute($arrays) ) {
            $this->db->where_in($this->_primary_key, $arrays);
            $this->db->delete($this->_table_name);
            return true;
        } else {
            return false;
        }
    }

    public function delete( $id )
    {
        $filter = $this->_primary_filter;
        $id     = $filter($id);

        if ( !$id ) {
            return false;
        }
        $this->db->where($this->_primary_key, $id);
        $this->db->limit(1);
        $this->db->delete($this->_table_name);
    }

    public function hash( $string )
    {
        return hash("sha512", $string . config_item("encryption_key"));
    }

    public function makeArrayWithTableName( $array, $tableName = null )
    {
        if ( is_null($tableName) ) {
            $tableName = $this->_table_name;
        }
        if ( is_array($array) ) {
            $ar = [];
            foreach ( $array as $key => $a ) {
                $relation = explode('.', $key);
                if ( customCompute($relation) == 1 ) {
                    $ar[ $tableName . '.' . $key ] = $a;
                } else {
                    $ar[ $key ] = $a;
                }
            }
            return $ar;
        } else {
            return $array;
        }
    }
}