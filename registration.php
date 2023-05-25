<?php

 //create connection
 include_once 'connection.php';

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit'])) {

        $name        =$_POST['name'] ?? "";
        $password    =$_POST['password'] ?? "";
        $bank_name   =$_POST['bank_name'] ?? "";
        $branch_name =$_POST['branch_name'] ?? "";
        $balance = 0; // Default balance for new user

        $name        = input_test($name);
        $password    = input_test($password);
        $bank_name   = input_test($bank_name);
        $branch_name = input_test($branch_name);

       
        // check username already exists

        $sql  = "select name from atm_account where name = ?";
        $stmt = $conn->prepare($sql);;
        $stmt->bind_param("s",$name);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0 ){
            $error_msg = "user name already exists. please choose a different user name";
        
        }
        else{
            //password hashed for security purpose
            $hash_password = password_hash($password,PASSWORD_DEFAULT);
             // insert the data
            $sql = "insert into atm_account (name,password,bank_name,branch_name,balance) values(?,?,?,?,?)";
            $stmt = $conn->prepare($sql);;
            $stmt->bind_param("ssssd",$name,$hash_password,$bank_name,$branch_name,$balance);
            if($stmt->execute()){
                header("location:login.php");
            }
            $stmt->close();
        }
    }
}
$conn->close();
function input_test($data)
{
    $data  = trim($data);
    $data  = htmlspecialchars($data,ENT_QUOTES,'UTF-8');
    
    return $data;
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
            .input-container input[name="password"]{
                margin-right: -30;
            }
            .input-container input[name="bank_name"]{
                margin-right: -20;
            }
            .input-container input[name="branch_name"]{
                margin-right: -10;
            }
        </style>
    </head>
    <body>
        <div class="input-container">
            <h2 style="color: darkgray;">User Registration</h2>
            <form name="register_form" method="post" >
                <label>User Name :</label>
                <input type="text" name="name" required><br><br>

                <label>Password :</label>
                <input type="password" name="password" required><br><br>

                <label>Bank Name :</label>
                <input type="text" name="bank_name" required><br><br>

                <label>Branch Name :</label>
                <input type="text" name="branch_name" required><br><br>

                
                <input type="submit" name="submit" value="register"><br><br>
                <a href="login.php">Already have an account?</a>
                <?php if(isset($error_msg)){
                    echo '<p style = "color : red">'.$error_msg.'</p>';
                }?>
               
            </form>
        </div>
    </body>
</html>