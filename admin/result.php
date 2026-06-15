<?php
require_once '../common/config.php';
require_once 'common/header.php';

// Handle Match Winner Submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_result'])) {
    $match_id = (int)$_POST['match_id'];
    $winner_id = (int)$_POST['winner_id'];
    $t_id = (int)$_POST['tournament_id'];

    // Update current match
    $stmt = $pdo->prepare("UPDATE matches SET winner_team_id = ? WHERE id = ?");
    $stmt->execute([$winner_id, $match_id]);

    // Change Tournament status to Live if it was upcoming
    $pdo->query("UPDATE tournaments SET status = 'Live' WHERE id = $t_id AND status = 'Upcoming'");

    // Check if next match exists
    $m_stmt = $pdo->prepare("SELECT next_match_id FROM matches WHERE id = ?");
    $m_stmt->execute([$match_id]);
    $next_m = $m_stmt->fetchColumn();

    if($next_m) {
        // Find if team1_id is empty, else put in team2_id
        $nm_stmt = $pdo->prepare("SELECT team1_id FROM matches WHERE id = ?");
        $nm_stmt->execute([$next_m]);
        $has_team1 = $nm_stmt->fetchColumn();

        if(empty($has_team1)) {
            $pdo->query("UPDATE matches SET team1_id = $winner_id WHERE id = $next_m");
        } else {
            $pdo->query("UPDATE matches SET team2_id = $winner_id WHERE id = $next_m");
        }
    } else {
        // No next match = Final Match! Mark Tournament Completed & Set Champion
        $pdo->query("UPDATE tournaments SET status = 'Completed', champion_team_id = $winner_id WHERE id = $t_id");
    }
    
    echo "<script>window.location='result.php';</script>";
    exit;
}

// Fetch matches that are ready (have both teams) but no winner yet
$query = "
    SELECT m.*, t.title as t_title, 
           t1.team_name as t1_name, t1.team_logo as t1_logo,
           t2.team_name as t2_name, t2.team_logo as t2_logo
    FROM matches m
    JOIN tournaments t ON m.tournament_id = t.id
    JOIN teams t1 ON m.team1_id = t1.id
    JOIN teams t2 ON m.team2_id = t2.id
    WHERE m.winner_team_id IS NULL AND m.team1_id IS NOT NULL AND m.team2_id IS NOT NULL
    ORDER BY m.tournament_id DESC, m.round_no ASC
";
$pending_matches = $pdo->query($query)->fetchAll();
?>

<div class="glass-panel p-6 rounded-lg max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-6 border-b border-gray-700 pb-4">
        <i class="fa-solid fa-gavel text-3xl text-green-400"></i>
        <h2 class="text-2xl font-bold">Match Results Management</h2>
    </div>

    <?php if(count($pending_matches) == 0): ?>
        <div class="bg-gray-800 p-10 text-center rounded border border-gray-700 border-dashed">
            <i class="fa-regular fa-face-smile-beam text-4xl text-gray-500 mb-3"></i>
            <p class="text-gray-400">No pending matches require a result right now.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach($pending_matches as $m): ?>
            <div class="bg-gray-900 border border-gray-700 rounded-xl p-5 shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-blue-900 text-blue-300 text-xs px-2 py-1 rounded-bl-lg font-bold">
                    Round <?= $m['round_no'] ?>
                </div>
                <h4 class="text-gray-400 text-xs uppercase tracking-wider mb-4 border-b border-gray-800 pb-1"><?= htmlspecialchars($m['t_title']) ?></h4>
                
                <form method="POST" action="">
                    <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
                    <input type="hidden" name="tournament_id" value="<?= $m['tournament_id'] ?>">
                    
                    <div class="flex justify-between items-center mb-6">
                        <label class="flex flex-col items-center cursor-pointer group w-1/3">
                            <input type="radio" name="winner_id" value="<?= $m['team1_id'] ?>" required class="peer sr-only">
                            <div class="w-16 h-16 rounded-full border-2 border-gray-700 peer-checked:border-green-500 peer-checked:shadow-[0_0_15px_rgba(74,222,128,0.5)] transition p-1 mb-2 bg-gray-800">
                                <img src="../upload/<?= htmlspecialchars($m['t1_logo']) ?>" class="w-full h-full rounded-full object-cover">
                            </div>
                            <span class="text-sm font-bold text-center peer-checked:text-green-400 transition"><?= htmlspecialchars($m['t1_name']) ?></span>
                        </label>

                        <div class="text-xl font-black text-gray-600 italic">VS</div>

                        <label class="flex flex-col items-center cursor-pointer group w-1/3">
                            <input type="radio" name="winner_id" value="<?= $m['team2_id'] ?>" required class="peer sr-only">
                            <div class="w-16 h-16 rounded-full border-2 border-gray-700 peer-checked:border-green-500 peer-checked:shadow-[0_0_15px_rgba(74,222,128,0.5)] transition p-1 mb-2 bg-gray-800">
                                <img src="../upload/<?= htmlspecialchars($m['t2_logo']) ?>" class="w-full h-full rounded-full object-cover">
                            </div>
                            <span class="text-sm font-bold text-center peer-checked:text-green-400 transition"><?= htmlspecialchars($m['t2_name']) ?></span>
                        </label>
                    </div>

                    <button type="submit" name="save_result" class="w-full bg-green-700 hover:bg-green-600 text-white py-2 rounded font-bold transition flex justify-center items-center gap-2">
                        <i class="fa-solid fa-check"></i> Save Winner
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'common/bottom.php'; ?>
