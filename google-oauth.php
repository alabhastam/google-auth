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
if (isset($profile['email'])) {
    $google_name_parts = [];
    $google_name_parts[] = isset($profile['given_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['given_name']) : '';
    $google_name_parts[] = isset($profile['family_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['family_name']) : '';
    // Check if the account exists in the database
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = ?');
    $stmt->execute([ $profile['email'] ]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    // If the account does not exist in the database, insert the account into the database
    if (!$account) {
        $stmt = $pdo->prepare('INSERT INTO accounts (email, name, picture, registered, method) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([ $profile['email'], implode(' ', $google_name_parts), isset($profile['picture']) ? $profile['picture'] : '', date('Y-m-d H:i:s'), 'google' ]);
        $id = $pdo->lastInsertId();
    } else {
        $id = $account['id'];
    }
    // Authenticate the account
    session_regenerate_id();
    $_SESSION['google_loggedin'] = TRUE;
    $_SESSION['google_id'] = $id;
    // Redirect to profile page
    header('Location: profile.php');
    exit;
} else {
    exit('Could not retrieve profile information! Please try again later!');
}


// Authenticate the user
session_regenerate_id();
$_SESSION['google_loggedin'] = TRUE;
$_SESSION['google_email'] = $profile['email'];
$_SESSION['google_name'] = implode(' ', $google_name_parts);
$_SESSION['google_picture'] = isset($profile['picture']) ? $profile['picture'] : '';





?>