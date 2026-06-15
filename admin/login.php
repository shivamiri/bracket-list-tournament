<?php
require_once '../common/config.php';

// 🔥 NEW: Agar user already logged in hai, toh direct dashboard (index.php) par bhej do
if (session_status() === PHP_SESSION_NONE) session_start();
if(isset($_SESSION['admin_id']) && !isset($_GET['logout'])) {
    header("Location: index.php");
    exit;
}

if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid admin credentials!";
    }
}
require_once 'common/header.php';
?>

<div class="flex items-center justify-center h-full min-h-[80vh]">
    <div class="glass-panel p-10 rounded-xl w-full max-w-md shadow-2xl">
        <div class="text-center mb-8">
            <i class="fa-solid fa-shield-halved text-5xl text-blue-500 mb-4 drop-shadow-[0_0_15px_rgba(59,130,246,0.6)]"></i>
            <h2 class="text-3xl font-bold">Admin Secure Login</h2>
        </div>
        <?php if($error) echo "<div class='bg-red-900 border border-red-500 text-red-200 px-4 py-2 rounded mb-4'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-4">
                <input type="text" name="username" placeholder="Admin Username" required class="w-full bg-gray-800 border border-gray-700 p-3 rounded focus:border-blue-500 outline-none text-white">
            </div>
            <div class="mb-6">
                <input type="password" name="password" placeholder="Password" required class="w-full bg-gray-800 border border-gray-700 p-3 rounded focus:border-blue-500 outline-none text-white">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded transition shadow-[0_0_15px_rgba(37,99,235,0.4)]">Access Panel</button>
        </form>
    </div>
</div>

<?php require_once 'common/bottom.php'; ?>
