<?php
defined('BASEPATH') OR exit('No direct script access allowed');
define('ENCRYPTION_KEY', '__^%&Q@$&*!@#$%^&*^__');

class Email extends CI_Controller {
    private $openssl;
    
	public function __construct() {
	   parent::__construct();
	   $this->load->model('emailmodel');
	   $this->openssl=new Openssl_EncryptDecrypt();

    	//including congifuration files
    	include_once APPPATH."/third_party/config.php";
    	//including third party xmlapi
    	include_once APPPATH."third_party/xmlapi.php";	
    }

	public function registeranonymousemail(){
	    @$_SESSION["location"]="email/registeranonymousemail";
	    if (@$_SESSION["site_lang"]=="italia"){
	        redirect(base_url()."it/email/registeranonymousemail");
	    }elseif (@$_SESSION["site_lang"]=="french"){
	        redirect(base_url()."fr/email/registeranonymousemail");
	    }

        $data = array(
            'title'		=> 'Anonymous Email | MIF - Money Industrial Factory',
            'content'	=> 'regis/cpemail',
			'extra'		=> 'regis/js/js_cpmail'
		);
		$this->load->view('layout/wrapper', $data);	
	}
	
	
	public function confirm(){
	    $data = array(
            'title'		=> 'Confirm Registration | MIF - Money Industrial Factory',
            'content'	=> 'regis/confirmregis',
		);
		$this->load->view('layout/wrapper', $data);	
    }

	public function forgotpass(){
        $data = array(
            'title'		=> 'Forgot Password | MIF - Money Industrial Factory',
            'content'	=> 'regis/resetpass',
		);
		$this->load->view('layout/wrapper', $data);	
	}

    public function checkaccount(){
		$this->form_validation->set_rules('username', 'Username', 'trim|required');
		if ($this->form_validation->run() == FALSE){
		    echo json_encode(array("code"=>"5001", "message"=>"Field Account is empty"));
		}
		
        $input		= $this->input;
		$anonmail	= $this->security->xss_clean($input->post("username"));
		$anonregis	= $anonmail."@tracklessmail.com";
		$res		= $this->emailmodel->cekMail($anonregis);
		if ($res["code"]==2021){
		    echo json_encode(array("code"=>"2021", "message"=>"Account is unavailable to registered"));
		}else{
		    echo json_encode(array("code"=>"0", "message"=>""));
		}
    }
    public function createemail(){
        //init
		$this->form_validation->set_rules('username', 'Username', 'trim|required');
		$this->form_validation->set_rules('email', 'Email', 'valid_email|trim|required');
		$this->form_validation->set_rules('pass', 'Password', 'trim|required|min_length[9]|max_length[15]');
		$this->form_validation->set_rules('confirmpass', 'Confirm Password', 'trim|required|matches[pass]');

		if ($this->form_validation->run() == FALSE){
		    $this->session->set_flashdata('failed', validation_errors());
		    redirect(base_url()."email/registeranonymousemail");
            return;
		}

        $input		= $this->input;
		$anonmail	= $this->security->xss_clean($input->post("username"));
		$email		= $this->security->xss_clean($input->post("email"));
		$pass		= $this->security->xss_clean($input->post("pass"));
		$confpass	= $this->security->xss_clean($input->post("confirmpass"));

		$anonregis	= $anonmail."@tracklessmail.com";
		$res		= $this->emailmodel->cekMail($anonregis);
		if ($res["code"]!=0){
		    $this->session->set_flashdata('failed', $res["message"]);
		    redirect(base_url()."email/registeranonymousemail");
            return;
		}
		
		if (!preg_match('/(?=[A-Za-z0-9!@#$%^&*\-_=+]+$)^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*(?:[^!@#$%^&*\-_=+]*[!@#$%^&*\-_=+]){2})(?=.{9,}).*$/', $pass)){
		    $this->session->set_flashdata('failed', "Invalid Password. Password should contain 1 Uppercase, 1 lowercase, 1 Numeric and 2 Special Charaters");
		    redirect(base_url()."email/registeranonymousemail");
            return;
		}
		
		$data=array(
			"anonmail"	=> $anonregis,
			"email"		=> $email
		);

		$result=$this->emailmodel->insertMail($data);
		if ($result["code"]==2021){
		    $this->session->set_flashdata('failed',$result["message"]);
		    redirect(base_url()."email/registeranonymousemail");
            return;
		}
        
        $string=$anonregis."-".$pass."-".$email;
        $encrypted = $this->openssl->encrypt($string, ENCRYPTION_KEY);

		//init
		$subject="Anonymous Email Activation";
		$message="
		Thank you for registering Anonymous Email<br><br>

		You need activate the email before use it.<br><br>
		You can activate by clicking this <a href='".base_url()."email/activate?key=".urlencode(base64_encode($encrypted))."'>link</a><br><br>
		
		Email Configuration will be sent after activate the account.
		";

		$this->sendmail($email,$subject, $message);
		$this->session->set_flashdata('success',"Thank you for registering Anonymous Email, You need activate the email before use it.");
		redirect(base_url()."email/registeranonymousemail");
    }

	public function activate(){

	    $real=base64_decode($_GET["key"]);
        $data = explode("-",$this->openssl->decrypt($real, ENCRYPTION_KEY));

	    $email=$data[2];
        
        $xmlapi = new xmlapi(SERVER_IP);
        $xmlapi->set_port(SERVER_PORT); // the ssl port for cpanel
        

    	//{{{ Start - creating cpanel email account
        $email_user     = $data[0];  // if email address is mail@dmainname.com then email should be mail
        $email_password = $data[1]; // password to access email account in cpanel
        $email_quota    = '1024';  // email quota
        $email_domain   = 'mifmail.vip';
        $xmlapi->password_auth(CPANEL_USER, CPANEL_PASSWORD);
        $xmlapi->set_output('json');        
        $xmlapi->set_debug(0);
        try {
                $result = $xmlapi->api2_query(CPANEL_USER, "Email", "addpop", array(
                        "domain"    => $email_domain,
                        "email"     => $email_user,
                        "password"  => $email_password,
                        "quota"     => $email_quota
                    ));
    		$result = json_decode($result);

    		if (isset($result->cpanelresult->data[0]->result)) {		
    			if ($result->cpanelresult->data[0]->result == '1') {
    				$errmessage = "Email account created successfully, detail configuration has been sent to your email";
            		//init
            		$subject="Anonymous Email Configuration";
            		$message="
            		Here's your anonymous email configuration<br><br>
            		username : ".$data[0]."<br>
            		password : [your chosen password]<br><br>
            
            		Email Configuration<br>
            		Incoming Server : mail.mifmail.vip<br>
            		IMAP Port: 993 POP3 Port: 995<br><br>
            		Outgoing Server : mail.mifmail.vip<br> 
            		SMTP Port: 465<br><br>
            		IMAP, POP3, and SMTP require authentication.
            		<br><br>
            		Your Anonymous email remain active for 30 days, after that it's automatically deleted
            		";
            		
            		$newdata=array(
            		        "activate"  => 1,
            		        "timecreate"=> date("Y-m-d H:i:s")
            		    );
                    $this->emailmodel->activateMail($newdata,$email);
            		$this->sendmail($email,$subject, $message);
                    $this->session->set_flashdata('success', $errmessage);
    			} else {
    			    if (strpos($result->cpanelresult->data[0]->reason,"it is too weak")){
    			        $errmessage = "Your choosen password is to weak, please try again";
    			    }else{
        				$errmessage = "Error while activating this email account. Please try again";
    			    }
        		    $this->session->set_flashdata('failed', $errmessage);
        		    redirect(base_url()."email/registeranonymousemail");
                    return;
    			}
    		} else {
    				$errmessage = "Unable to create this email account.";
        		    $this->session->set_flashdata('failed', $errmessage);
    		}
    	}catch (Exception $e) {
    	    
        }
        
		//redirect(base_url()."email/confirm");

    	//}}} End - creating cpanel email account

	}

	public function resetpass(){
		$this->form_validation->set_rules('username', 'Username', 'trim|required');
		if ($this->form_validation->run() == FALSE){
		    $this->session->set_flashdata('failed', validation_errors());
		    redirect(base_url()."email/forgotpass");
            return;
		}

        $input		= $this->input;
		$anonmail	= $this->security->xss_clean($input->post("username"));
		$anonregis	= $anonmail."@mifmail.vip";
		$res		= $this->emailmodel->resetMail($anonregis);
		if ($res["code"]==2021){
		    $this->session->set_flashdata('failed', $res["message"]);
		    redirect(base_url()."email/forgotpass");
            return;
		}
		
		
		//send email reset
		$email=$res['email'];
		$subject="Password Reset Request for ".$anonregis;
		$message="Hi,<br><br>

                  Someone has requested a new password for the following account on ".$anonregis.":<br><br>

                  If you didn't make this request, just ignore this email. If you'd like to proceed:<br><br>

                  <a href='".base_url()."email/recovery/".base64_encode($anonregis)."/".$res['tempcode']."'>Click here to reset your password</a><br><br>

                  Thanks for reading.";

		$this->sendmail($email,$subject, $message);
        
        $flashmessage="<hr>
            Password reset email has been sent
            <hr>
            A password reset email has been set to the email address related to your account. Please wait at least 10 minutes before attempting another reset.";

	    $this->session->set_flashdata('success', $flashmessage);
	    redirect(base_url()."email/forgotpass");
        return;
	}
	
	public function recovery($email, $code){
	    $email=base64_decode($this->security->xss_clean($email));
	    $code=$this->security->xss_clean($code);
        
		$res		= $this->emailmodel->cekcode($email,$code);
		if ($res["code"]==2022){
		    $this->session->set_flashdata('failed', $res["message"]);
		    redirect(base_url()."email/forgotpass");
            return;
		}
		
        $data = array(
            'title'		=> 'New Password | MIF - Money Industrial Factory',
            'email'     => $email,
            'code'      => $code,
            'content'	=> 'regis/recovery',
			'extra'		=> 'regis/js/js_recovery'
		);
		$this->load->view('layout/wrapper', $data);	
           
	}
	
    public function updatepassword(){
		$this->form_validation->set_rules('pass', 'Password', 'trim|required|min_length[9]|max_length[15]');
		$this->form_validation->set_rules('confirmpass', 'Confirm Password', 'trim|required|matches[pass]');

        $input		= $this->input;
		$anonmail	= $this->security->xss_clean($input->post("anonemail"));
		$code	    = $this->security->xss_clean($input->post("code"));
		$pass		= $this->security->xss_clean($input->post("pass"));
		$confpass	= $this->security->xss_clean($input->post("confirmpass"));

		if ($this->form_validation->run() == FALSE){
		    $this->session->set_flashdata('failed', validation_errors());
		    redirect(base_url()."email/recovery/".$anonmail."/".$code);
            return;
		}

		if (!preg_match('/(?=[A-Za-z0-9!@#$%^&*\-_=+]+$)^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*(?:[^!@#$%^&*\-_=+]*[!@#$%^&*\-_=+]){2})(?=.{9,}).*$/', $pass)){
		    $this->session->set_flashdata('failed', "Invalid Password. Password should contain 1 Uppercase, 1 lowercase, 1 Numeric and 2 Special Charaters");
		    redirect(base_url()."email/recovery/".$anonmail."/".$code);
            return;
		}
        
        $xmlapi = new xmlapi(SERVER_IP);
        $xmlapi->set_port(SERVER_PORT); // the ssl port for cpanel
        

    	//{{{ Start - creating cpanel email account
        $email_user     = $anonmail;  // if email address is mail@dmainname.com then email should be mail
        $email_password = $pass; // password to access email account in cpanel
        $email_domain   = 'tracklessmail.com';

        $xmlapi->password_auth(CPANEL_USER, CPANEL_PASSWORD);
        $xmlapi->set_output('json');        
        $xmlapi->set_debug(0);
        try {
                $result = $xmlapi->api2_query(CPANEL_USER, "Email", "passwdpop", array( 
                    'domain'    => $email_domain, 
                    'email'     => $email_user, 
                    'password'  => $email_password
                    ));

    		$result = json_decode($result);
    		if (isset($result->cpanelresult->data[0]->result)) {		
    			if ($result->cpanelresult->data[0]->result == '1') {
            		$this->emailmodel->updateCode($anonmail);
    				$errmessage = "Your Password is successfully changed, please relogin your anonymous email with current password";
                    $this->session->set_flashdata('success', $errmessage);
            		redirect(base_url()."email/registeranonymousemail");
                } else {
    			    if (strpos($result->cpanelresult->data[0]->reason,"it is too weak")){
    			        $errmessage = "Your choosen password is to weak, please try again";
    			    }else{
        				$errmessage = "Error while changing password. Please try again ";
    			    }
        		    $this->session->set_flashdata('failed', $errmessage);
        		    redirect(base_url()."email/recovery/".$anonmail."/".$code);
                    return;
    			}
    		} else {
    				$errmessage = "failed to change password this email account.";
        		    $this->session->set_flashdata('failed', $errmessage);
        		    redirect(base_url()."email/recovery/".$anonmail."/".$code);
                    return;
    		}
    	}catch (Exception $e) {
    	    
        }
        

    }
    
    public function hapusmail(){
        $list=$this->emailmodel->getExpired();
        $xmlapi = new xmlapi(SERVER_IP);
        $xmlapi->set_port(SERVER_PORT); // the ssl port for cpanel
        $xmlapi->password_auth(CPANEL_USER, CPANEL_PASSWORD);
        $xmlapi->set_output('json');        
        $xmlapi->set_debug(0);
        try {
            foreach ($list as $dt){
                $result = $xmlapi->api2_query(CPANEL_USER, "Email", "delpop", array(
                    "email"     =>  $dt["anonmail"], 
                    "domain"    =>  "mifmail.vip"
                ));
                
    		$result = json_decode($result);
            $this->emailmodel->removeExpired($dt["anonmail"]);
            }
    	}catch (Exception $e) {

        }
    }
    
	public function sendmail($email,$subject, $message){	
		$mail = $this->phpmailer_lib->load();
				   
		$mail->isSMTP();
		$mail->Host			= 'mail.tracklessmail.com';
		$mail->SMTPAuth		= true;
		$mail->Username		= 'no-reply@tracklessmail.com';
		$mail->Password		= '';
		$mail->SMTPAutoTLS	= false;
		$mail->SMTPSecure	= false;
		$mail->Port			= 587;           

		$mail->setFrom('no-reply@tracklessmail.com', 'Trackless Mail');
		$mail->isHTML(true);

		$mail->ClearAllRecipients();
				 
				
		$mail->Subject = $subject;
		$mail->AddAddress($email);

        $mail->msgHTML($message);
		$mail->send();
	}
}
