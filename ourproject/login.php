<?php


if ($_SERVER['REQUEST_METHOD']=='POST'){
    $username=$_POST['username'];
    $password = $_POST['password'];
}

$conn=mysqli_connect("localhost","root","","dsatabse");

if (!$conn){
    die("connection failed: ". mysqli_connect_error());
}
 echo "Connected successfully";



 $sql="SELECT password FROM users WHERE name = ?";



$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $storedHashedPassword = $row['password'];
    
   
    if (password_verify($password, $storedHashedPassword)) {
        
    echo "Login successful!";
    session_start();
    $_SESSION['username'] = $username;
    $_SESSION['loggedin'] = true; 
    header("Location: session.php"); 
    exit();

    } else {
        echo "Invalid password.";
         header("Location: login.php?error=invalid_password");
    }
} else {
    echo "Username not found.";
}


$stmt->close();


 mysqli_close($conn);
?>