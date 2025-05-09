<?php
session_start();

// Inicializacija ob začetku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['names'], $_POST['rolls'], $_POST['rounds'])) {
    $_SESSION['players'] = array_map('htmlspecialchars', $_POST['names']);
    $_SESSION['rolls'] = (int)$_POST['rolls'];
    $_SESSION['rounds'] = (int)$_POST['rounds'];
    $_SESSION['currentRound'] = 1;
    $_SESSION['results'] = array_fill(0, count($_SESSION['players']), []);
}
elseif (!isset($_SESSION['players'])) {
    header('Location: index.html');
    exit();
}

$players = $_SESSION['players'];
$rollsPerRound = $_SESSION['rolls'];
$totalRounds = $_SESSION['rounds'];
$currentRound = $_SESSION['currentRound'];
$results = &$_SESSION['results'];

// Generiraj mete kock za trenutni krog
$roundSums = [];
$diceImages = [];
foreach ($players as $i => $player) {
    $sum = 0;
    $diceRolls = [];
    for ($j = 0; $j < $rollsPerRound; $j++) {
        $roll = rand(1, 6);
        $sum += $roll;
        $diceRolls[] = "<img src='./img/dice$roll.gif' alt='$roll'>";
    }
    $results[$i][] = $sum;
    $roundSums[] = $sum;
    $diceImages[] = $diceRolls;
}

// Izračun skupnih seštevkov po vseh krogih
$totals = array_map(function($playerResults) {
    return array_sum($playerResults);
}, $results);

// Določanje zmagovalca (na koncu)
$winners = [];
if ($currentRound >= $totalRounds) {
    $maxTotal = max($totals);
    foreach ($totals as $i => $total) {
        if ($total === $maxTotal) {
            $winners[] = $players[$i];
        }
    }
    session_destroy();
}

?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Rezultati - Krog <?php echo $currentRound; ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        .container-no-animation {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .results {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 20px;
            margin: 30px 0;
        }
        .player-card {
            flex: 0 0 calc(33.33% - 20px);
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        @media (max-width: 900px) {
            .player-card {
                flex: 0 0 calc(50% - 15px);
            }
        }
        @media (max-width: 600px) {
            .player-card {
                flex: 0 0 100%;
            }
        }
    </style>
</head>
<body>
<div class="container-no-animation">
    <header>
        <h1>Round <?php echo $currentRound; ?> / <?php echo $totalRounds; ?></h1>
        <?php if ($currentRound >= $totalRounds): ?>
            <p class="subtitle">Final Results</p>
        <?php else: ?>
            <p class="subtitle">Current Round</p>
        <?php endif; ?>
    </header>

    <div class="game-card">
        <div class="results">
            <?php foreach ($players as $i => $player): ?>
                <div class="player-card">
                    <h2><?php echo $player; ?></h2>
                    <div class="dice">
                        <?php echo implode('', $diceImages[$i]); ?>
                    </div>
                    <div class="round-sum">Sum of this round: <strong><?php echo $roundSums[$i]; ?></strong></div>
                    <?php if ($currentRound >= $totalRounds): ?>
                        <div class="total-sum">Total sum: <strong><?php echo $totals[$i]; ?></strong></div>
                        <?php if (in_array($player, $winners)): ?>
                            <div class="winner-badge">Winner!</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="actions">
            <?php if ($currentRound >= $totalRounds): ?>
                <a href="index.html"><button class="btn-primary">Start again</button></a>
            <?php else: ?>
                <?php $_SESSION['currentRound']++; ?>
                <form method="post">
                    <button type="submit" class="btn-primary">Next round</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>