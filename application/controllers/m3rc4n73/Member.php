<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Member extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Member_model');
        if (empty($this->session->userdata('id'))) {
            redirect(base_url());
        }
    }

    public function index()
    {
        $data = array(
            "title"     => "TracklessBank - Member",
            "content"   => "admin/member/member",
            "mn_member" => "active",
            "extra"     => "admin/member/js/js_member"
        );

        $this->load->view('admin_template/wrapper', $data);
    }

    public function get_all()
    {
        $result = $this->Member_model->getMember();

        if (@$result) {
            $data["member"] = $result;
        } else {
            $data["member"] = NULL;
        }
        echo json_encode($data);
    }

    public function sendmail()
    {
        $result = $this->Member_model->getMember();
        if (@$result) {
            $member = $result;
        } else {
            $member = NULL;
        }
        $data = array(
            "title"     => "Freedybank - Send Email",
            "content"   => "admin/member/sendmail",
            "mn_member" => "active",
            "member" => $member,
            "extra"     => "admin/member/js/js_email"
        );


        $this->load->view('admin_template/wrapper', $data);
    }

    public function sendmail_proses()
    {
        $input      = $this->input;
        $email      = $this->security->xss_clean($input->post("tujuan"));
        $all        = $this->security->xss_clean($input->post("all"));
        $message    = $this->security->xss_clean($input->post("message"));
        $subject    = $this->security->xss_clean($input->post("subject"));
        if ($all == "all") {
            $result = $this->Member_model->getMember();
            $member = array();
            foreach ($result as $dt) {
                $temp["email"] = $dt['email'];
                array_push($member, $temp);
            }
            $this->send_email($member, $subject, $message);
            $this->session->set_flashdata('success', "Email is successfully schedule to send");
            redirect(base_url() . "m3rc4n73/member/sendmail");
            return;
        } else {
            $this->send_email($email, $subject, $message);
            $this->session->set_flashdata('success', "Email is successfully schedule to send");
            redirect(base_url() . "m3rc4n73/member/sendmail");
            return;
        }
    }

    public function send_email($email, $subject, $message)
    {
        $mail = $this->phpmailer_lib->load();

        $mail->isSMTP();
        $mail->Host            = 'mail.tracklessmail.com';
        $mail->SMTPAuth        = true;
        $mail->Username        = 'no-reply@tracklessmail.com';
        $mail->Password        = 'k]qo6uUroZ1k';
        $mail->SMTPDebug    = 2;
        $mail->SMTPAutoTLS    = true;
        $mail->SMTPSecure    = "tls";
        $mail->Port            = 587;
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
        foreach ($email as $dt) {
            $mail->AddAddress($dt);
        }

        $mail->msgHTML($message);
        $mail->send();
    }
}