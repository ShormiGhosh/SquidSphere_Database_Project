// Game state
let ropePosition = 0; // -100 to 100, negative = Team A winning, positive = Team B winning
let gameInterval;
let winningTeam = null;

// Start the game automatically
window.addEventListener('load', () => {
    setTimeout(startGame, 1000);
});

function startGame() {
    const statusText = document.getElementById('statusText');
    const rope = document.getElementById('rope');
    
    // Simulate rope movement
    let pullCount = 0;
    const maxPulls = 6; // 6 pulls total (about 2.4 seconds)
    
    gameInterval = setInterval(() => {
        pullCount++;
        
        // Random pull (-1 for Team A, 1 for Team B)
        const pull = Math.random() > 0.5 ? 1 : -1;
        ropePosition += pull * 15;
        
        // Apply rope animation
        if (pull < 0) {
            rope.classList.remove('move-right');
            rope.classList.add('move-left');
            statusText.textContent = 'Team A is pulling strong!';
        } else {
            rope.classList.remove('move-left');
            rope.classList.add('move-right');
            statusText.textContent = 'Team B is pulling strong!';
        }
        
        // Remove animation class after it completes
        setTimeout(() => {
            rope.classList.remove('move-left', 'move-right');
        }, 400);
        
        // Check if game should end
        if (pullCount >= maxPulls) {
            clearInterval(gameInterval);
            endGame();
        }
    }, 400);
}

function endGame() {
    const statusText = document.getElementById('statusText');
    const rope = document.getElementById('rope');
    const teamAPlayers = document.querySelectorAll('.team-a .player');
    const teamBPlayers = document.querySelectorAll('.team-b .player');
    
    // Determine winner based on rope position
    if (ropePosition < 0) {
        winningTeam = 'Team A';
        rope.classList.add('final-left');
        statusText.textContent = 'Team A wins!';
        
        // Team B falls
        setTimeout(() => {
            teamBPlayers.forEach((player, index) => {
                setTimeout(() => {
                    player.classList.add('eliminated');
                }, index * 50);
            });
        }, 800);
    } else {
        winningTeam = 'Team B';
        rope.classList.add('final-right');
        statusText.textContent = 'Team B wins!';
        
        // Team A falls
        setTimeout(() => {
            teamAPlayers.forEach((player, index) => {
                setTimeout(() => {
                    player.classList.add('eliminated');
                }, index * 50);
            });
        }, 800);
    }
    
    // Show result after animations
    setTimeout(async () => {
        // Call server-side elimination for tug_of_war
        try {
            // send which team won so server can eliminate the losing team
            const params = new URLSearchParams();
            params.append('action', 'eliminate');
            params.append('game', 'tug_of_war');
            params.append('winningTeam', winningTeam === 'Team A' ? 'A' : 'B');

            const resp = await fetch('api/game_elimination.php?' + params.toString());
            const data = await resp.json();

            if (data.success) {
                showResultDB(data.eliminated, data.remaining);
            } else {
                console.error('Elimination API error:', data.error);
                showResult();
            }
        } catch (e) {
            console.error('Elimination request failed:', e);
            showResult();
        }
    }, 2000);
}

function showResult() {
    const resultScreen = document.getElementById('resultScreen');
    const resultTitle = document.getElementById('resultTitle');
    const winningTeamSpan = document.getElementById('winningTeam');
    const survivorsSpan = document.getElementById('survivors');
    const eliminatedSpan = document.getElementById('eliminated');
    
    resultTitle.textContent = winningTeam + ' Wins!';
    winningTeamSpan.textContent = winningTeam;
    survivorsSpan.textContent = '10 players';
    eliminatedSpan.textContent = '10 players';
    
    resultScreen.classList.add('active');
}

// Show result using DB-updated values from API
function showResultDB(eliminatedCount, remainingCount) {
    const resultScreen = document.getElementById('resultScreen');
    const resultTitle = document.getElementById('resultTitle');
    const winningTeamSpan = document.getElementById('winningTeam');
    const survivorsSpan = document.getElementById('survivors');
    const eliminatedSpan = document.getElementById('eliminated');

    resultTitle.textContent = winningTeam + ' Wins!';
    winningTeamSpan.textContent = winningTeam;
    survivorsSpan.textContent = `${remainingCount} players`;
    eliminatedSpan.textContent = `${eliminatedCount} players`;

    resultScreen.classList.add('active');
}
