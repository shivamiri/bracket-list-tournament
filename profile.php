<?php
require_once 'common/config.php';
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $user_id]);
    $_SESSION['name'] = $name;
    $success = "Profile updated successfully!";
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

require_once 'common/header.php';
?>

<div class="max-w-2xl mx-auto mt-10 glass-card p-8 rounded-xl">
    <h2 class="text-3xl font-bold mb-6 text-cyan-400">My Profile</h2>
    <?php if($success) echo "<p class='text-green-500 mb-4'>$success</p>"; ?>
    
    <div class="mb-6 flex items-center gap-4">
        <div class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center text-3xl font-bold text-gray-400">
            <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>
        <div>
            <h3 class="text-2xl font-bold"><?= htmlspecialchars($user['name']) ?></h3>
            <p class="text-gray-400">Joined: <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
        </div>
    </div>

    <form method="POST" action="">
        <div class="mb-4">
            <label class="block text-gray-400 mb-2">Username</label>
            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled class="w-full bg-gray-900 text-gray-500 p-3 rounded border border-gray-700 cursor-not-allowed">
        </div>
        <div class="mb-4">
            <label class="block text-gray-400 mb-2">Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full bg-gray-800 text-white p-3 rounded border border-gray-700 focus:border-cyan-500 outline-none">
        </div>
        <div class="mb-6">
            <label class="block text-gray-400 mb-2">Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full bg-gray-800 text-white p-3 rounded border border-gray-700 focus:border-cyan-500 outline-none">
        </div>
        <button type="submit" name="update_profile" class="bg-cyan-600 hover:bg-cyan-500 text-white px-6 py-2 rounded font-bold transition">Update Profile</button>
    </form>
</div>

<?php require_once 'common/bottom.php'; ?>
