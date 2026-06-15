<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bracket List Tournament</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <style>
        /* AMOLED Dark & Neon Theme */
        body { background-color: #000000; color: #e2e8f0; user-select: none; -webkit-user-select: none; }
        .glass-card { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-text { text-shadow: 0 0 10px rgba(56, 189, 248, 0.8); }
        
        /* Bracket Layout */
        .bracket-container { 
            display: flex; padding: 20px; overflow-x: auto; align-items: stretch;
        }
        .bracket-round { 
            display: flex; flex-direction: column; justify-content: space-around; 
            min-width: 260px; position: relative; margin-right: 50px; /* Gap ke liye margin */
        }
        .bracket-round:last-child { margin-right: 0; }
        
        /* 🔥 NEW: Pair Wrapper jo vertical line draw karega */
        .match-pair {
            display: flex; flex-direction: column; justify-content: space-around;
            position: relative; flex-grow: 1; min-height: 180px;
        }
        
        /* 🔥 Vertical Line connecting the pair */
        .bracket-round:not(:last-of-type) .match-pair::after {
            content: ''; position: absolute;
            right: -25px; /* Margin ke theek beech me */
            top: 25%; bottom: 25%; /* Top card se Bottom card tak perfect height */
            width: 2px; background-color: #0ea5e9; box-shadow: 0 0 8px rgba(14, 165, 233, 0.8);
        }

        /* Final match me vertical line hide karne ke liye */
        .match-pair.single-match::after { display: none; }

        .match-card { 
            background: #1f2937; border: 1px solid #374151; border-radius: 8px; 
            padding: 10px; margin: 15px 0; position: relative; z-index: 10; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.5); 
        }
        .match-card.champion { border-color: #fbbf24; box-shadow: 0 0 15px rgba(251, 191, 36, 0.6); }
        .team-row { display: flex; align-items: center; gap: 10px; padding: 5px 0; border-bottom: 1px solid #374151; }
        .team-row:last-child { border-bottom: none; }
        .team-logo { width: 24px; height: 24px; border-radius: 50%; object-fit: cover; }
        .winner-text { color: #4ade80; font-weight: bold; }

        /* Right Horizontal Line */
        .bracket-round:not(:last-of-type) .match-card::after {
            content: ''; position: absolute; right: -25px; top: 50%;
            width: 25px; height: 2px; background-color: #0ea5e9; box-shadow: 0 0 8px rgba(14, 165, 233, 0.8);
        }

        /* Left Horizontal Line */
        .bracket-round:not(:first-of-type) .match-card::before {
            content: ''; position: absolute; left: -25px; top: 50%;
            width: 25px; height: 2px; background-color: #0ea5e9; box-shadow: 0 0 8px rgba(14, 165, 233, 0.8);
        }
    </style>
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">
    <nav class="bg-gray-900 border-b border-gray-800 p-4 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 neon-text">
                <i class="fa-solid fa-trophy text-cyan-400"></i> Bracket List
            </a>
            <div class="space-x-4">
                <a href="index.php" class="hover:text-cyan-400 transition"><i class="fa-solid fa-home"></i> Home</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="hover:text-cyan-400 transition"><i class="fa-solid fa-user"></i> Profile</a>
                    <a href="login.php?logout=1" class="text-red-400 hover:text-red-300 transition"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-cyan-400 transition"><i class="fa-solid fa-sign-in-alt"></i> Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="flex-grow container mx-auto p-4">
