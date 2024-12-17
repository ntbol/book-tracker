<?php 

    $search = $_GET['q'];
    $search = str_replace(' ','+', $search);

    $url= "https://www.googleapis.com/books/v1/volumes?q=$search";

    $json = file_get_contents($url);
    $bookData = json_decode($json);

    $books = $bookData->items;

    $thumbnail = $bookData->items[0]->volumeInfo->imageLinks->thumbnail;
?>

<img src="<?=$thumbnail?>">
 
<form method="GET">
    <input name="q" id="q" placeholder="Search for a book" required></input>
    <input type="submit" value="Submit">
</form>

<?php foreach ($books as $b): ?>

    <p><?=$b->volumeInfo->title?></p>

<?php endforeach; ?>


