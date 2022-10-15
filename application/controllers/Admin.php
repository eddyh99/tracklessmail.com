<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
    
	public function __construct() {
	    parent::__construct();
	    $this->load->model('mifmodel',"auth");
	    if ($this->session->userdata('logged_status') == NULL) {
	        redirect(base_url()."/adminlogin");
	        die();
	    }
    }

    public function member(){
        $data = array(
            'title'		=> 'Account - Money Industrial Factory',
            'content'	=> 'member/index',
            'extra'     => 'member/js/js_index',
            'menu2'     => 'active'
		);
		$this->load->view('layoutsignin/wrapper', $data);
    }
    
    public function get_all(){
        $member=$this->auth->get_member();
        echo json_encode($member);
    }
    
    public function massmail(){
        $member=$this->auth->get_member();
        $data = array(
            'title'		=> 'Email - Money Industrial Factory',
            'content'	=> 'email/index',
            'extra'     => 'email/js/js_index',
            'menu3'     => 'active',
            'member'    => $member
		);
		$this->load->view('layoutsignin/wrapper', $data);
    }
    
    public function send(){
        $input		= $this->input;
		$email	    = $this->security->xss_clean($input->post("tujuan"));
		$all	    = $this->security->xss_clean($input->post("all"));
		$message	= $this->security->xss_clean($input->post("message"));
		$subject	= $this->security->xss_clean($input->post("subject"));
		
        if (!isset($all)){
            $member=$this->auth->get_member();
            foreach ($member as $dt){
                $this->sendmail($dt["email"],$subject,$message);
            }
        }else{
            foreach ($email as $dt){
                $this->sendmail($dt,$subject,$message);
            }
        }
	    $this->session->set_flashdata('success', "<p style='color:black'>Email is successfully schedule to send</p>");
	    redirect(base_url()."admin/massmail");
        return;
    }   

	public function sendmail($email, $subject, $message){	
		$mail = $this->phpmailer_lib->load();
				   
		$mail->isSMTP();
		$mail->Host			= 'mail.moneyindustrialfactory.io';
		$mail->SMTPAuth		= true;
		$mail->Username		= 'no-reply@moneyindustrialfactory.io';
		$mail->Password		= 'G7VfsqbnH}k}';
		$mail->SMTPAutoTLS	= false;
		$mail->SMTPSecure	= false;
		$mail->Port			= 587;           

		$mail->setFrom('no-reply@moneyindustrialfactory.io', 'Money Industrial Factory');
		$mail->isHTML(true);

		$mail->ClearAllRecipients();
				 
				
		$mail->Subject = $subject;
		$mail->AddAddress($email);

        $mail->msgHTML($message);
		$mail->send();
	}
	
}
