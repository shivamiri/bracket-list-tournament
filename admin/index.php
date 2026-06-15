<?php
require_once '../common/config.php';
require_once 'common/header.php';

// Stats
$t_total = $pdo->query("SELECT COUNT(*) FROM tournaments")->fetchColumn();
$t_teams = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
$t_comp = $pdo->query("SELECT COUNT(*) FROM tournaments WHERE status='Completed'")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_tournament'])) {
    $title = sanitize($_POST['title']);
    $size = (int)$_POST['round_size']; // 4, 8, 16, 32

    // Insert Tournament
    $stmt = $pdo->prepare("INSERT INTO tournaments (title, round_size, status) VALUES (?, ?, 'Upcoming')");
    $stmt->execute([$title, $size]);
    $t_id = $pdo->lastInsertId();

    $team_ids = [];
    
    // Process Teams
    for($i = 1; $i <= $size; $i++) {
        $t_name = sanitize($_POST["team_name_$i"]);
        $logo_name = 'default.png';

        if(isset($_FILES["team_logo_$i"]) && $_FILES["team_logo_$i"]['error'] == 0) {
            $ext = pathinfo($_FILES["team_logo_$i"]['name'], PATHINFO_EXTENSION);
            $new_name = uniqid("team_{$t_id}_") . '.' . $ext;
            if(move_uploaded_file($_FILES["team_logo_$i"]['tmp_name'], "../upload/" . $new_name)) {
                $logo_name = $new_name;
            }
        }
        $t_stmt = $pdo->prepare("INSERT INTO teams (tournament_id, team_name, team_logo) VALUES (?, ?, ?)");
        $t_stmt->execute([$t_id, $t_name, $logo_name]);
        $team_ids[] = $pdo->lastInsertId();
    }

    // GENERATE BRACKET
    // We need (size - 1) matches.
    // E.g., for 8 teams: 7 matches. M1..M4 (R1), M5..M6 (R2), M7 (R3)
    $matches_data = [];
    $num_matches_in_round = $size / 2;
    $r = 1;
    
    while($num_matches_in_round >= 1) {
        for($i = 0; $i < $num_matches_in_round; $i++) {
            $matches_data[] = ['round_no' => $r, 'match_no' => $i + 1, 'db_id' => 0];
        }
        $r++;
        $num_matches_in_round /= 2;
    }

    // Insert all matches to generate IDs
    $insert_m = $pdo->prepare("INSERT INTO matches (tournament_id, round_no, match_no) VALUES (?, ?, ?)");
    foreach($matches_data as $k => $md) {
        $insert_m->execute([$t_id, $md['round_no'], $md['match_no']]);
        $matches_data[$k]['db_id'] = $pdo->lastInsertId();
    }

    // Assign Teams to Round 1 Matches
    $t_idx = 0;
    foreach($matches_data as $md) {
        if($md['round_no'] == 1) {
            $team1 = $team_ids[$t_idx++];
            $team2 = $team_ids[$t_idx++];
            $pdo->query("UPDATE matches SET team1_id = $team1, team2_id = $team2 WHERE id = {$md['db_id']}");
        }
    }

    // Assign next_match_id
    // Formula: next match in array is at index: start_of_next_round + floor(current_match_in_round / 2)
    $start_idx = 0;
    $matches_in_r = $size / 2; // e.g., 4
    
    while($matches_in_r > 1) {
        $next_round_start_idx = $start_idx + $matches_in_r;
        for($i = 0; $i < $matches_in_r; $i++) {
            $curr_match = $matches_data[$start_idx + $i];
            $next_match = $matches_data[$next_round_start_idx + floor($i / 2)];
            $pdo->query("UPDATE matches SET next_match_id = {$next_match['db_id']} WHERE id = {$curr_match['db_id']}");
        }
        $start_idx = $next_round_start_idx;
        $matches_in_r /= 2;
    }
    
    echo "<script>alert('Tournament & Bracket Generated Successfully!'); window.location='index.php';</script>";
}

// Delete Tournament
if(isset($_GET['delete'])) {
    $d_id = (int)$_GET['delete'];
    $pdo->query("DELETE FROM tournaments WHERE id = $d_id"); // Cascade deletes teams & matches
    header("Location: index.php");
    exit;
}

$tournaments = $pdo->query("SELECT * FROM tournaments ORDER BY id DESC")->fetchAll();
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass-panel p-6 rounded-lg border-l-4 border-blue-500 flex items-center justify-between">
        <div><p class="text-gray-400 text-sm">Total Tournaments</p><h3 class="text-3xl font-bold"><?= $t_total ?></h3></div>
        <i class="fa-solid fa-trophy text-4xl text-gray-700"></i>
    </div>
    <div class="glass-panel p-6 rounded-lg border-l-4 border-purple-500 flex items-center justify-between">
        <div><p class="text-gray-400 text-sm">Total Teams</p><h3 class="text-3xl font-bold"><?= $t_teams ?></h3></div>
        <i class="fa-solid fa-users text-4xl text-gray-700"></i>
    </div>
    <div class="glass-panel p-6 rounded-lg border-l-4 border-green-500 flex items-center justify-between">
        <div><p class="text-gray-400 text-sm">Completed</p><h3 class="text-3xl font-bold"><?= $t_comp ?></h3></div>
        <i class="fa-solid fa-check-circle text-4xl text-gray-700"></i>
    </div>
</div>

<div class="flex flex-col-reverse md:flex-row gap-8">
    <div class="flex-1 glass-panel p-4 md:p-6 rounded-lg h-fit overflow-x-auto">
        <h3 class="text-xl font-bold mb-4 border-b border-gray-700 pb-2">Manage Tournaments</h3>
        <table class="w-full text-left text-sm whitespace-nowrap md:whitespace-normal">
            <thead class="text-gray-400 bg-gray-800">
                <tr>
                    <th class="p-3 rounded-tl">Name</th>
                    <th class="p-3">Size</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 rounded-tr text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($tournaments as $t): ?>
                <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition">
                    <td class="p-3 font-semibold"><?= htmlspecialchars($t['title']) ?></td>
                    <td class="p-3"><?= $t['round_size'] ?></td>
                    <td class="p-3"><span class="px-2 py-1 rounded text-xs <?= $t['status']=='Live'?'bg-green-900 text-green-300':($t['status']=='Completed'?'bg-purple-900 text-purple-300':'bg-gray-700 text-gray-300') ?>"><?= $t['status'] ?></span></td>
                    <td class="p-3 text-right">
                        <a href="index.php?delete=<?= $t['id'] ?>" onclick="return confirm('Delete this tournament and all data?');" class="text-red-400 hover:text-red-300"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="w-full md:w-1/3 glass-panel p-6 rounded-lg h-fit">
        <h3 class="text-xl font-bold mb-4 border-b border-gray-700 pb-2">Create Tournament</h3>
        <form method="POST" enctype="multipart/form-data" id="tourneyForm">
            <div class="mb-4">
                <label class="block text-sm text-gray-400 mb-1">Tournament Name</label>
                <input type="text" name="title" required placeholder="e.g. SM Anime Award 2026" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white focus:border-blue-500 outline-none">
            </div>
            <div class="mb-4">
                <label class="block text-sm text-gray-400 mb-1">Round Selection (Teams)</label>
                <select name="round_size" id="roundSize" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-white focus:border-blue-500 outline-none" onchange="generateTeamInputs()">
                    <option value="4">4 Teams</option>
                    <option value="8">8 Teams</option>
                    <option value="16">16 Teams</option>
                    <option value="32">32 Teams</option>
                </select>
            </div>
            
            <div id="teamInputsContainer" class="max-h-96 overflow-y-auto pr-2 mb-4 space-y-3 custom-scrollbar">
                </div>

            <button type="submit" name="create_tournament" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-2 rounded transition shadow-lg">Save & Generate Bracket</button>
        </form>
    </div>
</div>

<script>
function generateTeamInputs() {
    const size = document.getElementById('roundSize').value;
    const container = document.getElementById('teamInputsContainer');
    container.innerHTML = '';
    
    for(let i=1; i<=size; i++) {
        let div = document.createElement('div');
        div.className = 'bg-gray-800 p-3 rounded border border-gray-700';
        div.innerHTML = `
            <label class="text-xs text-gray-400 font-bold mb-1 block">Team ${i}</label>
            <input type="text" name="team_name_${i}" required placeholder="Team Name" class="w-full bg-gray-900 border border-gray-600 p-1.5 text-sm rounded text-white mb-2 outline-none focus:border-blue-400">
            <input type="file" name="team_logo_${i}" accept="image/*" class="w-full text-xs text-gray-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-900 file:text-blue-300 hover:file:bg-blue-800">
        `;
        container.appendChild(div);
    }
}
window.onload = generateTeamInputs;
</script>
<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #1e293b; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 4px; }
</style>

<?php require_once 'common/bottom.php'; ?>
