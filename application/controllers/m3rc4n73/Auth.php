<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if ($this->session->userdata('user_id')) {
			redirect("m3rc4n73/dashboard");
		}

		$data = array(
			"title"     => "TracklessMail - Login",
			"content"   => "auth/login",
		);

		$this->load->view('tamplate/wrapper', $data);
	}

	public function login()
	{
		if ($this->session->userdata('user_id')) {
			redirect("m3rc4n73/dashboard");
		}

		$data = array(
			"title"     => "TracklessMail - Login",
			"content"   => "auth/login",
		);

		$this->load->view('tamplate/wrapper', $data);
	}

	public function auth_login()
	{
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('failed', validation_errors());
			redirect(base_url() . "m3rc4n73/auth/login");
			return;
		}

		$uname = $this->security->xss_clean($this->input->post('email'));
		$pass = $this->security->xss_clean($this->input->post('password'));

		$user = $this->db->get_where('pengguna', ['email' => $uname])->row_array();

		if ($user) {
			if (password_verify($pass, $user['password'])) {
				$data = [
					'name' => $user['name'],
					'id' => $user['id']
				];
				$this->session->set_userdata($data);

				redirect('m3rc4n73/dashboard');
			} else {
				$this->session->set_flashdata('message', 'Password Is not valid');
				redirect('m3rc4n73/auth/login');
			}
		} else {
			$this->session->set_flashdata('message', 'Unregistered Email');
			redirect('m3rc4n73/auth/login');
		}
	}
	public function logout()
	{
		$this->session->sess_destroy();
		redirect('');
	}
}