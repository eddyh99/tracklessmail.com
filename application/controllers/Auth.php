<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function __construct() {
	   parent::__construct();

    }

	public function index()
	{
        $data = array(
            'title'     => 'Trackless Mail',
            'content'   => 'home/index',
            'extra'     => 'home/js/js_index',
		);
		$this->load->view('layout/wrapper', $data);
	}
	
	public function resetpw()
	{
        $data = array(
            'title'     => 'Trackless Mail - Reset Password',
            'content'   => 'home/resetpw',
		);
		$this->load->view('layout/wrapper', $data);
	}
}