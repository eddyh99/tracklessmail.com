<?php

class Member_model extends CI_Model
{
    public function getMember()
    {
        $this->db->select('*');
        $this->db->from('regismail');
        $this->db->order_by('timecreate', 'DESC');
        $data = $this->db->get()->result_array();
        return $data;
    }
}