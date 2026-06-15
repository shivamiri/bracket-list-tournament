<?php
require_once 'common/config.php';
require_once 'common/header.php';

// Fetch active or completed tournaments
$stmt = $pdo->query("SELECT * FROM tournaments ORDER BY id DESC");
$tournaments = $stmt->fetchAll();
?>

<div class="mb-8 text-center">
    <h1 class="text-4xl font-extrabold mb-2 neon-text text-white tracking-wider uppercase">Tournaments Hub</h1>
    <p class="text-gray-400">Select a tournament to view the bracket</p>
</div>

<?php if(isset($_GET['view'])): 
    $t_id = (int)$_GET['view'];
    $t_stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
    $t_stmt->execute([$t_id]);
    $tourney = $t_stmt->fetch();
    
    if($tourney):
        // Fetch teams mapped by ID
        $teams_stmt = $pdo->prepare("SELECT * FROM teams WHERE tournament_id = ?");
        $teams_stmt->execute([$t_id]);
        $teams_raw = $teams_stmt->fetchAll();
        $teams = [];
        foreach($teams_raw as $t) {
            $teams[$t['id']] = $t;
        }

        // Fetch matches
        $matches_stmt = $pdo->prepare("SELECT * FROM matches WHERE tournament_id = ? ORDER BY round_no ASC, match_no ASC");
        $matches_stmt->execute([$t_id]);
        $matches_raw = $matches_stmt->fetchAll();
        
        $bracket = [];
        $total_rounds = 0;
        foreach($matches_raw as $m) {
            $bracket[$m['round_no']][] = $m;
            if($m['round_no'] > $total_rounds) $total_rounds = $m['round_no'];
        }
?>
    <div class="glass-card p-6 rounded-xl mb-8 border-l-4 border-cyan-500">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-white"><?= htmlspecialchars($tourney['title']) ?></h2>
                <p class="text-gray-400 mt-1"><i class="fa-solid fa-users"></i> <?= $tourney['round_size'] ?> Teams | Status: <span class="text-cyan-400"><?= $tourney['status'] ?></span></p>
            </div>
            <a href="index.php" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white transition"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
    </div>

        <div class="bracket-container glass-card rounded-xl">
        <?php foreach($bracket as $round_no => $matches): ?>
            <div class="bracket-round">
                <h3 class="text-center text-cyan-500 font-bold mb-4 uppercase tracking-widest text-sm bg-gray-900 py-1 rounded">
                    <?= $round_no == $total_rounds ? 'Final' : ($round_no == $total_rounds - 1 ? 'Semi Final' : 'Round '.$round_no) ?>
                </h3>
                
                <?php 
                // Matches ko 2-2 ke pair me divide karna
                $chunks = array_chunk($matches, 2);
                foreach($chunks as $pair):
                    // Agar pair me sirf 1 match hai (jaise Final), toh extra class add karenge
                    $is_single = count($pair) == 1 ? 'single-match' : '';
                ?>
                <div class="match-pair <?= $is_single ?>">
                    <?php foreach($pair as $m): 
                        $t1 = $m['team1_id'] ? $teams[$m['team1_id']] : null;
                        $t2 = $m['team2_id'] ? $teams[$m['team2_id']] : null;
                        $winner = $m['winner_team_id'];
                    ?>
                    <div class="match-card <?= ($tourney['champion_team_id'] && ($tourney['champion_team_id'] == $m['team1_id'] || $tourney['champion_team_id'] == $m['team2_id']) && $round_no == $total_rounds) ? 'champion' : '' ?>">
                        <div class="team-row <?= ($winner == $m['team1_id']) ? 'winner-text' : ($winner ? 'opacity-50' : 'text-white') ?>">
                            <?php if($t1): ?>
                                <img src="upload/<?= htmlspecialchars($t1['team_logo']) ?>" class="team-logo" alt="Logo">
                                <span class="truncate w-32"><?= htmlspecialchars($t1['team_name']) ?></span>
                                <?php if($winner == $m['team1_id']): ?><i class="fa-solid fa-check ml-auto"></i><?php endif; ?>
                            <?php else: ?>
                                <div class="team-logo bg-gray-700"></div><span class="text-gray-500 italic text-sm">TBD</span>
                            <?php endif; ?>
                        </div>
                        <div class="team-row <?= ($winner == $m['team2_id']) ? 'winner-text' : ($winner ? 'opacity-50' : 'text-white') ?>">
                            <?php if($t2): ?>
                                <img src="upload/<?= htmlspecialchars($t2['team_logo']) ?>" class="team-logo" alt="Logo">
                                <span class="truncate w-32"><?= htmlspecialchars($t2['team_name']) ?></span>
                                <?php if($winner == $m['team2_id']): ?><i class="fa-solid fa-check ml-auto"></i><?php endif; ?>
                            <?php else: ?>
                                <div class="team-logo bg-gray-700"></div><span class="text-gray-500 italic text-sm">TBD</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <?php if($tourney['champion_team_id']): 
            $champ = $teams[$tourney['champion_team_id']];
        ?>
        <div class="bracket-round justify-center ml-4">
            <h3 class="text-center text-yellow-500 font-bold mb-4 uppercase tracking-widest text-sm bg-gray-900 py-1 rounded">Champion</h3>
            <div class="match-card champion text-center p-6">
                <i class="fa-solid fa-crown text-3xl text-yellow-500 mb-2 drop-shadow-[0_0_10px_rgba(234,179,8,0.8)]"></i>
                <img src="upload/<?= htmlspecialchars($champ['team_logo']) ?>" class="w-16 h-16 rounded-full mx-auto mb-2 object-cover border-2 border-yellow-500">
                <h4 class="text-xl font-bold text-yellow-400"><?= htmlspecialchars($champ['team_name']) ?></h4>
            </div>
        </div>
        <?php endif; ?>
    </div>

<?php 
    else: echo "<p class='text-center text-red-500'>Tournament not found.</p>";
    endif;
else: 
?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($tournaments as $t): ?>
        <a href="index.php?view=<?= $t['id'] ?>" class="glass-card p-6 rounded-xl block hover:-translate-y-1 hover:shadow-[0_0_15px_rgba(56,189,248,0.3)] transition transform duration-300">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-gray-800 p-3 rounded-lg text-cyan-400">
                    <i class="fa-solid fa-sitemap text-2xl"></i>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded <?= $t['status']=='Live' ? 'bg-green-900 text-green-400' : ($t['status']=='Completed' ? 'bg-purple-900 text-purple-400' : 'bg-gray-700 text-gray-300') ?>">
                    <?= $t['status'] ?>
                </span>
            </div>
            <h3 class="text-xl font-bold text-white mb-2"><?= htmlspecialchars($t['title']) ?></h3>
            <p class="text-gray-400 text-sm"><i class="fa-solid fa-layer-group"></i> <?= $t['round_size'] ?> Teams Bracket</p>
        </a>
        <?php endforeach; ?>
        <?php if(!$tournaments) echo "<p class='text-gray-500 col-span-full text-center'>No tournaments available yet.</p>"; ?>
    </div>
<?php endif; ?>

<?php require_once 'common/bottom.php'; ?>
