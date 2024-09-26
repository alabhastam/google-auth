<?php
// Initialize the session
session_start();
// Update the following variables
$google_oauth_client_id = 'YOUR_CLIENT_ID';
$google_oauth_client_secret = 'YOUR_CLIENT_SECRET';
$google_oauth_redirect_uri = 'http://localhost/google-login/google-oauth.php';
$google_oauth_version = 'v3';


// If the captured code param exists and is valid
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Execute cURL request to retrieve the access token
    $params = [
        'code' => $_GET['code'],
        'client_id' => $google_oauth_client_id,
        'client_secret' => $google_oauth_client_secret,
        'redirect_uri' => $google_oauth_redirect_uri,
        'grant_type' => 'authorization_code'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response, true);
    // Code goes here...
} else {
    // Define params and redirect to Google Authentication page
    $params = [
        'response_type' => 'code',
        'client_id' => $google_oauth_client_id,
        'redirect_uri' => $google_oauth_redirect_uri,
        'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    header('Location: https://accounts.google.com/o/oauth2/auth?' . http_build_query($params));
    exit;
}


// Make sure access token is valid
if (isset($response['access_token']) && !empty($response['access_token'])) {
    // Execute cURL request to retrieve the user info associated with the Google account
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/' . $google_oauth_version . '/userinfo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
    $response = curl_exec($ch);
    curl_close($ch);
    $profile = json_decode($response, true);
    // Make sure the profile data exists
    if (isset($profile['email'])) {
        $google_name_parts = [];
        $google_name_parts[] = isset($profile['given_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['given_name']) : '';
        $google_name_parts[] = isset($profile['family_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['family_name']) : '';
        // Code goes here...
    } else {
        exit('Could not retrieve profile information! Please try again later!');
    }
} else {
    exit('Invalid access token! Please try again later!');
}

?>