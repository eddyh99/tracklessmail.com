<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Emailmodel extends CI_Model{
    private $regismail = 'regismail';

	public function cekMail($anonregis){
		$sql="SELECT * FROM ".$this->regismail." WHERE anonmail=? AND activate='1'";
		$query=$this->db->query($sql,$anonregis);
		if ($query->num_rows()>0){
			return array("code"=>2021,"message"=>"Username not available");
		}else{
			return array("code"=>0,"message"=>"");
		}
	}
	
	public function insertMail($data){
	    $sql="SELECT activate FROM ".$this->regismail." WHERE anonmail=?";
	    $query=$this->db->query($sql,$data["anonmail"]);
	    if ($query->num_rows()>0){
    	    if ($query->row()->activate==1){
    			return array("code"=>2021,"message"=>"Username not available");
    	    }else{
    	        $qreplace=$this->db->insert_string($this->regismail,$data)." ON DUPLICATE KEY UPDATE email=?";
    	        $queryreplace=$this->db->query($qreplace,$data["email"]);
    	        return array("code"=>0, "message"=>"");
    	    }	        
	    }else{
    		if ($this->db->insert($this->regismail,$data)){
    	        return array("code"=>0, "message"=>"");
    		}else{
    		    return array("code"=>"2022","message"=>"Email already registered");
    		}
	    }
	}
	
	public function activateMail($data,$email){
	    $this->db->where("email",$email);
	    $this->db->update($this->regismail,$data);
	        
	}
	
	public function getExpired(){
	    $now=date("Y-m-d");
	    $sql="SELECT * FROM ".$this->regismail." WHERE ADDDATE(timecreate,30)<?";
	    $query=$this->db->query($sql,$now);
	    return $query->result_array();
	}
	
	public function removeExpired($anonmail){
	    $this->db->where("anonmail",$anonmail);
	    $this->db->delete($this->regismail);
	}
	
	public function resetMail($anonregis){
	    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $shuffle= substr(str_shuffle($permitted_chars), 0, 9);
		
		
		$sql="SELECT * FROM ".$this->regismail." WHERE anonmail=?";
		$query=$this->db->query($sql,$anonregis);
		if ($query->num_rows()>0){
		    $email=$query->row()->email;
		    
		    $sql="UPDATE ".$this->regismail." SET tempcode=? WHERE anonmail=?";
		    $this->db->query($sql,array($shuffle,$anonregis));

			return array("code"=>0,"tempcode"=>$shuffle,"email"=>$email);
		}else{
			return array("code"=>2021,"message"=>"Username not available");
		}
	    
	}

	public function cekcode($email,$code){
	    $sql="SELECT * FROM ".$this->regismail." WHERE anonmail=? AND tempcode=?";
		$query=$this->db->query($sql,array($email,$code));
		if ($query->num_rows()>0){
		    return array("code"=>0,"message"=>"");
		}else{
			return array("code"=>2022,"message"=>"Invalid reset code, please try again");
		}
	}
    
    public function updatecode($email){
        $sql="UPDATE ".$this->regismail." SET tempcode='' WHERE anonmail=?";
        $this->db->query($sql,$email);
    }
}