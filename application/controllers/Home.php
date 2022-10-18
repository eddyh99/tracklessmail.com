<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function index()
	{
        $data = array(
            'title'		=> 'MIF - Money Industrial Factory',
            'content'	=> 'home/index',
		);
		$this->load->view('layout/wrapper', $data);
	}

	public function contact()
	{
        $data = array(
            'title'		=> 'Contact MIF - Money Industrial Factory',
            'content'	=> 'home/contact',
            'extra'	    => 'home/js/js_contact',
		);
		$this->load->view('layout/wrapper', $data);
	}

	public function confirm(){
	    $data = array(
            'title'		=> 'Confirm Registration | MIF - Money Industrial Factory',
            'content'	=> 'home/confirmemail',
		);
		$this->load->view('layout/wrapper', $data);	
    }
	
	public function individualmail(){
        $input		= $this->input;
        $data       = $this->security;
        
		$title	     = $data->xss_clean($input->post("title"));
		$firstname   = $data->xss_clean($input->post("firstname"));
		$lastname	 = $data->xss_clean($input->post("lastname"));
		$email	     = $data->xss_clean($input->post("email"));
		$phone	     = $data->xss_clean($input->post("phone"));
		$country	 = $data->xss_clean($input->post("country"));
		$language    = $data->xss_clean($input->post("language"));
		$prefcontact = $data->xss_clean($input->post("prefcontact"));
		$service	 = $data->xss_clean($input->post("service"));
        $message     = $data->xss_clean($input->post("message"));
        
		//init
		$subject="Ask about ".$service;
		$message="
		    <table border='0'>
    		    <tr><td>From </td><td>".$title." ".$firstname." ".$lastname."</td></tr> 
    		    <tr><td>Email </td><td>".$email."</td></tr> 
    		    <tr><td>Phone </td><td>".$phone."</td></tr> 
    		    <tr><td>Country </td><td>".$country."</td></tr> 
    		    <tr><td>Language </td><td>".$language."</td></tr> 
    		    <tr><td>Contact </td><td>".$prefcontact."</td></tr> 
    		    <tr><td>Message </td><td>".$message."</td></tr> 
		    </table>
		";
		
		$this->sendmail($email,$subject, $message,$firstname." ".$lastname);
		
		$subjectkonfirm="Inquiry for ".$service;
		$messagekonfirm="Thank you for your interest using our service<br><br>
		    Please allow 2 business day for our team to respond your email.
		";
		$this->notifback($email,$subjectkonfirm,$messagekonfirm);
		
		$errmessage="<h3>Message Sent</h3><br>Please allow 2 business day for our team to respond your email immediately";
        $this->session->set_flashdata('message', $errmessage);
		redirect(base_url()."home/confirm");
	}

	public function companymail(){
        $input		= $this->input;
        $data       = $this->security;
        //conpany
		$company	= $data->xss_clean($input->post("company"));
		$cemail     = $data->xss_clean($input->post("cemail"));
		$cphone	    = $data->xss_clean($input->post("cphone"));
		$ccountry	= $data->xss_clean($input->post("ccountry"));
        
        //contact person        
		$title	     = $data->xss_clean($input->post("cptitle"));
		$firstname   = $data->xss_clean($input->post("cpfirstname"));
		$lastname	 = $data->xss_clean($input->post("cplastname"));
		$email	     = $data->xss_clean($input->post("cpemail"));
		$phone	     = $data->xss_clean($input->post("cpphone"));
		$country	 = $data->xss_clean($input->post("cpcountry"));
		$language    = $data->xss_clean($input->post("cplanguage"));
		$prefcontact = $data->xss_clean($input->post("cpprefcontact"));
		$service	 = $data->xss_clean($input->post("cpservice"));
        $message     = $data->xss_clean($input->post("cpmessage"));
        
		//init
		$subject="Ask about ".$service;
		$message="
		    <table border='0'>
		        <tr><td colspan='2'><b>Company</b></td></tr>
    		    <tr><td>Company </td><td>".$company."</td></tr> 
    		    <tr><td>Email </td><td>".$cemail."</td></tr> 
    		    <tr><td>Phone </td><td>".$cphone."</td></tr> 
    		    <tr><td>Country </td><td>".$ccountry."</td></tr> 
		        <tr><td colspan='2'><br><b>Contact</b></td></tr>
    		    <tr><td>From </td><td>".$title." ".$firstname." ".$lastname."</td></tr> 
    		    <tr><td>Email </td><td>".$email."</td></tr> 
    		    <tr><td>Phone </td><td>".$phone."</td></tr> 
    		    <tr><td>Country </td><td>".$country."</td></tr> 
    		    <tr><td>Language </td><td>".$language."</td></tr> 
    		    <tr><td>Contact </td><td>".$prefcontact."</td></tr> 
    		    <tr><td>Message </td><td>".$message."</td></tr> 
		    </table>
		";

		$this->sendmail($email,$subject, $message,$firstname." ".$lastname);
		
		$subjectkonfirm="Inquiry for ".$service;
		$messagekonfirm="Thank you for your interest using our service<br><br>
		    Please allow 2 business day for our team to respond your email.
		";
		$this->notifback($email,$subjectkonfirm,$messagekonfirm);
		
		$errmessage="<h3>Message Sent</h3><br>Please allow 2 business day for our team to respond your email immediately";
        $this->session->set_flashdata('message', $errmessage);
		redirect(base_url()."home/confirm");
	}
	
	//send notifikasi email terkirim
	public function notifback($email, $subject, $message){	
		$mail = $this->phpmailer_lib->load();
				   
		$mail->isSMTP();
		$mail->Host			= 'mail.moneyindustrialfactory.io';
		$mail->SMTPAuth		= true;
		$mail->Username		= 'no-reply@moneyindustrialfactory.io';
		$mail->Password		= 'G7VfsqbnH}k}';
		$mail->SMTPAutoTLS	= false;
		$mail->SMTPSecure	= false;
		$mail->Port			= 587;           

		$mail->setFrom("no-reply@moneyindustrialfactory.io", "Money Industrial Factory");
		$mail->isHTML(true);

		$mail->ClearAllRecipients();
				 
				
		$mail->Subject = $subject;
		$mail->AddAddress($email);

        $mail->msgHTML($message);
		$mail->send();
	}

	
	public function sendmail($email, $subject, $message, $name){	
		$mail = $this->phpmailer_lib->load();
				   
		$mail->isSMTP();
		$mail->Host			= 'mail.moneyindustrialfactory.io';
		$mail->SMTPAuth		= true;
		$mail->Username		= 'no-reply@moneyindustrialfactory.io';
		$mail->Password		= 'G7VfsqbnH}k}';
		$mail->SMTPAutoTLS	= false;
		$mail->SMTPSecure	= false;
		$mail->Port			= 587;           

		$mail->setFrom($email, $name);
		$mail->isHTML(true);

		$mail->ClearAllRecipients();
				 
				
		$mail->Subject = $subject;
		$mail->AddAddress('principe@moneyindustrialfactory.io');
        $mail->AddCC('camilla@moneyindustrialfactory.io');
        $mail->AddCC('gede@moneyindustrialfactory.io');
        $mail->AddCC('Nico@moneyindustrialfactory.io');

        $mail->msgHTML($message);
		$mail->send();
	}
	
}
