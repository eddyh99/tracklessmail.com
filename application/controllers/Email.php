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
			$this->session->set_flashdata('failed', 'anonymous mail has been Used, please try another id');
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
			$this->session->set_flashdata('failed', 'Failed to open anonymous mail, please try again');
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

		  .btn:hover{
			color: #000;
		  }
	  
		  .info {
			margin: .5rem 0;
			padding: 2rem 0;
			box-sizing: border-box;
		  }
	  </style>
		</head>
	
	  <body>
	  <div class="body">
		<div class="content">
		  <p>Activate the anonymous email by clicking the button below</p>
		  <img src="http://tracklessmail.com/assets/images/mail.png" alt="" class="img-mail"><br>
		  <div class="info">
			<span>
			To increase your privacy and to do not keep track of your IP address, the only way to check your email it will be just by using an email client such as Mozilla Thunderbird, Outlook express, Mail Android, etc...
			</span>
		  </div>
		</div>
		<div class="link">
		  <a href="' . base_url() . 'email/activate?key=' . urlencode(base64_encode($encrypted)) . '" class="btn">Activate email</a>
		</div>
	  </div>
	  </body>
	  </html>';

		$this->sendmail($email, $subject, $message);
		$this->session->set_flashdata('success', "Thank you for registering Anonymous Email, You need activate the email before use it.");

		redirect(base_url() . "auth/succes_regis");
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
					//init
					$subject = "Anonymous Email Configuration";
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

    .col-12 {
      width: 100%;
    }

    .content {
      width: 75%;
      max-width: 720px;
      margin: auto;
      font-size: 18px;
    }

    .info {
      margin: .5rem 0;
      padding: 1rem 0;
      box-sizing: border-box;
    }

    .w-25 {
      width: 25% !important;
    }

    .border-0 {
      border: 0 !important;
    }

    .table tr,
    .table tr>td {
      padding: 0.5rem 0.5rem;
    }

    .green {
      color: #00DD9C;
    }

    .green-bold {
      color: #00DD9C;
      font-weight: 700;
    }

    @media (max-width: 460px) {
      .content {
        width: 100%;
        max-width: 720px;
        margin: auto;
        font-size: 14px;
      }
    }
  </style>
</head>

<body>
  <div class="body">
    <div class="content">
      <p>These are your configuration data to enter on an email client :</p>
      <div class="info">
        <p class="green-bold">Configuration Data</p>
        <div class="table-responsive">
          <table class="table align-middle border border-0 text-white">
            <tbody>
              <tr>
                <td class="w-25 border border-0">Incoming Server</td>
                <td class="border border-0">:</td>
                <td class="border border-0"> mail.tracklessmail.com</td>
              </tr>
              <tr>
                <td class="w-25 border border-0">IMAP Port</td>
                <td class="border border-0">:</td>
                <td class="green border border-0"> 993
                </td>
              </tr>
              <tr>
                <td class="w-25 border border-0">POP3 Port</td>
                <td class="border border-0">:</td>
                <td class="green border border-0"> 995
                </td>
              </tr>
              <tr>
                <td class="w-25 border border-0">Outgoing Server</td>
                <td class="border border-0">:</td>
                <td class="border border-0"> mail.tracklessmail.com
                </td>
              </tr>
              <tr>
                <td class="w-25 border border-0">SMTP Port</td>
                <td class="border border-0">:</td>
                <td class="green border border-0"> 465
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>

</html>

            		';
					$newdata = array(
						"activate"  => 1,
						"timecreate" => date("Y-m-d H:i:s")
					);
					$this->emailmodel->activateMail($newdata, $email);
					$this->sendmail($email, $subject, $message);

					$this->session->set_flashdata('success', 'The email account has been created successfully !
					Your details configuration has been sent to your recovery mail, check it now');
					redirect(base_url() . "auth/info_activate?mail=" . substr($data[0], 0, -18) . '&email=' . $email);
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
				redirect(base_url() . "auth/index");
				return;
			}
		} catch (Exception $e) {
		}

		//redirect(base_url()."email/confirm");

		//}}} End - creating cpanel email account

	}

	public function resetpass()
	{
		$this->form_validation->set_rules(
			'anonmail',
			'Email',
			array(
				'trim',
				'required',
				array(
					'validate_anonmail',
					function ($str) {
						if (!empty($str)) {
							$i = $this->emailmodel->cek_valid_mail($str);
							if ($i) {
								return TRUE;
							} else {
								return FALSE;
							}
						}
					}
				)
			),
			array('validate_anonmail' => 'Account not found, please check it again')
		);
		$this->form_validation->set_rules('email', 'Email Recovery', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('failed', validation_errors());
			redirect(base_url() . "auth/resetpw");
			return;
		}

		$input		= $this->input;
		$email	    = $this->security->xss_clean($input->post("email"));
		$anonmail   = $this->security->xss_clean($input->post("anonmail"));
		$anonregis	= $anonmail . "@tracklessmail.com";
		if (!$this->emailmodel->cek_mail_recovery($anonregis, $email)) {
			$this->session->set_flashdata('failed', "Wrong recovery mail for this account");
			redirect(base_url() . "auth/resetpw");
			return;
		}

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
	  
		  .info {
			margin: .5rem 0;
			padding: 2rem 0;
			box-sizing: border-box;
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
			<span>
			  If you do not find the email check into your
			  spam folder
			</span>
		  </div>
		</div>
		<div class="link">
		  <a href="' . base_url() . 'email/recovery/' . base64_encode($anonregis) . '/' . $res['tempcode'] . '/ ' . $email . '" class="btn">Click here to reset your password</a>
		</div>
	  </div>
	  </body>
	  </html>';

		$this->sendmail($email, $subject, $message);

		$flashmessage = "A password reset email has been set to the email address related to your account. Please wait at least 10 minutes before attempting another reset.";

		$this->session->set_flashdata('success', $flashmessage);
		redirect(base_url() . "auth/index");
		return;
	}

	public function recovery($email, $code, $gmail)
	{
		$emailbaru = base64_decode($this->security->xss_clean($email));
		$code = $this->security->xss_clean($code);
		$gmail = $this->security->xss_clean($gmail);

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
			'gmail'      => $gmail,
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
		$gmail		= $this->security->xss_clean($input->post("gmail"));
		$code	    = $this->security->xss_clean($input->post("code"));
		$pass		= $this->security->xss_clean($input->post("pass"));
		$confpass	= $this->security->xss_clean($input->post("confirmpass"));

		// echo $anonmail . ' ' . $code . ' ' . $pass . ' ' . $confpass;
		// die;

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('failed', validation_errors());
			redirect(base_url() . "email/recovery/" . base64_encode($anonmail) . "/" . $code . '/' . $gmail);
			return;
		}


		if (!preg_match('/(?=[A-Za-z0-9!@#$%^&*\-_=+]+$)^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*(?:[^!@#$%^&*\-_=+]*[!@#$%^&*\-_=+]){2})(?=.{9,}).*$/', $pass)) {

			$this->session->set_flashdata('failed', "Invalid Password. Password should contain 1 Uppercase, 1 lowercase, 1 Numeric and 2 Special Charaters");
			redirect(base_url() . "email/recovery/" . base64_encode($anonmail) . "/" . $code . '/' . $gmail);
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
					redirect(base_url() . "auth/info_activate?mail=" . substr($anonmail, 0, -18) . "&email=" . $gmail);
				} else {
					if (strpos($result->cpanelresult->data[0]->reason, "it is too weak")) {
						$errmessage = "Your choosen password is to weak, please try again";
					} else {
						$errmessage = "Error while changing password. Please try again ";
					}
					$this->session->set_flashdata('failed', $errmessage);
					redirect(base_url() . "email/recovery/" . base64_encode($anonmail) . "/" . $code . '/' . $gmail);
					return;
				}
			} else {
				$errmessage = "failed to change password this email account.";
				$this->session->set_flashdata('failed', $errmessage);
				redirect(base_url() . "email/recovery/" . base64_encode($anonmail) . "/" . $code . '/' . $gmail);
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
		// $mail->SMTPDebug    = 2;
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
	
		.info {
		margin: .5rem 0;
		padding: 2rem 0;
		box-sizing: border-box;
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
			<span>
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
		$mail->AddAddress('mamugeming00@gmail.com');

		// $mail->msgHTML($message);
		$mail->send();
	}
}