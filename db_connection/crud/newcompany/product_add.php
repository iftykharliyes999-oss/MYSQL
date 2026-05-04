<?php $conn = mysqli_connect("localhost", "root", "", "shop_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
 ?>

<h2>Add Product</h2>

<form method="POST" enctype="multipart/form-data">
    Name: <input type="text" name="name"><br>
    Price: <input type="text" name="price"><br>

    Brand:
    <select name="brand_id">
        <?php
        $res = mysqli_query($conn,"SELECT * FROM brand");
        while($row=mysqli_fetch_assoc($res)){
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
    </select><br>

    Image: <input type="file" name="image"><br>

    <button name="save">Insert</button>
</form>

<?php
if(isset($_POST['save'])){
    $name=$_POST['name'];
    $price=$_POST['price'];
    $brand=$_POST['brand_id'];

    $img=$_FILES['image']['name'];
    $tmp=$_FILES['image']['tmp_name'];

    move_uploaded_file($tmp,"upload/".$img);

    mysqli_query($conn,"INSERT INTO products(name,price,brand_id,product_image)
    VALUES('$name','$price','$brand','$img')");

    echo "Inserted!";
}
?>