<?php
require_once 'common/config.php';

if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        $name = sanitize($_POST['name']);
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $username, $email, $password]);
            $success = "Registration successful! Please login.";
        } catch(PDOException $e) {
            $error = "Username or Email already exists.";
        }
    } elseif (isset($_POST['login'])) {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid credentials!";
        }
    }
}
require_once 'common/header.php';
?>

<div class="max-w-4xl mx-auto mt-10 flex flex-col md:flex-row gap-8">
    <div class="flex-1 glass-card p-8 rounded-xl">
        <h2 class="text-2xl font-bold mb-6 text-cyan-400">Login</h2>
        <?php if($error) echo "<p class='text-red-500 mb-4'>$error</p>"; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required class="w-full bg-gray-800 text-white p-3 rounded mb-4 border border-gray-700 focus:border-cyan-500 outline-none">
            <input type="password" name="password" placeholder="Password" required class="w-full bg-gray-800 text-white p-3 rounded mb-4 border border-gray-700 focus:border-cyan-500 outline-none">
            <button type="submit" name="login" class="w-full bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white p-3 rounded font-bold transition">Login</button>
        </form>
    </div>

    <div class="flex-1 glass-card p-8 rounded-xl">
        <h2 class="text-2xl font-bold mb-6 text-purple-400">Create Account</h2>
        <?php if($success) echo "<p class='text-green-500 mb-4'>$success</p>"; ?>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Full Name" required class="w-full bg-gray-800 text-white p-3 rounded mb-4 border border-gray-700 focus:border-purple-500 outline-none">
            <input type="text" name="username" placeholder="Username" required class="w-full bg-gray-800 text-white p-3 rounded mb-4 border border-gray-700 focus:border-purple-500 outline-none">
            <input type="email" name="email" placeholder="Email Address" required class="w-full bg-gray-800 text-white p-3 rounded mb-4 border border-gray-700 focus:border-purple-500 outline-none">
            <input type="password" name="password" placeholder="Password" required class="w-full bg-gray-800 text-white p-3 rounded mb-4 border border-gray-700 focus:border-purple-500 outline-none">
            <button type="submit" name="register" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white p-3 rounded font-bold transition">Register</button>
        </form>
    </div>
</div>

<?php require_once 'common/bottom.php'; ?>
