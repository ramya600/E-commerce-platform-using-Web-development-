<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch();

    if ($item) {
        $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?")
             ->execute([$user_id, $product_id]);
    } else {
        $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)")
             ->execute([$user_id, $product_id]);
    }
}

// Update quantity
if (isset($_POST['update_quantity'])) {
    $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?")
         ->execute([$_POST['quantity'], $_POST['cart_id'], $user_id]);
}

// Remove item
if (isset($_POST['remove_from_cart'])) {
    $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")
         ->execute([$_POST['cart_id'], $user_id]);
}

// Fetch cart
$stmt = $conn->prepare("SELECT cart.id AS cart_id, products.name, products.price, cart.quantity 
                        FROM cart JOIN products ON cart.product_id = products.id 
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
</head>
<body>

<h2>Your Cart</h2>
<a href="../index.php">⬅ Back to Shop</a>

<?php if (empty($items)): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
<table border="1" cellpadding="10">
<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Action</th></tr>
<?php $total = 0; foreach ($items as $item): $total += $item['price'] * $item['quantity']; ?>
<tr>
    <td><?= $item['name'] ?></td>
    <td>$<?= $item['price'] ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1">
            <button name="update_quantity">Update</button>
        </form>
    </td>
    <td>$<?= $item['price'] * $item['quantity'] ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
            <button name="remove_from_cart">Remove</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<h3>Total: $<?= $total ?></h3>

<?php endif; ?>

</body>
</html>
