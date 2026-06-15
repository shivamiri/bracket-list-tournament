<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bracket List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0f172a; color: #f8fafc; }
        .glass-panel { background: rgba(30, 41, 59, 0.8); border: 1px solid #334155; }
        /* Mobile Navbar Scrollbar Hide/Style */
        .mobile-nav-scroll::-webkit-scrollbar { height: 4px; }
        .mobile-nav-scroll::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 4px; }
    </style>
</head>
<body class="flex flex-col md:flex-row min-h-screen">
    <?php if(isset($_SESSION['admin_id'])): ?>
    
    <aside class="w-full md:w-64 bg-gray-900 border-b md:border-b-0 md:border-r border-gray-800 flex flex-col flex-shrink-0 z-50">
        
        <div class="p-4 md:p-6 border-b border-gray-800 flex justify-between items-center">
            <h2 class="text-xl md:text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">Admin Panel</h2>
            <a href="login.php?logout=1" class="md:hidden text-red-400 hover:text-red-300 text-xl"><i class="fa-solid fa-sign-out-alt"></i></a>
        </div>
        
        <nav class="flex md:flex-col p-2 md:p-4 gap-2 overflow-x-auto md:overflow-visible whitespace-nowrap mobile-nav-scroll">
            <a href="index.php" class="inline-block md:block py-2.5 px-4 rounded transition hover:bg-gray-800 hover:text-blue-400"><i class="fa-solid fa-plus-circle mr-2"></i> Manage Tournaments</a>
            <a href="result.php" class="inline-block md:block py-2.5 px-4 rounded transition hover:bg-gray-800 hover:text-green-400"><i class="fa-solid fa-trophy mr-2"></i> Match Results</a>
            <a href="../index.php" target="_blank" class="inline-block md:block py-2.5 px-4 rounded transition hover:bg-gray-800 hover:text-cyan-400"><i class="fa-solid fa-globe mr-2"></i> View Site</a>
        </nav>
        
        <div class="p-4 border-t border-gray-800 hidden md:block mt-auto">
            <a href="login.php?logout=1" class="block py-2 text-red-400 hover:text-red-300"><i class="fa-solid fa-sign-out-alt mr-2"></i> Logout</a>
        </div>
    </aside>
    <?php endif; ?>
    
    <main class="flex-1 p-4 md:p-8 overflow-x-hidden w-full">
