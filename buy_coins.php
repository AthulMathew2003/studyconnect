<?php
session_start();
include 'connectdb.php';

// Load Razorpay keys
$key_id = "rzp_test_rCNFk3kITtnNBO";
$key_secret = "y7Rv1n26su7rNAZ5LeHidSKw";

if(!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

// Process the payment success
if(isset($_POST['razorpay_payment_id'])) {
    $razorpay_payment_id = $_POST['razorpay_payment_id'];
    $coins = intval($_POST['coins']);
    $amount = intval($_POST['amount']);
    $userid = $_SESSION['userid'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update user's coin balance in database
        $sql = "SELECT * FROM tbl_coinwallet WHERE userid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            // Update existing wallet
            $row = $result->fetch_assoc();
            $new_balance = $row['coin_balance'] + $coins;
            $sql = "UPDATE tbl_coinwallet SET coin_balance = ? WHERE userid = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $new_balance, $userid);
        } else {
            // Create new wallet
            $sql = "INSERT INTO tbl_coinwallet (userid, coin_balance) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $userid, $coins);
        }
        $stmt->execute();
        
        // Record transaction in tbl_coins
        $transaction_type = 'Purchase';
        $description = "Purchased $coins coins for ₹$amount";
        $sql = "INSERT INTO tbl_coins (userid, transaction_type, coins_amount, payment_id, description) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiss", $userid, $transaction_type, $coins, $razorpay_payment_id, $description);
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        $success_message = "Successfully purchased $coins coins for ₹$amount!";
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        $error_message = "Transaction failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Coins - Futuristic Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f4f7fe;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .header {
            width: 100%;
            max-width: 1200px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 10px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .header-logo {
            font-size: 1.8em;
            font-weight: 700;
            color: #2B3674;
        }

        .back-button {
            padding: 8px 16px;
            background: #4318FF;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-button:hover {
            background: #3311DB;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 24, 255, 0.2);
        }

        .container {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            color: #2B3674;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2em;
            font-weight: 700;
        }

        .coin-calculator {
            background: #f4f7fe;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
        }

        input[type="number"] {
            width: 100%;
            padding: 16px;
            border: 2px solid #E0E5F2;
            background: white;
            border-radius: 10px;
            color: #2B3674;
            font-size: 1.1em;
            margin-bottom: 15px;
            transition: border-color 0.3s ease;
        }

        input[type="number"]:focus {
            outline: none;
            border-color: #4318FF;
        }

        .cost-display {
            color: #2B3674;
            font-size: 1.2em;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            padding: 16px;
            background: white;
            border-radius: 10px;
            border: 2px solid #E0E5F2;
        }

        .buy-btn {
            width: 100%;
            padding: 16px;
            border: none;
            background: #4318FF;
            color: white;
            font-size: 1.1em;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .buy-btn:hover {
            background: #3311DB;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 24, 255, 0.2);
        }

        .buy-btn:active {
            transform: translateY(0);
        }

        .success-message {
            color: #05CD99;
            background: #E6FAF5;
            text-align: center;
            margin-top: 20px;
            padding: 16px;
            border-radius: 10px;
            font-weight: 500;
        }

        .error-message {
            color: #FF5252;
            background: #FFF1F1;
            text-align: center;
            margin-top: 20px;
            padding: 16px;
            border-radius: 10px;
            font-weight: 500;
        }

        /* Coin icon animation */
        .coin-icon {
            font-size: 2.5em;
            text-align: center;
            margin-bottom: 20px;
            color: #FFB547;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <div class="header">
        <div class="header-logo">StudyConnect</div>
        <button class="back-button" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>

    <div class="container">
        <div class="coin-icon">
            <i class="fas fa-coins"></i>
        </div>
        <h1>Buy Coins</h1>
        
        <div class="coin-calculator">
            <input type="number" 
                   id="coinAmount" 
                   min="1" 
                   placeholder="Enter number of coins"
                   oninput="calculateCost(this.value)">
            
            <div class="cost-display" id="costDisplay">
                Total Cost: ₹0
            </div>
        </div>

        <button id="rzp-button" class="buy-btn">
            <i class="fas fa-shopping-cart"></i> Purchase Coins
        </button>

        <form id="payment-form" method="POST" action="" style="display: none;">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="coins" id="coins">
            <input type="hidden" name="amount" id="amount">
        </form>

        <?php if(isset($success_message)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        let coinAmount = 0;
        let totalCost = 0;
        
        function calculateCost(coins) {
            coinAmount = parseInt(coins) || 0;
            totalCost = coinAmount * 2; // 2 Rs per coin
            document.getElementById('costDisplay').textContent = 
                `Total Cost: ₹${totalCost}`;
        }
        
        document.getElementById('rzp-button').addEventListener('click', function(e) {
            if (coinAmount <= 0) {
                alert('Please enter a valid number of coins to purchase');
                return;
            }
            
            // Set form values
            document.getElementById('coins').value = coinAmount;
            document.getElementById('amount').value = totalCost;
            
            var options = {
                key: "<?php echo $key_id; ?>",
                amount: totalCost * 100, // Amount in paise
                currency: "INR",
                name: "StudyConnect",
                description: "Purchase " + coinAmount + " coins",
                image: "https://your-logo-url.png", // Replace with your logo
                handler: function (response) {
                    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                    document.getElementById('payment-form').submit();
                },
                prefill: {
                    name: "<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>",
                    email: "<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>"
                },
                theme: {
                    color: "#4318FF"
                }
            };
            var rzp1 = new Razorpay(options);
            rzp1.open();
            e.preventDefault();
        });

        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
