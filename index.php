<?php 
    session_start();


   require 'db.php';
    
    if (isset($_SESSION['google_loggedin'])) {

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([ $_SESSION['google_id'] ]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        // Retrieve session variables
        $google_loggedin = $_SESSION['google_loggedin'];
        $google_email = $account['email'];
        $google_name = $account['name'];
        $google_picture = $account['picture'];
        $user_id = $account['id'];
    }

    $search = $_GET['q'];
    $search = str_replace(' ','+', $search);

    if (!isset($search)){
        $search = "";
    }

    $url= "https://www.googleapis.com/books/v1/volumes?q=$search";

    $json = file_get_contents($url);
    $bookData = json_decode($json);

    $books = $bookData->items;



?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    </head>
  <body>
    
    <a href="google-oauth.php" class="google-login-btn">
        <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 488 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/></svg>
        </span>
        Login with Google
    </a>

    <?php if (isset($_SESSION['google_loggedin'])) { ?>
    <a href="logout.php" class="google-login-btn">
        Logout
    </a>
    <?php } ?>

    <img src="<?=$google_picture?>" referrerpolicy="no-referrer">

    
    <form method="GET">
        <input name="q" id="q" placeholder="Search for a book" required></input>
        <input type="submit" value="Submit">
    </form>

    <div class="container text-center">
        <div class="row">
            <?php foreach ($books as $b): ?>
                <div class="col-sm-3">
                    <img src="<?=$b->volumeInfo->imageLinks->thumbnail?>">
                    <p><?=$b->volumeInfo->title?></p>
                    <?php
                        $isbn = $b->volumeInfo->industryIdentifiers[0]->identifier;
                        $title = $b->volumeInfo->title;
                    ?>
                    
                    <p><?=$isbn?></p>

                    <?php
                        $added = $pdo->prepare("SELECT count(*) as num FROM library WHERE user=".$user_id." AND book=".$isbn."");
                        $added->execute();
                        $add = $added->fetch(PDO::FETCH_ASSOC);

                        if ($add['num'] > 0):
                    ?>
                        <button class="add-to-library-btn" data-isbn="<?=$isbn?>" data-user="<?=$user_id?>">In library</button>
                    <?php else: ?>
                        <button class="add-to-library-btn" data-isbn="<?=$isbn?>" data-user="<?=$user_id?>">Add to Library</button>

                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="responseMessage">
                
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        const toastLiveExample = document.getElementById('liveToast')


        $(document).ready(function() {
       

            $('.add-to-library-btn').on('click', function() {
                const isbn = $(this).data('isbn');
                const userId = $(this).data('user'); // Replace with the actual logged-in user's ID
                const added = 1;
                
                $.ajax({
                    url: 'data.php',
                    type: 'POST',
                    data: { isbn: isbn, user_id: userId, addToLibrary: added},
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                $('#responseMessage').text('Book added to library!');
                                $('#liveToast').addClass("text-bg-success");
                                const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
                                toastBootstrap.show()
                            } else {
                                $('#responseMessage').text(result.message);
                                $('#liveToast').addClass("text-bg-secondary");
                                const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
                                toastBootstrap.show()
                            }
                        } catch (error) {
                            console.error('Error parsing response:', error);
                            $('#responseMessage').text('An unexpected error occurred.');
                            $('#liveToast').addClass("text-bg-warning");
                            const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
                            toastBootstrap.show()
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        $('#responseMessage').text('Failed to add book.');
                        $('#liveToast').addClass("text-bg-warning");
                        const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
                        toastBootstrap.show()
                    }
                });
            });
        });
    </script>
</body>
</html>




