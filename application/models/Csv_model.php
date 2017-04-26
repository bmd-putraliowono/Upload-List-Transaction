<?php

class Csv_model extends CI_Model {

    function __construct() {
        parent::__construct();

    }

    function select_trans() {
        $this->db->distinct();
        $this->db->select('ListName');
        $query = $this->db->get('azswd01.BI_Data.dbo.tempTransGA');
        return $query->result_array();
    }

    function insert_trans_b($table,$data) {
        $this->db->insert_batch($table, $data);
    }

    function insert_trans($table,$data) {
        $this->db->insert($table, $data);
    }
}
    /*field azswd01.BI_Data.dbo.BI_Transaction_DM
    1. KodeTrx
    2. ListName
    3. Date_Time
    */
?>