<?php 
$con = mysqli_connect("localhost", "root", "", "company");

?>

<h2>Add Manufacturer</h2>

<form method="post" action="insert_manufacturer.php">
    Name: <input type="text" name="name" required><br><br>
    Address: <input type="text" name="address" required><br><br>
    Contact: <input type="text" name="contact" required><br><br>

    <input type="submit" name="submit" value="Save">
</form>
<?php
$conn = new mysqli("localhost", "root", "", "test");
$result = $conn->query("SELECT * FROM Manufacturer");
?>

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

    <input type="submit" name="submit" value="Save Product">
</form>
6. Product Insert (PHP)
$conn = new mysqli("localhost", "root", "", "test");

if(isset($_POST['submit'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $manufacturer_id = $_POST['manufacturer_id'];

    $conn->query("CALL insert_product('$name','$price','$manufacturer_id')");

    echo "Product Added!";
}
4. Manufacturer Insert (PHP)
$conn = new mysqli("localhost", "root", "", "test");

if(isset($_POST['submit'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];

    $conn->query("CALL insert_manufacturer('$name','$address','$contact')");
    
    echo "Manufacturer Added!";
}


Stored Procedure (Product)
DELIMITER //

CREATE PROCEDURE insert_product(
    IN p_name VARCHAR(50),
    IN p_price INT,
    IN p_manufacturer_id INT
)
BEGIN
    INSERT INTO Product(name, price, manufacturer_id)
    VALUES (p_name, p_price, p_manufacturer_id);
END //

DELIMITER ;


Stored Procedure (Manufacturer)
DELIMITER //

CREATE PROCEDURE insert_manufacturer(
    IN m_name VARCHAR(50),
    IN m_address VARCHAR(100),
    IN m_contact VARCHAR(50)
)
BEGIN
    INSERT INTO Manufacturer(name, address, contact_no)
    VALUES (m_name, m_address, m_contact);
END //

DELIMITER ;