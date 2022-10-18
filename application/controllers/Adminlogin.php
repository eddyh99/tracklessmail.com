<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Adminlogin extends CI_Controller {
    
	public function __construct() {
	   parent::__construct();
	   $this->load->model('mifmodel',"auth");
    }

	public function index()
	{
        $data = array(
            'title'		=> 'Admin MIF - Money Industrial Factory',
            'content'	=> 'login/index',
		);
		$this->load->view('layout/wrapper', $data);
	}

	public function auth_login(){
        $this->form_validation->set_rules('email', 'Email', 'trim|required');
        $this->form_validation->set_rules('pass', 'Password', 'trim|required');
		
		if ($this->form_validation->run() == FALSE){
		    $this->session->set_flashdata('message', "<p style='color:black'>".validation_errors()."</p'>");
		    redirect(base_url()."adminlogin");
            return;
		}

		$uname = $this->security->xss_clean($this->input->post('email'));
        $pass = $this->security->xss_clean($this->input->post('pass'));
		$mdata = array(
		    'email' => $uname,
		    'password' => sha1($pass)
		    );
		$result=$this->auth->get_single($mdata);

		if ($result){
			$session_data = array(
				'email'     => $result->email,
				'name'      => $result->name,
				'is_login'  => true
			);
			
			$this->session->set_userdata('logged_status', $session_data);
			redirect(base_url()."admin/member");
		}else{
		    $this->session->set_flashdata('message', "<p style='color:black'>Invalid username or password</p>");
		    redirect(base_url()."adminlogin");
            return;
		}
	}

	public function forgotpass() {
        $data = array(
            'title'     => 'Forgot Password - Money Industrial Factory',
            'is_login'  => false,
            'content'   => 'login/forgotpass',
		);
		$this->load->view('layout/wrapper', $data);
	}
	
	public function resetpass(){
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		if ($this->form_validation->run() == FALSE){
		    $this->session->set_flashdata('message', "<p style='color:black'>".validation_errors()."</p>");
		    redirect(base_url()."adminlogin");
            return;
		}

		$email = $this->security->xss_clean($this->input->post('email'));

		$result= $this->auth->cekemail($email);
		if (!isset($result)){
		    $this->session->set_flashdata('message', "<p style='color:black'>Email not registered</p>");
		    redirect(base_url()."adminlogin");
            return;
        }
        
        $subject="Forgot Password";
		
		$message="Hi,<br><br>

                  Someone has requested a new password for the following account on ".$email.":<br><br>

                  If you didn't make this request, just ignore this email. If you'd like to proceed:<br><br>

                  <a href='".base_url()."adminlogin/recovery?token=".$result."'>Click here to reset your password</a><br><br>

                  Thanks for reading.";
        
		$this->sendmail($email,$subject, $message);

		$this->session->set_flashdata('message', "<p style='color:black'>Reset password successfully sent to your email</p>");
	    redirect(base_url()."adminlogin");
        return;
	}

	public function recovery(){
	    $token=$_GET["token"];
	    $result=$this->auth->check_token($token);
        if (!isset($result)){
    		$this->session->set_flashdata('message', "<p style='color:black'>Invalid reset link</p>");
    	    redirect(base_url()."adminlogin/forgotpass");
            return;
        }
        
        $data = array(
            'title'		=> 'New Password - Money Industrial Factory',
            'content'	=> 'login/recovery',
            'email'     => $result->email,
            'token'     => $token
		);
		$this->load->view('layout/wrapper', $data);	
	}
	
    public function updatepassword(){
		$this->form_validation->set_rules('pass', 'Password', 'trim|required|min_length[9]|max_length[15]');
		$this->form_validation->set_rules('confirmpass', 'Confirm Password', 'trim|required|matches[pass]');

        $input		= $this->input;
		$email	    = $this->security->xss_clean($input->post("email"));
		$token	    = $this->security->xss_clean($input->post("token"));
		$pass		= $this->security->xss_clean($input->post("pass"));
		$confpass	= $this->security->xss_clean($input->post("confirmpass"));

		if ($this->form_validation->run() == FALSE){
		    $this->session->set_flashdata('message', "<p style='color:black'>".validation_errors()."</p>");
		    redirect(base_url()."auth/recovery?token=".$token);
            return;
		}
        
        $data=array(
                "password"  => sha1($pass)
            );
	    $result=$this->auth->updatepass($data,$email);
		$this->session->set_flashdata('message', "<p style='color:black'>Congratulations! Your password has been changed successfully</p>");
	    redirect(base_url()."adminlogin/");
    }

	public function logout() {
		$this->session->sess_destroy();
		redirect(base_url());
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
