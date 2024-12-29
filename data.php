<?php
require 'db.php';



if (isset($_POST['addToLibrary'])){
    $isbn = isset($_POST['isbn']) ? trim($_POST['isbn']) : '';
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if (!$isbn || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        exit;
    }

    try {
        // Check if the book is already in the user's library
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM library WHERE user = :user_id AND book = :isbn");
        $stmt->execute(['user_id' => $user_id, 'isbn' => $isbn]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'This book is already in your library.']);
        } else {
            // Add the book to the user's library
            $stmt = $pdo->prepare("INSERT INTO library (user, book) VALUES (:user_id, :isbn)");
            $stmt->execute(['user_id' => $user_id, 'isbn' => $isbn]);

            echo json_encode(['success' => true, 'message' => 'Book added to library.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}


