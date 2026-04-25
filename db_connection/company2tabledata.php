<?php
$conn = new mysqli("localhost", "root", "", "company");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Manufacturer list load
$result = $conn->query("SELECT * FROM Manufacturer");
?>

<h2>Add Manufacturer</h2>

<form method="post" action="insert_manufacturer.php">
    Name: <input type="text" name="name" required><br><br>
    Address: <input type="text" name="address" required><br><br>
    Contact: <input type="text" name="contact" required><br><br>

    <input type="submit" value="Save Manufacturer">
</form>

<hr>

<h2>Add Product</h2>

<form method="post" action="insert_product.php">
    Name: <input type="text" name="name" required><br><br>

    Price: <input type="number" name="price" required><br><br>

    Manufacturer:
    <select name="manufacturer_id" required>
        <option value="">Select Manufacturer</option>
        <?php while($row = $result->fetch_assoc()) { ?>
            <option value="<?php echo $row['id']; ?>">
                <?php echo $row['name']; ?>
            </option>
        <?php } ?>
    </select><br><br>

    <input type="submit" value="Save Product">
</form>


<?php
$conn = new mysqli("localhost", "root", "", "company");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];

    $stmt = $conn->prepare("CALL insert_manufacturer(?, ?, ?)");
    $stmt->bind_param("sss", $name, $address, $contact);
    $stmt->execute();

    echo "Manufacturer Added!";
}
?>


<?php
$conn = new mysqli("localhost", "root", "", "company");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $manufacturer_id = $_POST['manufacturer_id'];

    $stmt = $conn->prepare("CALL insert_product(?, ?, ?)");
    $stmt->bind_param("sii", $name, $price, $manufacturer_id);
    $stmt->execute();

    echo "Product Added!";
}
?>


