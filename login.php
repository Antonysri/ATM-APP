<?php

 session_start();
//  if (isset($_SESSION['password-updated'])) {
//     $passwordUpdated = $_SESSION['password-updated'];
//     unset($_SESSION['password-updated']);
//     //Remove the session variable to avoid displaying the message again
// } 
//Check if the user is already logged in
if(isset($_SESSION['id'])){
    header("location:index.php");
}


// check database connection
include_once 'connection.php';
$error_msg = "";
$updated   ="";
if($_SERVER["REQUEST_METHOD"]=="POST"){
    if(isset($_POST['submit'])){
        $name = $_POST['name'] ?? "";
        $password = $_POST['password'] ?? "";
        // check sanitize
        $name = htmlspecialchars($name,ENT_QUOTES,'UTF-8');
        $password = htmlspecialchars($password,ENT_QUOTES,'UTF-8');

        $sql = "select * from atm_account where name=?";
        $stmt = $conn->prepare($sql);;
        $stmt->bind_param("s",$name);
        $stmt->execute();
        $result = $stmt->get_result();
       
        if($result->num_rows > 0 ){
            $row    = $result->fetch_assoc();
            $hashedPassword = $row['password'];
        
            if(password_verify($password,$hashedPassword)){

            $_SESSION['id']            = $row['id'];
            $_SESSION['name']          = $row['name'];
            $_SESSION['password']      = $row['password'];
            $_SESSION['bank_name']     = $row['bank_name'];
            $_SESSION['branch_name']   = $row['branch_name'];
            $_SESSION['balance']       = $row['balance'];
            $_SESSION['last_activity'] = time();

            header("location:index.php");
            exit;
            }
            else{
                $error_msg = "Invalid  password.";
            }
        }
        else{
            $error_msg = "Invalid username or password.";
        }
        
        

    }
}

?>

<html>
    <head>

<style>
    .input-container{
        text-align: center;
    }
    .input-container input[name="name"]{
        margin-right: -20;
    }
</style>

    </head>
    <body>
        <div class="input-container">
        <h2 style="color: darkgrey;">Please Login to Your Bank Account</h2>
        <?php
        if(isset($error_msg)):
            echo '<p style = "color:red ;">'.$error_msg.'</p>';
        endif;
        ?>
        <form name="f1" method="post" action="">
            <label for="email">Name :</label>
            <input type="text" name="name" required>
            <br><br>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <br><br>
            <input type="submit" name="submit" value="Login">
            <br><br>
            <a href="registration.php">CREATE A ACCOUNT</a>
            <br><br><br><br>
<!--         
            <?php if (isset($passwordUpdated)): ?>
            <p style="color: green;"><?php echo $passwordUpdated; ?></p>
            <?php endif; ?>  -->
        </form>
    </div>  
    </body>
</html>