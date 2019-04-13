<?php
$response['status'] = 'no';
if (!empty($_POST['email'])) {
    $reg = new \User\Registration();
    if ($reg->verifyEmail($_POST['email'])) {
        $response['status'] = 'ok';
        $response['email'] = 'no';
    } else {
        $response['email'] = 'yes';
        $response['error'] = $reg->error;
    }
} else {
    $response['error']['email'] = 'The field must not be empty';
}
echo json_encode($response);
die();