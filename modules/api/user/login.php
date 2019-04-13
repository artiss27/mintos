<?php
$response['status'] = 'no';
if (isset($_POST['action'], $_POST['email'], $_POST['xsrf']) && $_POST['xsrf'] === $_SESSION['antixsrf']) {

    if ($_POST['action'] === 'login' && isset($_POST['password'])) {

        $auth = new \User\Authorization;
        if ($auth->authByField([
            'email' => $_POST['email'],
            'password' => trim($_POST['password'])
        ], true)) {
            $response['status'] = 'ok';
        } else {
            $response['error'] = $auth->errors;
        }

    } elseif ($_POST['action'] === 'register' && isset($_POST['password'], $_POST['name'])) {

        $reg = new \User\Registration;
        if ($reg->registByField([
            'email' => $_POST['email'],
            'password' => trim($_POST['password']),
            'name' => $_POST['name'],
        ])) {
            $auth = new \User\Authorization;
            $auth->authByField([
                'email' => $_POST['email'],
                'password' => trim($_POST['password'])
            ], true);
            $response['status'] = 'ok';
        } else {
            $response['error'] = $reg->error;
        }

    } elseif ($_POST['action'] === 'restore') {

        $reg = new \User\Registration;
        $reg->verifyEmail($_POST['email']);
        if (!empty($reg->error['email']) && $reg->error['email'] === 'Email is busy') {
            $reg->sendPassRecLink($_POST['email']);
            $response['status'] = 'ok';
        } else {
            $response['error']['email'] = 'This e-mail is not registered';
        }
    } else {
        $response['error'] = 'incorrect data sent';
    }
}
echo json_encode($response);
exit;
