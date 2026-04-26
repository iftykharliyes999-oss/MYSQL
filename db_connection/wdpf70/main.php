<?php
$conn = new mysqli("localhost", "root", "", "wdpf70");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* INSERT MANUFACTURER */
if(isset($_POST['add_manufacturer'])){
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];

    $stmt = $conn->prepare("CALL add_manufacturer(?,?,?)");
    $stmt->bind_param("sss", $name, $address, $contact);
    $stmt->execute();

    $stmt->close();
    $conn->next_result();
}

/* INSERT PRODUCT */
if(isset($_POST['add_product'])){
    $name = $_POST['pname'];
    $price = $_POST['price'];
    $mid = $_POST['manufacturer_id'];

    $stmt = $conn->prepare("CALL add_product(?,?,?)");
    $stmt->bind_param("sdi", $name, $price, $mid);
    $stmt->execute();

    $stmt->close();
    $conn->next_result();
}

/* DELETE MANUFACTURER */
if (isset($_POST['delete'])) {
	$dmid = $_POST['manufacturer_id'];
	$conn->query(" delete from manufacturers where id='$dmid' ");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>WDPF70 Project</title>
</head>
<body>

<h2>Add Manufacturer</h2>
<form method="post">
    Name: <input type="text" name="name"><br>
    Address: <input type="text" name="address"><br>
    Contact: <input type="text" name="contact"><br>
    <button type="submit" name="add_manufacturer">Add Manufacturer</button>
</form>

<hr>

<h2>Add Product</h2>
<form method="post">
    Name: <input type="text" name="pname"><br>
    Price: <input type="text" name="price"><br>

    Manufacturer:
    <select name="manufacturer_id">
        <?php
        $res = $conn->query("SELECT * FROM manufacturers");
        while($row = $res->fetch_assoc()){
            echo "<option value='".$row['id']."'>".$row['name']."</option>";
        }
        ?>
    </select>

    <button type="submit" name="add_product">Add Product</button>
</form>
<hr>

<h2>Delete Manufacturer</h2>

<form method="post" action="">
    <select name="manufacturer_id">
    <?php
    $res = $conn->query("SELECT * FROM manufacturers");
    while($row = $res->fetch_assoc()){
        echo "<option value='".$row['id']."'>".$row['name']."</option>";
    }
    ?>
</select>

    <button type="submit" name="delete">Delete</button>
</form>

<hr>

<h2>Product View</h2>

<table border="1">
<tr>
    <th>ID</th>
    <th>Product Name</th>
    <th>Price</th>
    <th>Manufacturer Name</th>
</tr>

<?php
$res = $conn->query("SELECT * FROM product_view");

while($row = $res->fetch_assoc()){
    echo "<tr>
        <td>".$row['id']."</td>
        <td>".$row['product_name']."</td>
        <td>".$row['price']."</td>
        <td>".$row['manufacturer_name']."</td>
    </tr>";
}
?>

</table>




</body>
</html>