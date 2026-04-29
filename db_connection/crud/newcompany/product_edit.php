<?php $conn = mysqli_connect("localhost", "root", "", "shop_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

$id=$_GET['id'];
$data=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM products WHERE id=$id"));
?>

<h2>Edit Product</h2>

<form method="POST" enctype="multipart/form-data">
    Name: <input type="text" name="name" value="<?= $data['name'] ?>"><br>
    Price: <input type="text" name="price" value="<?= $data['price'] ?>"><br>

    Image: <input type="file" name="image"><br>

    <button name="update">Update</button>
</form>

<?php
if(isset($_POST['update'])){
    $name=$_POST['name'];
    $price=$_POST['price'];

    if($_FILES['image']['name']!=""){
        $img=$_FILES['image']['name'];
        $tmp=$_FILES['image']['tmp_name'];
        move_uploaded_file($tmp,"upload/".$img);

        mysqli_query($conn,"UPDATE products SET name='$name',price='$price',product_image='$img' WHERE id=$id");
    }else{
        mysqli_query($conn,"UPDATE products SET name='$name',price='$price' WHERE id=$id");
    }

    header("Location: product_list.php");
}
?>