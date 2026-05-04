<?php $conn = mysqli_connect("localhost", "root", "", "shop_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
 ?>

<h2>Product List</h2>
<a href="product_add.php">Add New</a>

<table border="1">
<tr>
<th>ID</th>
<th>Name</th>
<th>Price</th>
<th>Brand</th>
<th>Image</th>
<th>Action</th>
</tr>

<?php
$q = "SELECT products.*, brand.name as brand_name 
      FROM products 
      JOIN brand ON products.brand_id = brand.id";

$res=mysqli_query($conn,$q);

while($row=mysqli_fetch_assoc($res)){
?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= $row['name'] ?></td>
<td><?= $row['price'] ?></td>
<td><?= $row['brand_name'] ?></td>
<td><img src="upload/<?= $row['product_image'] ?>" width="80"></td>

<td>
    <a href="product_edit.php?id=<?= $row['id'] ?>">Edit</a> |
    <a href="delete.php?id=<?= $row['id'] ?>">Delete</a>
</td>
</tr>
<?php } ?>
</table>