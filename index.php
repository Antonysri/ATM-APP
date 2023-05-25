<?php

session_start();
//updated password isset
if(isset($_SESSION['password-updated']) || !empty($_SESSION['password-updated'])){
    $updatdPassword =  $_SESSION['password-updated'];
    //Remove the session variable to avoid displaying the message again
    unset($_SESSION['password-updated']);
}

// Check if the user is logged in
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit;
}

// Session expiration time (in seconds)
$expirationTime = 1800; // 30 minutes * 60 seconds = 1800 seconds

// Check if the session is expired
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $expirationTime)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
} else {
    // Update session and database last_activity
    include_once 'connection.php';
    $_SESSION['last_activity'] = time();
    $sql = "UPDATE atm_account SET last_activity = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $stmt->close();
    
}

// Set the page title with the logged-in user's name
$title = $_SESSION['bank_name'] . " Bank - Account";
$error_msg = ""; // Initialize the error message variable

include_once 'connection.php';
//retrieve account details
$sql  = "select * from atm_account where id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
//amount withdraw and deposit

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $amount = $_POST['amount'];
    $amount = htmlspecialchars($amount,ENT_QUOTES,'UTF-8');
    //check deposit
    if(isset($_POST['deposit'])){
        $newBalance = $user['balance'];
        $_SESSION['balance'] = $amount + $newBalance;
        //update the balance in db
        $sql = "update atm_account set balance = ? where id = ?";
        $stmt= $conn->prepare($sql);
        $stmt->bind_param("di",$_SESSION['balance'],$_SESSION['id']);
        $stmt->execute();
        $stmt->close();

        header("Refresh:0");
        exit;
    
    }
    //check withdraw
     if(isset($_POST['withdraw'])){
        $newBalance = $user['balance'];
        if($amount > $newBalance){
            $error_msg = "insufficient account balance";
    
        }
        else{
        $_SESSION['balance'] = $newBalance - $amount;
        //update the balance in db
        $sql = "update atm_account set balance = ? where id = ?";
        $stmt= $conn->prepare($sql);
        $stmt->bind_param("di",$_SESSION['balance'],$_SESSION['id']);
        $stmt->execute();
        $stmt->close();

        header("Refresh:0");
        exit;
        }
    }
    //clear amount
    if(isset($_POST['clear'])){
        // echo "<script>
        //  function clear_amount(){
        //     document.getElementById('amount').value = '';
        // }
        // </script>";

        $amount = "";
    } 
    //logout function
    // if(isset($_POST['logout'])){
    //     echo '<script>

    //     if(confirm("Are you sure you want to log out?")){
    //     window.location.href="logout.php";
    //     }
        
    //     </script>';
        
    // }
}
$stmt->close();
$conn->close();


?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <style>
        .input-container {
            text-align: center;
        }
    </style>
    <!-- logout button action -->
    <script>
        function handleClick(){
            if(confirm("Are you sure you want to log out?")){
                window.location.href='logout.php';
            }
        }
    </script>
</head>
<body>
<div class="input-container">
    <h3 style="color: darkgray">Welcome, <?php echo $_SESSION['name']; ?>!</h3><br>
    
    <!-- Rest of your HTML code for displaying bank account information or performing actions -->
    <form method="post" action="" >
        <label>Enter your amount : </label>
        <input type="number" name ='amount'><br><br>
        <span style = "color:red"><?php echo $error_msg; ?></span<br><br>
        <input type="submit" name ='deposit' value="Deposit">
        <input type="submit" name ='withdraw' value="Withdraw">
        <input type="submit" name="clear" value="Clear" onclick="clear_amount()"><br>
        
        <p>Your current balance is = <?php echo $_SESSION['balance']; ?> </p>
        <button type="submit" name="logout" onclick= handleClick()>log out</button><br><br>
        <a href="password_change.php">Password change</a><br><br><br>

        <!--updated password message display -->
        <?php if(isset($updatdPassword)):?>
            <span style="color: darkgreen;"><?php echo $updatdPassword;?> </span>
            <?php endif; ?>

        
        
       
    </form>
</div>
</body>
</html>
