// Load game status on page load
window.addEventListener('DOMContentLoaded', () => {
    loadGameStatus();
});

// Load current game status
async function loadGameStatus() {
    try {
        const response = await fetch('api/game_elimination.php?action=status');
        const data = await response.json();

        if (data.success) {
            updateStatusDisplay(data);
        } else {
            alert('Error loading game status: ' + data.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load game status');
    }
}

// Update status display
function updateStatusDisplay(data) {
    // Update counts
    document.getElementById('aliveCount').textContent = data.status.alive;
    document.getElementById('eliminatedCount').textContent = data.status.eliminated;
    document.getElementById('winnerCount').textContent = data.status.winner;
    document.getElementById('prizeMoney').textContent = '₩' + data.prize_money.toLocaleString();

    // Update current round status
    const roundText = {
        'not_started': 'Ready to Start - All 456 Players Ready',
        'ready_for_round_1': 'Ready for Round 1: Red Light Green Light',
        'round_1_completed': 'Round 1 Complete - Ready for Round 2: Honeycomb',
        'round_2_completed': 'Round 2 Complete - Ready for Round 3: Tug of War',
        'round_3_completed': 'Round 3 Complete - Ready for Round 4: Marbles',
        'round_4_completed': 'Round 4 Complete - Ready for Round 5: Glass Bridge',
        'round_5_completed': 'Round 5 Complete - Ready for Final: Squid Game',
        'game_over_winner': 'GAME OVER - WE HAVE A WINNER!'
    };

    document.getElementById('currentRound').textContent = 
        roundText[data.current_round] || 'Game in Progress';

    // Show winner panel if there's a winner
    if (data.status.winner > 0) {
        showWinnerPanel();
    }

    // Update round statuses
    updateRoundStatuses(data.status.alive, data.status.eliminated);
}

// Update individual round statuses
function updateRoundStatuses(alive, eliminated) {
    // Round 1: Red Light Green Light
    if (eliminated === 0) {
        setRoundStatus('round1Status', 'Not Started', 'pending');
    } else if (alive < 456 && alive > 250) {
        setRoundStatus('round1Status', 'Completed', 'completed');
    } else if (alive <= 250) {
        setRoundStatus('round1Status', 'Completed', 'completed');
    }

    // Round 2: Honeycomb
    if (alive < 250 && alive > 120) {
        setRoundStatus('round2Status', 'Completed', 'completed');
    } else if (alive > 250) {
        setRoundStatus('round2Status', 'Not Started', 'pending');
    }

    // Round 3: Tug of War
    if (alive < 120 && alive > 40) {
        setRoundStatus('round3Status', 'Completed', 'completed');
    } else if (alive >= 120) {
        setRoundStatus('round3Status', 'Not Started', 'pending');
    }

    // Round 4: Marbles
    if (alive < 40 && alive > 10) {
        setRoundStatus('round4Status', 'Completed', 'completed');
    } else if (alive >= 40) {
        setRoundStatus('round4Status', 'Not Started', 'pending');
    }

    // Round 5: Glass Bridge
    if (alive <= 10 && alive > 1) {
        setRoundStatus('round5Status', 'Completed', 'completed');
    } else if (alive > 10) {
        setRoundStatus('round5Status', 'Not Started', 'pending');
    }

    // Round 6: Squid Game
    if (alive === 1) {
        setRoundStatus('round6Status', 'WINNER DETERMINED', 'winner');
    } else if (alive === 2) {
        setRoundStatus('round6Status', 'Ready for Final', 'ready');
    } else {
        setRoundStatus('round6Status', 'Not Started', 'pending');
    }
}

// Set round status
function setRoundStatus(elementId, text, cssClass) {
    const element = document.getElementById(elementId);
    element.textContent = text;
    element.className = 'round-status ' + cssClass;
}

// Eliminate players for a specific game
async function eliminateGame(game) {
    const gameNames = {
        'red_light': 'Red Light Green Light',
        'honeycomb': 'Honeycomb',
        'tug_of_war': 'Tug of War',
        'marbles': 'Marbles',
        'glass_bridge': 'Glass Bridge',
        'squid_game': 'Squid Game (Final)'
    };

    const confirmMessage = game === 'squid_game' 
        ? 'This will determine the WINNER! Only 1 player will remain. Continue?'
        : `This will eliminate players in ${gameNames[game]}. Continue?`;

    if (!confirm(confirmMessage)) {
        return;
    }

    try {
        const response = await fetch(`api/game_elimination.php?action=eliminate&game=${game}`);
        const data = await response.json();

        if (data.success) {
            if (data.winner) {
                // Show winner announcement
                showWinnerAnnouncement(data);
            } else {
                // Show elimination result
                alert(`${data.message}\n\n` +
                      `Game: ${data.game}\n` +
                      `Eliminated: ${data.eliminated} players\n` +
                      `Remaining: ${data.remaining} players`);
            }
            
            // Reload status
            loadGameStatus();
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to eliminate players');
    }
}

// Show winner announcement
function showWinnerAnnouncement(data) {
    const message = `
        WINNER ANNOUNCEMENT!
        
        Player #${data.winner.player_number}
        ${data.winner.name}
        
        Prize Money: ₩${data.prize_money.toLocaleString()}
        (${data.eliminated} eliminated players × ₩100,000,000)
        
        CONGRATULATIONS!
    `;
    
    alert(message);
    showWinnerPanel(data.winner, data.prize_money);
}

// Show winner panel
function showWinnerPanel(winner, prize) {
    const panel = document.getElementById('winnerPanel');
    const info = document.getElementById('winnerInfo');
    
    if (winner) {
        info.innerHTML = `
            <div class="winner-player">
                <div class="winner-number">${winner.player_number}</div>
                <div class="winner-name">${winner.name}</div>
            </div>
            <div class="winner-prize">₩${prize.toLocaleString()}</div>
            <p class="winner-text">Takes home the prize!</p>
        `;
    }
    
    panel.style.display = 'block';
}

// Reset all players to alive
async function resetGame() {
    if (!confirm('⚠️ This will reset ALL players to alive status. Are you sure?')) {
        return;
    }

    try {
        const response = await fetch('api/game_elimination.php?action=reset');
        const data = await response.json();

        if (data.success) {
            alert('Game Reset!\n\nAll 456 players are now alive.');
            document.getElementById('winnerPanel').style.display = 'none';
            loadGameStatus();
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to reset game');
    }
}
