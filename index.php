<?php 
    session_start();
    if (isset($_SESSION['google_loggedin'])) {
        require 'db.php';

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([ $_SESSION['google_id'] ]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        // Retrieve session variables
        $google_loggedin = $_SESSION['google_loggedin'];
        $google_email = $account['email'];
        $google_name = $account['name'];
        $google_picture = $account['picture'];
    }

    //$search = $_GET['q'];
    //$search = str_replace(' ','+', $search);

    $url= "https://www.googleapis.com/books/v1/volumes?q=harry+potter";

    $json = file_get_contents($url);
    $bookData = json_decode($json);

    $books = $bookData->items;


?>

<a href="logic/google-oauth.php" class="google-login-btn">
    <span class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 488 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/></svg>
    </span>
    Login with Google
</a>
<?php if (isset($_SESSION['google_loggedin'])) { ?>
<a href="logic/logout.php" class="google-login-btn">
    Logout
</a>
<?php } ?>

<img src="<?=$google_picture?>" referrerpolicy="no-referrer">

 
<form method="GET">
    <input name="q" id="q" placeholder="Search for a book" required></input>
    <input type="submit" value="Submit">
</form>

<?php foreach ($books as $b): ?>

    <img src="<?=$b->volumeInfo->imageLinks->thumbnail?>">
    <p><?=$b->volumeInfo->title?></p>

<?php endforeach; ?>


