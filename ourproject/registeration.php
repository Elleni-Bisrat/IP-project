<?php




if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $username= htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $role = htmlspecialchars($_POST['role']); 
    

$uploadDir = 'uploads/';   
$file = $_FILES['profile_pic'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxSize = 2 * 1024 * 1024;
 
 if (!in_array($file['type'], $allowedTypes)) {
 echo "Invalid file type.";
 exit;
 }
 
 if ($file['size'] > $maxSize) {
 echo "File size exceeds the limit.";
 exit;
 }
 
$safeFilename = uniqid() . '_' . basename($file['name']);
$uploadPath = $uploadDir . $safeFilename;
 
 if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
 echo "Profile picture uploaded successfully.";
 } else {
 echo "Error uploading file.";
 }
  

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

}






 
$conn = mysqli_connect("localhost", "root", "", "dsatabse");

if (!$conn) {
 die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";

    $sql = "INSERT INTO users (name, email, password, role, profile) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("sssss", $username, $email, $hashedPassword , $role, $safeFilename);
    if ($stmt->execute()){
        echo "<br/>"."Record added successfully";
    session_start();
    $_SESSION['username'] = $username;
    $_SESSION['loggedin'] = true; 
    $_SESSION['role'] = $role;
    header("Location: session.php"); 
    exit();

    }else {
        echo "<br/>Error adding record: " . $stmt->error;
    }


    mysqli_close($conn);


    
    
?>