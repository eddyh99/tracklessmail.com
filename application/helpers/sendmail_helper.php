<?php

function sendmail($php_mailer, $email, $subject, $message)
{
    $mail = $php_mailer;

    $mail->isSMTP();
    $mail->Host            = 'mail.tracklessmail.com';
    $mail->SMTPAuth        = true;
    $mail->Username        = 'no-reply@tracklessmail.com';
    $mail->Password        = 'k]qo6uUroZ1k';
    // $mail->SMTPDebug    = 2;
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
    $mail->Body    = $message;
    $mail->IsHTML(true);
    $mail->AddAddress($email);

    // $mail->msgHTML($message);
    $mail->send();
}