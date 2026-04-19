<?php 
$conn = mysqli_connect("localhost", "root", "", "batch70");

$msg = "";

if(isset($_POST['submit'])){
    $n = $_POST['name'] ?? '';
    $e = $_POST['email'] ?? '';
    $a = $_POST['address'] ?? '';
    $p = $_POST['phone'] ?? '';

    $query = "CALL call_users('$n','$e','$a','$p')";

    if(mysqli_query($conn, $query)){
        $msg = "success";
    }else{
        $msg = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Batch70 User Form</title>
    <style>
        body {
            font-family: Arial;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 350px;
            box-shadow: 0px 15px 40px rgba(0,0,0,0.3);
            animation: fadeIn 0.7s ease;
        }

        h2 {
            text-align: center;
            color: #444;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            background: #f1f1f1;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            background: #e0e0e0;
            box-shadow: 0 0 8px #667eea;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #5a67d8;
            transform: scale(1.05);
        }

        /* popup */
        .popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 10px;
            color: #fff;
            font-weight: bold;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            animation: slideIn 0.5s ease;
        }

        .success { background: #28a745; }
        .error { background: #dc3545; }

        @keyframes fadeIn {
            from {opacity:0; transform: translateY(20px);}
            to {opacity:1; transform: translateY(0);}
        }

        @keyframes slideIn {
            from {opacity:0; transform: translateX(100px);}
            to {opacity:1; transform: translateX(0);}
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>User Form</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Enter Name" required>
        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="text" name="address" placeholder="Enter Address" required>
        <input type="text" name="phone" placeholder="Enter Phone" required>
        <button type="submit" name="submit">Submit</button>
    </form>
</div>

<?php if($msg == "success"){ ?>
<div class="popup success">Inserted Successfully</div>
<?php } ?>

<?php if($msg == "error"){ ?>
<div class="popup error">Something went wrong</div>
<?php } ?>

</body>
</html>