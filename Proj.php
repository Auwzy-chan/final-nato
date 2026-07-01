<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";
$message_type = "";

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "hub.dbms"; 

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!empty($email) && strlen($password) >= 6) {
        // Uses your actual database table column structures
        $stmt = $conn->prepare("SELECT `Email_Address`, `Password` FROM `hub1.db` WHERE `Email_Address` = ? AND `Password` = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['Password'])) {
                    $message = "Successfully signed in as: " . htmlspecialchars($user['Full_Name']);
                    $message_type = "success";
                } else {
                    $message = "Invalid email or password.";
                    $message_type = "error";
                }
            } else {
                $message = "Invalid email or password.";
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $message = "Unable to prepare login query.";
            $message_type = "error";
        }
    } else {
        $message = "Please enter valid credentials.";
        $message_type = "error";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Hub - Welcome Back</title>
    <link rel="stylesheet" href="proj.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="overlay"></div>
            <div class="logo-area">
                <div class="logo-icon">🚗</div>
                <span class="logo-text">AUTO HUB</span>
            </div>
            <div class="hero-content">
                <span class="badge">PREMIER DEALERSHIP</span>
                <h1>YOUR NEXT <br><span class="highlight">DREAM CAR</span> <br>AWAITS.</h1>
                <p>Browse, finance, and book your next premium vehicle — all in one place.</p>
            </div>
            <div class="stats-container">
                <div class="stat-item"><h3>500+</h3><p>Vehicles</p></div>
                <div class="stat-item"><h3>4.9★</h3><p>Rating</p></div>
                <div class="stat-item"><h3>15yr</h3><p>Experience</p></div>
            </div>
        </div>

        <div class="right-panel">
            <div class="form-container">
                <div class="form-header">
                    <h2>WELCOME BACK</h2>
                    <p class="subtitle">Sign in to your account to continue</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="notification <?= $message_type; ?>"><?= $message; ?></div>
                <?php endif; ?>

                <div class="toggle-container">
                    <button class="toggle-btn active" type="button" onclick="location.href='signin.php'">Sign In</button>
                    <button class="toggle-btn" type="button" onclick="location.href='signup.php'">Create Account</button>
                </div>

                <form action="signin.php" method="POST">
                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="submit-btn">Sign In</button>
                </form>
                <p class="demo-text">Demo: any email + 6+ character password</p>
            </div>
            <div class="help-btn" onclick="alert('Need help? Contact support@autohub.demo')">?</div>
        </div>
    </div>
</body>
</html>




<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";
$message_type = "";
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "hub.dbms";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";

    $fullname = htmlspecialchars($fullname, ENT_QUOTES, "UTF-8");
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($phone, ENT_QUOTES, "UTF-8");

    if ($fullname === "" || $email === "" || $phone === "" || $password === "") {
        $message = "Please fill out all fields.";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = "error";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
        if ($conn->connect_error) {
            $message = "Database connection failed.";
            $message_type = "error";
        } else {
            // FIXED: Your table maps exact custom keys. Omitted auto-incrementing ID column.
            $stmt = $conn->prepare("INSERT INTO `hub1.db`(`Full_Name`, `Email_Address`, `Phone_Num`, `Password`) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $fullname, $email, $phone, $hashed_password);
                if ($stmt->execute()) {
                    $message = "Welcome to Auto Hub, " . $fullname . "! Account successfully created.";
                    $message_type = "success";
                } else {
                    if ($conn->errno == 1062) {
                        $message = "Email address is already registered.";
                    } else {
                        $message = "Failed to create account. Please try again.";
                    }
                    $message_type = "error";
                }
                $stmt->close();
            } else {
                $message = "Unable to prepare database query.";
                $message_type = "error";
            }
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Hub - Join Us</title>
    <link rel="stylesheet" href="proj.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="overlay"></div>
            <div class="logo-area">
                <div class="logo-icon">🚗</div>
                <span class="logo-text">AUTO HUB</span>
            </div>
            <div class="hero-content">
                <span class="badge">PREMIER DEALERSHIP</span>
                <h1>YOUR NEXT <br><span class="highlight">DREAM CAR</span> <br>AWAITS.</h1>
                <p>Browse, finance, and book your next premium vehicle — all in one place.</p>
            </div>
            <div class="stats-container">
                <div class="stat-item"><h3>500+</h3><p>Vehicles</p></div>
                <div class="stat-item"><h3>4.9★</h3><p>Rating</p></div>
                <div class="stat-item"><h3>15yr</h3><p>Experience</p></div>
            </div>
        </div>

        <div class="right-panel">
            <div class="form-container">
                <div class="form-header">
                    <h2>JOIN AUTO HUB</h2>
                    <p class="subtitle">Create your account to get started</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="notification <?= $message_type; ?>"><?= $message; ?></div>
                <?php endif; ?>

                <div class="toggle-container">
                    <button class="toggle-btn" type="button" onclick="location.href='signin.php'">Sign In</button>
                    <button class="toggle-btn active" type="button" onclick="location.href='signup.php'">Create Account</button>
                </div>

                <form action="signup.php" method="POST">
                    <div class="input-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" placeholder="Alex Rivera" required value="<?= isset($fullname) ? htmlspecialchars($fullname) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required value="<?= isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="+1 (555) 000-0000" required value="<?= isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="submit-btn">Create Account</button>
                </form>
            </div>
            <div class="help-btn">?</div>
        </div>
    </div>
</body>
</html>