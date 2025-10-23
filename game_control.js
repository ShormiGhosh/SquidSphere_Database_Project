// Load game status on page load
window.addEventListener('DOMContentLoaded', () => {
    loadGameStatus();
});

// Load current game status
async function loadGameStatus() {
    try {
        const response = await fetch('api/game_status.php');
        const data = await response.json();
        
        if (data.success) {
            // Update status cards
            document.getElementById('aliveCount').textContent = data.aliveCount;
            document.getElementById('eliminatedCount').textContent = data.eliminatedCount;
            document.getElementById('prizeMoney').textContent = data.prizeMoneyFormatted;
            document.getElementById('gamePhase').textContent = data.gamePhase;
            
            // Show winner if exists
            if (data.winner) {
                displayWinner(data.winner, data.prizeMoneyFormatted);
            } else {
                document.getElementById('winnerDisplay').style.display = 'none';
            }
            
            // Update unlocked/locked rounds
            updateRoundAvailability(data.unlockedRounds || [], data.currentRound || 1);
            
            // Auto-update elimination inputs based on alive count
            updateEliminationInputs(data.aliveCount);
        }
    } catch (error) {
        console.error('Error loading game status:', error);
    }
}

// Update round availability based on progress
function updateRoundAvailability(unlockedRounds, currentRound) {
    const roundCards = document.querySelectorAll('.round-card');
    
    roundCards.forEach((card, index) => {
        const roundNumber = index + 1;
        const isUnlocked = unlockedRounds.includes(roundNumber);
        const button = card.querySelector('.eliminate-btn, .winner-btn');
        const inputs = card.querySelectorAll('input');
        const roundHeader = card.querySelector('.round-header h3');
        
        if (isUnlocked) {
            // Unlock the round
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
            if (button) button.disabled = false;
            inputs.forEach(input => input.disabled = false);
            
            // Remove locked badge if exists
            const lockedBadge = roundHeader.querySelector('.locked-badge');
            if (lockedBadge) lockedBadge.remove();
            
            // Highlight current round
            if (roundNumber === currentRound) {
                card.style.borderColor = '#ffd700';
                card.style.boxShadow = '0 0 20px rgba(255, 215, 0, 0.4)';
                
                // Add "CURRENT" badge
                let currentBadge = roundHeader.querySelector('.current-badge');
                if (!currentBadge) {
                    currentBadge = document.createElement('span');
                    currentBadge.className = 'current-badge';
                    currentBadge.textContent = '▶ CURRENT';
                    currentBadge.style.cssText = 'background: #ffd700; color: #000; padding: 5px 10px; border-radius: 5px; font-size: 12px; margin-left: 10px; font-weight: bold;';
                    roundHeader.appendChild(currentBadge);
                }
            } else {
                card.style.borderColor = '#d70078';
                card.style.boxShadow = 'none';
                const currentBadge = roundHeader.querySelector('.current-badge');
                if (currentBadge) currentBadge.remove();
            }
        } else {
            // Lock the round
            card.style.opacity = '0.5';
            card.style.pointerEvents = 'none';
            if (button) button.disabled = true;
            inputs.forEach(input => input.disabled = true);
            card.style.borderColor = '#555';
            card.style.boxShadow = 'none';
            
            // Add locked badge
            let lockedBadge = roundHeader.querySelector('.locked-badge');
            if (!lockedBadge) {
                lockedBadge = document.createElement('span');
                lockedBadge.className = 'locked-badge';
                lockedBadge.textContent = '🔒 LOCKED';
                lockedBadge.style.cssText = 'background: #555; color: #999; padding: 5px 10px; border-radius: 5px; font-size: 12px; margin-left: 10px;';
                roundHeader.appendChild(lockedBadge);
            }
            
            // Remove current badge if exists
            const currentBadge = roundHeader.querySelector('.current-badge');
            if (currentBadge) currentBadge.remove();
        }
    });
}

// Update elimination input suggestions
function updateEliminationInputs(aliveCount) {
    if (aliveCount > 200) {
        document.getElementById('round1Eliminate').value = Math.floor(aliveCount * 0.55);
    } else if (aliveCount > 100) {
        document.getElementById('round2Eliminate').value = Math.floor(aliveCount * 0.5);
    } else if (aliveCount > 50) {
        document.getElementById('round3Eliminate').value = Math.floor(aliveCount * 0.5);
    } else if (aliveCount > 25) {
        document.getElementById('round4Eliminate').value = Math.floor(aliveCount * 0.5);
    } else if (aliveCount > 2) {
        document.getElementById('round5Eliminate').value = aliveCount - 2;
    }
}

// Eliminate players for a specific round
async function eliminatePlayers(roundName, inputId) {
    const eliminateCount = parseInt(document.getElementById(inputId).value);
    
    if (!eliminateCount || eliminateCount <= 0) {
        alert('Please enter a valid number of players to eliminate');
        return;
    }
    
    if (!confirm(`Eliminate ${eliminateCount} random players in ${roundName}?`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('gameRound', roundName);
        formData.append('eliminateCount', eliminateCount);
        
        const response = await fetch('api/eliminate_players.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message with details
            const message = `
✅ ${roundName} Complete!

Eliminated: ${data.eliminatedCount} players
Remaining: ${data.aliveCountAfter} players
Prize Money: ₩${data.prizeMoney.toLocaleString()}

Random players eliminated:
${data.eliminatedPlayers.slice(0, 5).map(p => `• ${p.player_number} - ${p.name}`).join('\n')}
${data.eliminatedPlayers.length > 5 ? `... and ${data.eliminatedPlayers.length - 5} more` : ''}
            `;
            
            alert(message);
            
            // Reload game status
            loadGameStatus();
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error eliminating players:', error);
        alert('Error connecting to server');
    }
}

// Declare winner
async function declareWinner() {
    if (!confirm('Declare a random winner from remaining alive players?')) {
        return;
    }
    
    try {
        const response = await fetch('api/set_winner.php', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            const message = `
🏆 Winner Declared! 🏆

Player: ${data.winner.player_number} - ${data.winner.name}
Prize Money: ${data.prizeMoneyFormatted}
Total Eliminated: ${data.totalEliminated} players

The winner takes it all!
            `;
            
            alert(message);
            
            // Reload game status
            loadGameStatus();
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error declaring winner:', error);
        alert('Error connecting to server');
    }
}

// Display winner
function displayWinner(winner, prizeMoney) {
    const winnerDisplay = document.getElementById('winnerDisplay');
    const winnerInfo = document.getElementById('winnerInfo');
    
    const genderText = winner.gender === 'M' ? 'Male' : (winner.gender === 'F' ? 'Female' : winner.gender);
    
    winnerInfo.innerHTML = `
        <div class="winner-card">
            <div class="winner-number">${winner.player_number}</div>
            <h3>${winner.name}</h3>
            <div class="winner-details">
                <p><strong>Age:</strong> ${winner.age}</p>
                <p><strong>Gender:</strong> ${genderText}</p>
                <p><strong>Nationality:</strong> ${winner.nationality}</p>
                <p><strong>Debt:</strong> ₩${Number(winner.debt_amount).toLocaleString()}</p>
            </div>
            <div class="winner-prize">
                <h2>Prize Won: ${prizeMoney}</h2>
            </div>
        </div>
    `;
    
    winnerDisplay.style.display = 'block';
}

// Auto-calculate elimination count
function autoCalculate(round) {
    const aliveCount = parseInt(document.getElementById('aliveCount').textContent);
    
    if (round === 5) {
        // Glass Bridge - leave 2 players
        const toEliminate = Math.max(0, aliveCount - 2);
        document.getElementById('round5Eliminate').value = toEliminate;
    }
}

// Reset game is DISABLED - players progress through rounds
async function resetGame() {
    alert('⚠️ Reset is disabled!\n\nPlayers must progress through rounds sequentially.\nEliminated players cannot be revived.\n\nTo restart, you need to regenerate all 456 players from the database.');
}

// Auto-play all rounds with recommended eliminations
async function autoPlayRounds() {
    // Get current game state
    const statusResponse = await fetch('api/game_status.php');
    const statusData = await statusResponse.json();
    let aliveCount = statusData.aliveCount;
    const currentRound = statusData.currentRound || 1;
    
    if (!confirm(`🎲 Auto-play from Round ${currentRound}?\n\nThis will execute remaining rounds automatically with random eliminations.\n\nContinue?`)) {
        return;
    }
    
    // Round 1: Red Light, Green Light
    if (currentRound <= 1 && aliveCount > 206) {
        await eliminateRound('Red Light Green Light', aliveCount - 206);
        await sleep(1500);
        aliveCount = await getCurrentAliveCount();
    }
    
    // Round 2: Honeycomb
    if (currentRound <= 2 && aliveCount > 106 && aliveCount <= 206) {
        await eliminateRound('Honeycomb', aliveCount - 106);
        await sleep(1500);
        aliveCount = await getCurrentAliveCount();
    }
    
    // Round 3: Tug of War
    if (currentRound <= 3 && aliveCount > 56 && aliveCount <= 106) {
        await eliminateRound('Tug of War', aliveCount - 56);
        await sleep(1500);
        aliveCount = await getCurrentAliveCount();
    }
    
    // Round 4: Marbles
    if (currentRound <= 4 && aliveCount > 28 && aliveCount <= 56) {
        await eliminateRound('Marbles', aliveCount - 28);
        await sleep(1500);
        aliveCount = await getCurrentAliveCount();
    }
    
    // Round 5: Glass Bridge
    if (currentRound <= 5 && aliveCount > 2 && aliveCount <= 28) {
        await eliminateRound('Glass Bridge', aliveCount - 2);
        await sleep(1500);
        aliveCount = await getCurrentAliveCount();
    }
    
    // Round 6: Declare Winner
    if (currentRound <= 6 && aliveCount == 2) {
        await sleep(1500);
        await declareWinner();
    }
    
    alert('🎉 Auto-play complete! Check the results.');
    loadGameStatus();
}

// Helper function for auto-play
async function eliminateRound(roundName, count) {
    const formData = new FormData();
    formData.append('gameRound', roundName);
    formData.append('eliminateCount', count);
    
    const response = await fetch('api/eliminate_players.php', {
        method: 'POST',
        body: formData
    });
    
    return await response.json();
}

// Helper function to get current alive count
async function getCurrentAliveCount() {
    const response = await fetch('api/game_status.php');
    const data = await response.json();
    return data.aliveCount;
}

// Helper sleep function
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
