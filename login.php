<?php
session_start();
include 'config.php';

$pdo = pdo_connect_mysql();

function redirectToDashboard($role) {
    switch ($role) {
        case 'admin':
            header('Location: admin_dashboard.php');
            break;
        case 'dinas':
            header('Location: dinas_dashboard.php');
            break;
        case 'user':
            header('Location: user_dashboard.php');
            break;
        default:
            exit('Invalid role');
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_GET['role'];

    if (isset($_POST['register'])) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':password', $hashed_password);
        $stmt->bindValue(':role', $role);
        $stmt->execute();
        $_SESSION['username'] = $username;
        redirectToDashboard($role);
    } elseif (isset($_POST['login'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND role = :role");
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':role', $role);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            redirectToDashboard($role);
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
    body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(135deg, #06BBCC 0%, #42E8E0 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
.container {
    max-width: 500px;
    background-color: #FFFFFF;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    padding: 40px;
}
.btn-primary {
    background-color: #06BBCC;
    border-color: #06BBCC;
    transition: all 0.3s ease;
    color: #FFFFFF;
}
.btn-primary:hover {
    background-color: #058C96;
    border-color: #058C96;
}
.btn-secondary {
    background-color: #CCCCCC;
    border-color: #CCCCCC;
    transition: all 0.3s ease;
    color: #333333;
}
.btn-secondary:hover {
    background-color: #B3B3B3;
    border-color: #B3B3B3;
}
.form-control:focus {
    border-color: #06BBCC;
    box-shadow: 0 0 0 0.2rem rgba(6, 187, 204, 0.25);
}
.form-control {
    border-radius: 5px;
    padding: 10px 15px;
}
.btn {
    border-radius: 5px;
    padding: 10px 15px;
}
    </style>
    <script>
        function validateForm() {
            var username = document.getElementById("username").value;
            var password = document.getElementById("password").value;
            if (username == "" || password == "") {
                alert("Username and Password must be filled out");
                return false;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4"><?php echo ucfirst($_GET['role']); ?> Login/Register</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form action="login.php?role=<?php echo $_GET['role']; ?>" method="post" onsubmit="return validateForm()">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" name="login" class="btn btn-primary w-45">Login</button>
                <button type="submit" name="register" class="btn btn-secondary w-45">Register</button>
            </div>
        </form>
    </div>
</body>
</html>