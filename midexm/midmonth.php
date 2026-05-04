<?php
$conn = new mysqli("localhost", "root", "", "company");

/* ---------------- BRAND ADD ---------------- */
if(isset($_POST['add_brand'])){
    $name = $_POST['name'];
    $contact = $_POST['contact'];

    $conn->query("INSERT INTO brand(name, contact) VALUES('$name', '$contact')");
}

/* ---------------- PRODUCT ADD ---------------- */
if(isset($_POST['add_product'])){
    $pname = $_POST['pname'];
    $price = $_POST['price'];
    $brand_id = $_POST['brand_id'];
    $image = $_POST['image'];

    $conn->query("INSERT INTO products(name, price, brand_id, product_image)
                  VALUES('$pname', '$price', '$brand_id', '$image')");
}

/* ---------------- BRAND DELETE (TRIGGER WILL AUTO DELETE PRODUCTS) ---------------- */
if(isset($_POST['delete_brand'])){
    $bid = $_POST['brand_id'];

    // 👉 ONLY THIS LINE IS ENOUGH (trigger will handle product delete)
    $conn->query("DELETE FROM brand WHERE id = $bid");
}

/* ---------------- GET DATA ---------------- */
$brands = $conn->query("SELECT * FROM brand");
$products = $conn->query("SELECT * FROM product_view");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Brand Product System</title>
    <style>
        body{font-family:Arial; margin:30px;}
        .box{border:1px solid #ccc; padding:15px; margin-bottom:20px;}
        input,select{width:100%; padding:8px; margin:5px 0;}
        button{padding:10px; cursor:pointer;}
        table{width:100%; border-collapse:collapse;}
        th,td{border:1px solid #ccc; padding:10px;}
    </style>
</head>
<body>

<!-- BRAND ADD -->
<div class="box">
    <h2>Add Brand</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Brand Name" required>
        <input type="text" name="contact" placeholder="Contact" required>
        <button name="add_brand">Add Brand</button>
    </form>
</div>

<!-- PRODUCT ADD -->
<div class="box">
    <h2>Add Product</h2>
    <form method="POST">
        <input type="text" name="pname" placeholder="Product Name" required>
        <input type="text" name="price" placeholder="Price" required>

        <select name="brand_id">
            <option>Select Brand</option>
            <?php while($b = $brands->fetch_assoc()){ ?>
                <option value="<?= $b['id'] ?>"><?= $b['name'] ?></option>
            <?php } ?>
        </select>

        <input type="text" name="image" placeholder="Image">
        <button name="add_product">Add Product</button>
    </form>
</div>

<!-- DELETE BRAND (TRIGGER WILL HANDLE PRODUCT DELETE) -->
<div class="box">
    <h2>Delete Brand</h2>
    <form method="POST">
        <select name="brand_id">
            <option>Select Brand</option>
            <?php
            $brands2 = $conn->query("SELECT * FROM brand");
            while($b = $brands2->fetch_assoc()){ ?>
                <option value="<?= $b['id'] ?>"><?= $b['name'] ?></option>
            <?php } ?>
        </select>

        <button name="delete_brand" style="background:red;color:white;">
            Delete Brand (Auto Product Delete)
        </button>
    </form>
</div>

<!-- PRODUCT VIEW -->
<div class="box">
    <h2>Products</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Brand</th>
            <th>Image</th>
        </tr>

        <?php while($p = $products->fetch_assoc()){ ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= $p['product_name'] ?></td>
            <td><?= $p['price'] ?></td>
            <td><?= $p['brand_name'] ?></td>
            <td><?= $p['product_image'] ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>