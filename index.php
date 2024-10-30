<?php
session_start();
include('filter.php');
include('connection.php');

$connection = new Connection();
$pdo = $connection->OpenConnection();

// Fetch categories for dropdown
$categoryStmt = $pdo->query("SELECT * FROM category");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products
$result = [];
if (isset($_POST['search'])) {
    $product_name = $_POST['product_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $product_availability = $_POST['product_availability'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    $sql = "SELECT p.*, c.category_name FROM products p INNER JOIN category c ON p.category = c.category_id WHERE 1";

    if (!empty($product_name)) {
        $sql .= " AND p.product_name LIKE :product_name";
    }
    if (!empty($category)) {
        $sql .= " AND p.category = :category";
    }
    if (!empty($product_availability)) {
        $sql .= " AND p.product_availability = :product_availability";
    }
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " AND p.date BETWEEN :start_date AND :end_date";
    }

    $sql .= " ORDER BY p.id ASC";

    $stmt = $pdo->prepare($sql);
    if (!empty($product_name)) {
        $stmt->bindValue(':product_name', "%" . $product_name . "%");
    }
    if (!empty($category)) {
        $stmt->bindValue(':category', $category);
    }
    if (!empty($product_availability)) {
        $stmt->bindValue(':product_availability', $product_availability);
    }
    if (!empty($start_date) && !empty($end_date)) {
        $stmt->bindValue(':start_date', $start_date);
        $stmt->bindValue(':end_date', $end_date);
    }

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT p.*, c.category_name FROM products p INNER JOIN category c ON p.category = c.category_id");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $new_category = $_POST['new_category'];
        $stmt = $pdo->prepare("INSERT INTO category (category_name) VALUES (:new_category)");
        $stmt->execute([':new_category' => $new_category]);
        $_SESSION['message'] = "Category added successfully";
        header("Location: index.php");
        exit;
    }

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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: gray;
            color: gold;
        }
        h1 {
            text-align: center;
            padding-top: 30px;
            font-family: "Century Gothic";
            font-weight: bolder;
            color: gold;
        }
        #carl {
            background-color: maroon;
            border-radius: 20px;
        }
        .btn-outline-info {
            margin-top: 5px;
        }
        .modal-dialog {
            color: gray;
        }
        .modal-content {
            color: maroon;
        }
        .btn-outline-primary {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<h1>A DAY IN A LIFE OF AN IT STUDENT - JAMES AMIT</h1>

<div class="container border border-dark mt-5" id="carl">
    <br>
    <form class="row g-3" method="POST">
        <div class="col-md-6">
            <label for="start_date" class="form-label">Start Date:</label>
            <input type="date" name="start_date" class="form-control" id="start_date">
        </div>
        <div class="col-md-6">
            <label for="end_date" class="form-label">End Date:</label>
            <input type="date" name="end_date" class="form-control" id="end_date">
        </div>
        <div class="col-md-6">
            <label for="product_name" class="form-label">Product Name</label>
            <input type="text" class="form-control" name="product_name" id="product_name">
        </div>
        <div class="col-md-6">
            <label for="category" class="form-label">Category</label>
            <select class="form-select" name="category">
                <option value="">All</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id']; ?>"><?= $cat['category_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6">
            <label for="product_availability" class="form-label">Product Availability:</label>
            <select class="form-select" name="product_availability">
                <option value="">All</option>
                <option value="In Stock">In Stock</option>
                <option value="Out of Stock">Out of Stock</option>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" name="search" class="btn btn-outline-primary">Filter</button>
            <button type="button" class="btn btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add Category</button>
        </div>
    </form>
    <br>
</div>

<div class="container">
    <div class="col-12 mt-3 d-flex justify-content-end">
        <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#addModal">Add Product</button>
    </div>
    <table class="table table-dark table-hover">
        <thead>
            <tr>
                <th scope="col">Id</th>
                <th scope="col">Product Name</th>
                <th scope="col">Category</th>
                <th scope="col">Price</th>
                <th scope="col">Quantity</th>
                <th scope="col">Product Availability</th>
                <th scope="col">Date</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row): ?>
                <tr>
                    <th scope="row"><?= $row['id']; ?></th>
                    <td><?= $row['product_name']; ?></td>
                    <td><?= $row['category_name']; ?></td>
                    <td><?= $row['price']; ?></td>
                    <td><?= $row['quantity']; ?></td>
                    <td><?= $row['product_availability']; ?></td>
                    <td><?= $row['date']; ?></td>
                    <td>
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">Delete</button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="crud.php" method="post">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <div class="mb-3">
                                        <label for="product_name" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" name="product_name" value="<?= $row['product_name'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" name="category">
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['category_id']; ?>" <?= $cat['category_id'] == $row['category'] ? 'selected' : ''; ?>>
                                                    <?= $cat['category_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <input type="number" class="form-control" name="price" value="<?= $row['price'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="quantity" value="<?= $row['quantity'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product_availability" class="form-label">Availability</label>
                                        <select class="form-select" name="product_availability">
                                            <option value="In Stock" <?= $row['product_availability'] == 'In Stock' ? 'selected' : ''; ?>>In Stock</option>
                                            <option value="Out of Stock" <?= $row['product_availability'] == 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="date" class="form-control" name="date" value="<?= $row['date'] ?>" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="edit_product" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete this product?
                            </div>
                            <div class="modal-footer">
                                <form action="crud.php" method="post">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="delete_product" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="crud.php" method="post">
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="product_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" name="category" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category_id']; ?>"><?= $cat['category_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_availability" class="form-label">Availability</label>
                        <select class="form-select" name="product_availability" required>
                            <option value="In Stock">In Stock</option>
                            <option value="Out of Stock">Out of Stock</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="new_category" class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="new_category" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="container mt-3 d-flex justify-content-end">
    <a href="logout.php" class="btn btn-outline-warning">Logout</a>
</div>


    


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>