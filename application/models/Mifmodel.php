<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mifmodel extends CI_Model{
    private $pengguna  = 'pengguna';
    private $regismail = 'regismail';
    
    function get_single($data){
        $this->db->where($data);
        return $this->db->get($this->pengguna)->row();
    }
    
    function get_member(){
        return $this->db->get($this->regismail)->result_array();

    }
    
    function cekemail($email){
	    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $shuffle= substr(str_shuffle($permitted_chars), 0, 9);

        $this->db->where("email",$email);
        if ($this->db->get($this->pengguna)->num_rows()>0){
            $this->db->where("email",$email);
            $this->db->update($this->pengguna,array("token"=>$shuffle));
            return $shuffle;
        }else{
            return "";
        }
    }

    function check_token($token){
        $this->db->where("token",$token);
        return $this->db->get($this->pengguna)->row();
        
    }    
    
    function updatepass($data,$email){
        $this->db->where("email",$email);
        $this->db->update($this->pengguna,$data);
    }
}
?>