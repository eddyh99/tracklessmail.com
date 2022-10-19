<?php
defined('BASEPATH') or exit('No direct script access allowed');
define('ENCRYPTION_KEY', '__^%&Q@$&*!@#$%^&*^__');

class Email extends CI_Controller
{
	private $openssl;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('emailmodel');
		$this->openssl = new Openssl_EncryptDecrypt();

		//including congifuration files
		include_once APPPATH . "/third_party/config.php";
		//including third party xmlapi
		include_once APPPATH . "third_party/xmlapi.php";
	}

	public function registeranonymousemail()
	{
		@$_SESSION["location"] = "email/registeranonymousemail";
		if (@$_SESSION["site_lang"] == "italia") {
			redirect(base_url() . "it/email/registeranonymousemail");
		} elseif (@$_SESSION["site_lang"] == "french") {
			redirect(base_url() . "fr/email/registeranonymousemail");
		}

		$data = array(
			'title'		=> 'Anonymous Email | MIF - Money Industrial Factory',
			'content'	=> 'regis/cpemail',
			'extra'		=> 'regis/js/js_cpmail'
		);
		$this->load->view('layout/wrapper', $data);
	}

	public function confirm()
	{
		$data = array(
			'title'		=> 'Confirm Registration | MIF - Money Industrial Factory',
			'content'	=> 'regis/confirmregis',
		);
		$this->load->view('layout/wrapper', $data);
	}

	public function forgotpass()
	{
		$data = array(
			'title'		=> 'Forgot Password | MIF - Money Industrial Factory',
			'content'	=> 'regis/resetpass',
		);
		$this->load->view('layout/wrapper', $data);
	}

	public function checkaccount()
	{
		$this->form_validation->set_rules('username', 'Username', 'trim|required');
		if ($this->form_validation->run() == FALSE) {
			echo json_encode(array("code" => "5001", "message" => "Field Account is empty"));
		}

		$input		= $this->input;
		$anonmail	= $this->security->xss_clean($input->post("username"));
		$anonregis	= $anonmail . "@tracklessmail.com";
		$res		= $this->emailmodel->cekMail($anonregis);
		if ($res["code"] == 2021) {
			echo json_encode(array("code" => "2021", "message" => "Account is unavailable to registered"));
		} else {
			echo json_encode(array("code" => "0", "message" => ""));
		}
	}
	public function createemail()
	{
		//init
		$this->form_validation->set_rules('anonmail', 'Email', 'trim|required');
		$this->form_validation->set_rules('password1', 'Password', 'trim|required|min_length[9]|max_length[15]');
		$this->form_validation->set_rules('password2', 'Confirm Password', 'trim|required|matches[password1]');
		$this->form_validation->set_rules('emailrecovery', 'Email Recovery', 'valid_email|trim|required');
		$this->form_validation->set_rules('confirmemailrecovery', 'Confirm Email Recovery', 'trim|required|matches[emailrecovery]');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('failed', validation_errors());
			redirect(base_url() . "auth/index");
			return;
		}

		$input		= $this->input;
		$anonmail	= $this->security->xss_clean($input->post("anonmail"));
		$pass		= $this->security->xss_clean($input->post("password1"));
		$confpass	= $this->security->xss_clean($input->post("password2"));
		$email		= $this->security->xss_clean($input->post("emailrecovery"));
		$confemail	= $this->security->xss_clean($input->post("confirmemailrecovery"));

		$anonregis	= $anonmail . "@tracklessmail.com";
		$res		= $this->emailmodel->cekMail($anonregis);
		if ($res["code"] != 0) {
			$this->session->set_flashdata('failed', 'Mail sudah ada');
			redirect(base_url() . "auth/index");
			return;
		}

		if (!preg_match('/(?=[A-Za-z0-9!@#$%^&*\-_=+]+$)^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*(?:[^!@#$%^&*\-_=+]*[!@#$%^&*\-_=+]){2})(?=.{9,}).*$/', $pass)) {
			$this->session->set_flashdata('failed', "Invalid Password. Password should contain 1 Uppercase, 1 lowercase, 1 Numeric and 2 Special Charaters");
			redirect(base_url() . "auth/index");
			return;
		}

		$data = array(
			"anonmail"	=> $anonregis,
			"email"		=> $email
		);

		$result = $this->emailmodel->insertMail($data);
		if ($result["code"] == 2021) {
			$this->session->set_flashdata('failed', 'Gagal input');
			redirect(base_url() . "auth/index");
			return;
		}

		$string = $anonregis . "-" . $pass . "-" . $email;
		$encrypted = $this->openssl->encrypt($string, ENCRYPTION_KEY);

		//init
		$subject = "Anonymous Email Activation";
		$message = '
		
		<html>
		<head>
		<style>
		@import url("https://fonts.googleapis.com/css2?family=Poppins&display=swap");
	
		* {
		  box-sizing: border-box;
		  font-family: "Poppins";
		}
	
	
		.body {
		  width: 100%;
		  background: #000;
		  color: #fff;
		  padding: 1rem 1rem;
		  padding-bottom: 5rem;
		  height: auto;
		}
	
		.img-logo {
		  position: absolute;
		  top: 0;
		  left: 0;
		}
	
		
		.img-mail {
			width: 100%;
			max-width: 480px;
		}
	
		.col-12 {
		  width: 100%;
		}
	
		.content {
		  width: 75%;
		  max-width: 720px;
		  text-align: center;
		  margin: auto;
		  font-size: 18px;
		}
	
		.info {
		  display: flex;
		  justify-content: center;
		}
	
		.link {
		  width: 75%;
		  max-width: 720px;
		  margin: auto;
		  text-align: center;
		}
	
		.btn {
		  padding: 1rem 2rem;
		  background: #00DD9C;
		  border-radius: 10px;
		  text-decoration: none;
		  color: #000;
		}
	  </style>
		</head>
	
	  <body>
	  <div class="body">
		<div class="col-12">
		  <img src="http://tracklessmail.com/assets/images/logo-polos.png" alt="logo" width="150">
		</div>
		<div class="content">
		  <p>To activate the anonymous email please click the link received in your recovery mail</p>
		  <img src="http://tracklessmail.com/assets/images/mail.png" alt="" class="img-mail"><br>
		  <div class="info">
			<svg width="30" height="30" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg"
			  style="margin: 2rem 1rem;">
			  <circle cx="22.5" cy="22.5" r="22" stroke="#00DD9C" />
			  <path
				d="M22.9912 13.93L22.6532 26.54H20.1832L19.8452 13.93H22.9912ZM21.5092 32.182C20.9719 32.182 20.5212 32 20.1572 31.636C19.7932 31.272 19.6112 30.8213 19.6112 30.284C19.6112 29.7467 19.7932 29.296 20.1572 28.932C20.5212 28.568 20.9719 28.386 21.5092 28.386C22.0292 28.386 22.4712 28.568 22.8352 28.932C23.1992 29.296 23.3812 29.7467 23.3812 30.284C23.3812 30.8213 23.1992 31.272 22.8352 31.636C22.4712 32 22.0292 32.182 21.5092 32.182Z"
				fill="#00DD9C" />
			</svg>
			<span style="margin: .5rem 0; padding: 2rem 0;box-sizing: border-box; ">
			  If you do not find the email check into your
			  spam folder
			</span>
		  </div>
		</div>
		<div class="link">
		  <a href="' . base_url() . 'email/activate?key=' . urlencode(base64_encode($encrypted)) . '" class="btn">Activate account</a>
		</div>
	  </div>
	  </body>
	  </html>';

		$this->sendmail($email, $subject, $message);
		$this->session->set_flashdata('success', "Thank you for registering Anonymous Email, You need activate the email before use it.");

		redirect(base_url() . "auth/index");
	}

	public function activate()
	{
		$real = base64_decode($_GET["key"]);
		$data = explode("-", $this->openssl->decrypt($real, ENCRYPTION_KEY));

		$email = $data[2];

		$xmlapi = new xmlapi(SERVER_IP);
		$xmlapi->set_port(SERVER_PORT); // the ssl port for cpanel


		//{{{ Start - creating cpanel email account
		$email_user     = $data[0];  // if email address is mail@dmainname.com then email should be mail
		$email_password = $data[1]; // password to access email account in cpanel
		$email_quota    = '1024';  // email quota
		$email_domain   = 'tracklessmail.com';
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
					$subject = "Anonymous Email Configuration";
					$message = "
            		Here's your anonymous email configuration<br><br>
            		Incoming Server : mail.tracklessmail.com<br>
            		IMAP Port: 993 POP3 Port: 995<br><br>
            		Outgoing Server : mail.tracklessmail.com<br> 
            		SMTP Port: 465<br><br>
            		IMAP, POP3, and SMTP require authentication.
            		";
					$newdata = array(
						"activate"  => 1,
						"timecreate" => date("Y-m-d H:i:s")
					);
					$this->emailmodel->activateMail($newdata, $email);
					$this->sendmail($email, $subject, $message);
					$this->session->set_flashdata('success', $errmessage);
					redirect(base_url() . "auth/index");
					return;
				} else {
					if (strpos($result->cpanelresult->data[0]->reason, "it is too weak")) {
						$errmessage = "Your choosen password is to weak, please try again";
					} else {
						$errmessage = "Error while activating this email account. Please try again";
					}
					$this->session->set_flashdata('failed', $errmessage);
					redirect(base_url() . "auth/index");
					return;
				}
			} else {
				$errmessage = "Unable to create this email account.";
				$this->session->set_flashdata('failed', $errmessage);
			}
		} catch (Exception $e) {
		}

		//redirect(base_url()."email/confirm");

		//}}} End - creating cpanel email account

	}

	public function resetpass()
	{
		$this->form_validation->set_rules('anonmail', 'Email', 'trim|required');
		$this->form_validation->set_rules('email', 'Email Recovery', 'trim|required');
		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('failed', validation_errors());
			redirect(base_url() . "auth/resetpw");
			return;
		}

		$input		= $this->input;
		$anonmail	= $this->security->xss_clean($input->post("anonmail"));
		$anonregis	= $anonmail . "@tracklessmail.com";
		$res		= $this->emailmodel->resetMail($anonregis);
		if ($res["code"] == 2021) {
			$this->session->set_flashdata('failed', $res["message"]);
			redirect(base_url() . "auth/resetpw");
			return;
		}

		//send email reset
		$email = $res['email'];
		$subject = "Password Reset Request for " . $anonregis;
		$message = '
		
		<html>
		<head>
		<style>
		@import url("https://fonts.googleapis.com/css2?family=Poppins&display=swap");
	
		* {
		  box-sizing: border-box;
		  font-family: "Poppins";
		}
	
	
		.body {
		  width: 100%;
		  background: #000;
		  color: #fff;
		  padding: 1rem 1rem;
		  padding-bottom: 5rem;
		  height: auto;
		}
	
		.img-logo {
		  position: absolute;
		  top: 0;
		  left: 0;
		}
	
		
		.img-mail {
			width: 100%;
			max-width: 480px;
		}
	
		.col-12 {
		  width: 100%;
		}
	
		.content {
		  width: 75%;
		  max-width: 720px;
		  text-align: center;
		  margin: auto;
		  font-size: 18px;
		}
	
		.info {
		  display: flex;
		  justify-content: center;
		}
	
		.link {
		  width: 75%;
		  max-width: 720px;
		  margin: auto;
		  text-align: center;
		}
	
		.btn {
		  padding: 1rem 2rem;
		  background: #00DD9C;
		  border-radius: 10px;
		  text-decoration: none;
		  color: #000;
		}
	  </style>
		</head>
	
	  <body>
	  <div class="body">
		<div class="col-12">
		  <img src="http://tracklessmail.com/assets/images/logo-polos.png" alt="logo" width="150">
		</div>
		<div class="content">
		  <p>To activate the anonymous email please click the link received in your recovery mail</p>
		  <img src="http://tracklessmail.com/assets/images/mail.png" alt="" class="img-mail"><br>
		  <div class="info">
			<svg width="30" height="30" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg"
			  style="margin: 2rem 1rem;">
			  <circle cx="22.5" cy="22.5" r="22" stroke="#00DD9C" />
			  <path
				d="M22.9912 13.93L22.6532 26.54H20.1832L19.8452 13.93H22.9912ZM21.5092 32.182C20.9719 32.182 20.5212 32 20.1572 31.636C19.7932 31.272 19.6112 30.8213 19.6112 30.284C19.6112 29.7467 19.7932 29.296 20.1572 28.932C20.5212 28.568 20.9719 28.386 21.5092 28.386C22.0292 28.386 22.4712 28.568 22.8352 28.932C23.1992 29.296 23.3812 29.7467 23.3812 30.284C23.3812 30.8213 23.1992 31.272 22.8352 31.636C22.4712 32 22.0292 32.182 21.5092 32.182Z"
				fill="#00DD9C" />
			</svg>
			<span style="margin: .5rem 0; padding: 2rem 0;box-sizing: border-box; text-align: center;">
			  If you do not find the email check into your
			  spam folder
			</span>
		  </div>
		</div>
		<div class="link">
		  <a href="' . base_url() . 'email/recovery/' . base64_encode($anonregis) . '/' . $res['tempcode'] . ' class="btn">Click here to reset your password</a>
		</div>
	  </div>
	  </body>
	  </html>';

		$this->sendmail($email, $subject, $message);

		$flashmessage = "<hr>
            Password reset email has been sent
            <hr>
            A password reset email has been set to the email address related to your account. Please wait at least 10 minutes before attempting another reset.";

		$this->session->set_flashdata('success', $flashmessage);
		redirect(base_url() . "auth/index");
		return;
	}

	public function recovery($email, $code)
	{
		$emailbaru = base64_decode($this->security->xss_clean($email));
		$code = $this->security->xss_clean($code);

		// echo $this->openssl->decrypt($emailbaru, ENCRYPTION_KEY);
		// echo $emailbaru . " " . $email;
		// die;

		$res		= $this->emailmodel->cekcode($emailbaru, $code);
		if ($res["code"] == 2022) {
			$this->session->set_flashdata('failed', $res["message"]);
			redirect(base_url() . "auth/resetpw");
			return;
		}

		$data = array(
			'title'		=> 'New Password | Tracklessmail',
			'email'     => $emailbaru,
			'code'      => $code,
			'content'	=> 'home/recoverypass',
			'extra'     => 'home/js/js_index',
		);
		$this->load->view('layout/wrapper', $data);
	}

	public function updatepassword()
	{
		$this->form_validation->set_rules('pass', 'Password', 'trim|required|min_length[9]|max_length[15]');
		$this->form_validation->set_rules('confirmpass', 'Confirm Password', 'trim|required|matches[pass]');

		$input		= $this->input;
		$anonmail	= $this->security->xss_clean($input->post("anonemail"));
		$code	    = $this->security->xss_clean($input->post("code"));
		$pass		= $this->security->xss_clean($input->post("pass"));
		$confpass	= $this->security->xss_clean($input->post("confirmpass"));

		// echo $anonmail . ' ' . $code . ' ' . $pass . ' ' . $confpass;
		// die;

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('failed', validation_errors());
			redirect(base_url() . "email/recovery/" . base64_encode($anonmail) . "/" . $code);
			return;
		}


		if (!preg_match('/(?=[A-Za-z0-9!@#$%^&*\-_=+]+$)^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*(?:[^!@#$%^&*\-_=+]*[!@#$%^&*\-_=+]){2})(?=.{9,}).*$/', $pass)) {

			$this->session->set_flashdata('failed', "Invalid Password. Password should contain 1 Uppercase, 1 lowercase, 1 Numeric and 2 Special Charaters");
			redirect(base_url() . "email/recovery/" . base64_encode($anonmail) . "/" . $code);
			return;
		}

		// $decrypted = $this->openssl->decrypt($anonmail, ENCRYPTION_KEY);
		// $encrypted = $this->openssl->encrypt($anonmail, ENCRYPTION_KEY);

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
			// print_r($result);
			// die;
			if (isset($result->cpanelresult->data[0]->result)) {
				if ($result->cpanelresult->data[0]->result == '1') {
					$this->emailmodel->updateCode($anonmail);
					$errmessage = "Your Password is successfully changed, please relogin your anonymous email with current password";
					$this->session->set_flashdata('success', $errmessage);
					redirect(base_url() . "auth/index");
				} else {
					if (strpos($result->cpanelresult->data[0]->reason, "it is too weak")) {

						echo $this->form_validation->run() . 'sss';
						die;
						$errmessage = "Your choosen password is to weak, please try again";
					} else {
						$errmessage = "Error while changing password. Please try again ";
					}
					$this->session->set_flashdata('failed', $errmessage);
					redirect(base_url() . "email/recovery/" . base64_encode($anonmail) . "/" . $code);
					return;
				}
			} else {
				$errmessage = "failed to change password this email account.";
				$this->session->set_flashdata('failed', $errmessage);
				redirect(base_url() . "email/recovery/" . base64_encode($anonmail) . "/" . $code);
				return;
			}
		} catch (Exception $e) {
		}
	}

	public function hapusmail()
	{
		$list = $this->emailmodel->getExpired();
		$xmlapi = new xmlapi(SERVER_IP);
		$xmlapi->set_port(SERVER_PORT); // the ssl port for cpanel
		$xmlapi->password_auth(CPANEL_USER, CPANEL_PASSWORD);
		$xmlapi->set_output('json');
		$xmlapi->set_debug(0);
		try {
			foreach ($list as $dt) {
				$result = $xmlapi->api2_query(CPANEL_USER, "Email", "delpop", array(
					"email"     =>  $dt["anonmail"],
					"domain"    =>  "mifmail.vip"
				));

				$result = json_decode($result);
				$this->emailmodel->removeExpired($dt["anonmail"]);
			}
		} catch (Exception $e) {
		}
	}

	public function sendmail($email, $subject, $message)
	{
		$mail = $this->phpmailer_lib->load();

		$mail->isSMTP();
		$mail->Host			= 'mail.tracklessmail.com';
		$mail->SMTPAuth		= true;
		$mail->Username		= 'no-reply@tracklessmail.com';
		$mail->Password		= 'k]qo6uUroZ1k';
		$mail->SMTPAutoTLS	= true;
		$mail->SMTPSecure	= "tls";
		$mail->Port			= 587;
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$mail->setFrom('no-reply@tracklessmail.com', 'Trackless Mail');
		$mail->isHTML(true);

		$mail->ClearAllRecipients();


		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->IsHTML(true);
		$mail->AddAddress($email);

		// $mail->msgHTML($message);
		$mail->send();
	}

	public function testingsendmail()
	{
		$message = '
		
		<html>
		<head>
		<style>
		@import url("https://fonts.googleapis.com/css2?family=Poppins&display=swap");
	
		* {
		  box-sizing: border-box;
		  font-family: "Poppins";
		}
	
	
		.body {
		  width: 100%;
		  background: #000;
		  color: #fff;
		  padding: 1rem 1rem;
		  padding-bottom: 5rem;
		  height: auto;
		}
	
		.img-logo {
		  position: absolute;
		  top: 0;
		  left: 0;
		}
	
		
		.img-mail {
			width: 100%;
			max-width: 480px;
		}
	
		.col-12 {
		  width: 100%;
		}
	
		.content {
		  width: 75%;
		  max-width: 720px;
		  text-align: center;
		  margin: auto;
		  font-size: 18px;
		}
	
		.info {
		  display: flex;
		  justify-content: center;
		}
	
		.link {
		  width: 75%;
		  max-width: 720px;
		  margin: auto;
		  text-align: center;
		}
	
		.btn {
		  padding: 1rem 2rem;
		  background: #00DD9C;
		  border-radius: 10px;
		  text-decoration: none;
		  color: #000;
		}
	  </style>
		</head>
	
	  <body>
	  <div class="body">
		<div class="col-12">
		  <img src="http://tracklessmail.com/assets/images/logo-polos.png" alt="logo" width="150">
		</div>
		<div class="content">
		  <p>To activate the anonymous email please click the link received in your recovery mail</p>
		  <img src="http://tracklessmail.com/assets/images/mail.png" alt="" class="img-mail"><br>
		  <div class="info">
			<svg width="30" height="30" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg"
			  style="margin: 2rem 1rem;">
			  <circle cx="22.5" cy="22.5" r="22" stroke="#00DD9C" />
			  <path
				d="M22.9912 13.93L22.6532 26.54H20.1832L19.8452 13.93H22.9912ZM21.5092 32.182C20.9719 32.182 20.5212 32 20.1572 31.636C19.7932 31.272 19.6112 30.8213 19.6112 30.284C19.6112 29.7467 19.7932 29.296 20.1572 28.932C20.5212 28.568 20.9719 28.386 21.5092 28.386C22.0292 28.386 22.4712 28.568 22.8352 28.932C23.1992 29.296 23.3812 29.7467 23.3812 30.284C23.3812 30.8213 23.1992 31.272 22.8352 31.636C22.4712 32 22.0292 32.182 21.5092 32.182Z"
				fill="#00DD9C" />
			</svg>
			<span style="text-align: center; margin: .5rem 0; padding: 2rem 0;box-sizing: border-box; ">
			  If you do not find the email check into your
			  spam folder
			</span>
		  </div>
		</div>
		<div class="link">
		  <a href="" class="btn">Activate account</a>
		</div>
	  </div>
	  </body>
	  </html>';

		$mail = $this->phpmailer_lib->load();

		$mail->isSMTP();
		$mail->Host			= 'mail.tracklessmail.com';
		$mail->SMTPAuth		= true;
		$mail->Username		= 'no-reply@tracklessmail.com';
		$mail->Password		= 'k]qo6uUroZ1k';
		$mail->SMTPDebug    = 2;
		$mail->SMTPAutoTLS	= true;
		$mail->SMTPSecure	= "tls";
		$mail->Port			= 587;
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$mail->setFrom('no-reply@tracklessmail.com', 'Trackless Mail');
		$mail->isHTML(true);

		$mail->ClearAllRecipients();

		$mail->Subject = 'Anonymous Email Configuration';
		$mail->Body    = $message;
		$mail->IsHTML(true);
		$mail->AddAddress('adipurnama42@tracklessmail.com');

		// $mail->msgHTML($message);
		$mail->send();
	}
}