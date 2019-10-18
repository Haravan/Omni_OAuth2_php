<?php
require __DIR__ . '/vendor/autoload.php';
define("url_connect_authorize", 'https://accounts.hara.vn/connect/authorize');
define("url_connect_token", 'https://accounts.hara.vn/connect/token');
define("url_get_user_info", 'https://accounts.hara.vn/connect/userinfo');
define("client_id", '434ff8a66d215c7f5a77c979d22cd866');
define("clientSecret", '996e37985efbb9b2dc4804931b33120c2c9a21f097d283229d62c18bbf71099f');
define("redirect_uri", "http://localhost/hara_account/");

$code = array_key_exists("code", $_POST) ? $_POST["code"] : "";


if ($code === "") {
    redirectLogin();
} else {
    getAccessToken();
}


function redirectLogin()
{
    $data = array(
        "response_mode" => "form_post",
        "response_type" => "code id_token",
        "scope" => "openid profile email org userinfo",
        "client_id" => client_id,
        "redirect_uri" => redirect_uri,
        "nonce" => "kcjqhdltd"
    );

    $query = "";
    foreach ($data as $key => $value) {
        $query .= $key . '=' . rawurlencode($value) . "&";
    }
    $query = substr($query, 0, -1);
    $url_account = url_connect_authorize . "?" . $query;
    header('Location: ' . $url_account);
}


function getAccessToken()
{
    $provider = new \League\OAuth2\Client\Provider\GenericProvider([
        'clientId'                => client_id,    // The client ID assigned to you by the provider
        'clientSecret'            => clientSecret,   // The client password assigned to you by the provider
        'redirectUri'             => redirect_uri,
        'urlAuthorize'            => url_connect_authorize,
        'urlAccessToken'          => url_connect_token,
        'urlResourceOwnerDetails' => url_get_user_info
    ]);

    try {
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_POST['code']
        ]);

        echo 'Access Token: ' . $accessToken->getToken() . "<br>";
        echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
        echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
        $id_token = $accessToken->getValues()["id_token"];
        $jwtPayload = parseToken($id_token);
        var_dump($jwtPayload);
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        exit($e->getMessage());
    }
}

function parseToken($id_token) {
    $tokenParts = explode(".", $id_token);
    $tokenHeader = base64_decode($tokenParts[0]);
    $tokenPayload = base64_decode($tokenParts[1]);
    $jwtHeader = json_decode($tokenHeader);
    $jwtPayload = json_decode($tokenPayload);
    return $jwtPayload;
}
