<?php
// Initialize the session
session_start();

// Include the Facebook SDK
require '/google-api-client/vendor/autoload.php';
// Include database connect
require 'db.php';
// Update the following variables
$google_oauth_client_id = env('oauth_client_id');
$google_oauth_client_secret = env('oauth_client_secret');
$google_oauth_redirect_uri = 'http://localhost/book-tracker/logic/google-oauth.php';
$google_oauth_version = 'v3';
// Create the Google Client object
$client = new Google_Client();
$client->setClientId($google_oauth_client_id);
$client->setClientSecret($google_oauth_client_secret);
$client->setRedirectUri($google_oauth_redirect_uri);
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
$client->addScope("https://www.googleapis.com/auth/userinfo.profile");
// If the captured code param exists and is valid
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Exchange the one-time authorization code for an access token
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($accessToken);
    // Make sure access token is valid
    if (isset($accessToken['access_token']) && !empty($accessToken['access_token'])) {
        // Now that we have an access token, we can fetch the user's profile data
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        // Make sure the profile data exists
        if (isset($google_account_info->email)) {    
            // Check if the account exists in the database
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([ $google_account_info->email ]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            // If the account does not exist in the database, insert the account into the database
            if (!$account) {
                $stmt = $pdo->prepare('INSERT INTO users (email, name, picture, registered, method) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([ $google_account_info->email, $google_account_info->name, isset($google_account_info->picture) ? $google_account_info->picture : '', date('Y-m-d H:i:s'), 'google' ]);
                $id = $pdo->lastInsertId();
            } else {
                $id = $account['id'];
            }
            // Authenticate the account
            session_regenerate_id();
            $_SESSION['google_loggedin'] = TRUE;
            $_SESSION['google_id'] = $id;
            // Redirect to profile page
            header('Location: ../index.php');
            exit;
        } else {
            exit('Could not retrieve profile information! Please try again later!');
        }
    } else {
        exit('Invalid access token! Please try again later!');
    }
} else {
    // Redirect to Google Authentication page
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}
?>