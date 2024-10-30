<?php
session_start();
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $connection = new Connection();
    $pdo = $connection->OpenConnection();

    // Add product
    if (isset($_POST['add_product'])) {
        $stmt = $pdo->prepare("INSERT INTO products (product_name, category, price, quantity, product_availability, date) VALUES (:product_name, :category, :price, :quantity, :product_availability, :date)");
        $stmt->execute([
            ':product_name' => $_POST['product_name'],
            ':category' => $_POST['category'],
            ':price' => $_POST['price'],
            ':quantity' => $_POST['quantity'],
            ':product_availability' => $_POST['product_availability'],
            ':date' => $_POST['date'],
        ]);
        $_SESSION['message'] = "Product added successfully";
        header("Location: index.php");
        exit;
    }

    // Edit product
    if (isset($_POST['edit_product'])) {
        $stmt = $pdo->prepare("UPDATE products SET product_name = :product_name, category = :category, price = :price, quantity = :quantity, product_availability = :product_availability, date = :date WHERE id = :product_id");
        $stmt->execute([
            ':product_name' => $_POST['product_name'],
            ':category' => $_POST['category'],
            ':price' => $_POST['price'],
            ':quantity' => $_POST['quantity'],
            ':product_availability' => $_POST['product_availability'],
            ':date' => $_POST['date'],
            ':product_id' => $_POST['product_id'],
        ]);
        $_SESSION['message'] = "Product updated successfully";
        header("Location: index.php");
        exit;
    }

    // Delete product
    if (isset($_POST['delete_product'])) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :product_id");
        $stmt->execute([':product_id' => $_POST['product_id']]);
        $_SESSION['message'] = "Product deleted successfully";
        header("Location: index.php");
        exit;
    }

    // Add category
    if (isset($_POST['add_category'])) {
        $new_category = $_POST['new_category'];
        $stmt = $pdo->prepare("INSERT INTO category (category_name) VALUES (:new_category)");
        $stmt->execute([':new_category' => $new_category]);
        $_SESSION['message'] = "Category added successfully";
        header("Location: index.php");
        exit;
    }
}