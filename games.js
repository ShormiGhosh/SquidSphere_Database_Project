// Load game status on page load
window.addEventListener('DOMContentLoaded', () => {
    loadGameStatus();
    checkGameResult(); // Check if returning from simulation
});

// Check if there's a game result to display
function checkGameResult() {
    const resultData = sessionStorage.getItem('gameResult');
    
    if (resultData) {
        const result = JSON.parse(resultData);
        sessionStorage.removeItem('gameResult'); // Clear it
        
        // Show result popup
        setTimeout(() => {
            showResultPopup(result);
        }, 500);
    }
}

// Show result popup modal
function showResultPopup(result) {
    // Create modal overlay
    const modal = document.createElement('div');
    modal.className = 'result-modal-overlay';
    modal.innerHTML = `
        <div class="result-modal">
            <div class="result-modal-header">
                <h1>${result.icon} Round ${result.round} Complete!</h1>
                <h2>${result.name}</h2>
            </div>
            <div class="result-modal-body">
                ${result.winner ? `
                    <div class="winner-info">
                        <h3>🏆 WINNER DECLARED! 🏆</h3>
                        <p><strong>Player #${result.winner.player_number}</strong></p>
                        <p>${result.winner.name} (Age: ${result.winner.age})</p>
                        <p class="prize-money">${result.prize}</p>
                    </div>
                ` : `
                    <div class="elimination-stats">
                        <div class="stat-box eliminated">
                            <div class="stat-icon">💀</div>
                            <div class="stat-value">${result.eliminated}</div>
                            <div class="stat-label">Eliminated</div>
                        </div>
                        <div class="stat-box survivors">
                            <div class="stat-icon">✅</div>
                            <div class="stat-value">${result.survivors}</div>
                            <div class="stat-label">Survivors</div>
                        </div>
                    </div>
                `}
                <p class="result-note">Real ${result.winner ? 'winner has' : 'eliminations have'} been recorded in the database!</p>
            </div>
            <div class="result-modal-footer">
                <button class="btn-close-modal" onclick="closeResultModal()">Continue</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Animate in
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

// Close result modal
function closeResultModal() {
    const modal = document.querySelector('.result-modal-overlay');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.remove();
            loadGameStatus(); // Refresh stats
        }, 300);
    }
}

// Make closeResultModal globally accessible
window.closeResultModal = closeResultModal;

// Load current game status and update UI
async function loadGameStatus() {
    try {
        const response = await fetch('api/game_status.php');
        const data = await response.json();
        
        if (data.success) {
            // Update stats display
            document.getElementById('aliveCount').textContent = data.aliveCount;
            document.getElementById('eliminatedCount').textContent = data.eliminatedCount;
            document.getElementById('prizeAmount').textContent = data.prizeMoneyFormatted;
            
            // Update game cards based on unlocked rounds
            updateGameCards(data.unlockedRounds || [1], data.currentRound || 1, data.aliveCount);
        }
    } catch (error) {
        console.error('Error loading game status:', error);
    }
}

// Update game card states (locked/unlocked)
function updateGameCards(unlockedRounds, currentRound, aliveCount) {
    const gameCards = document.querySelectorAll('.game-card');
    
    gameCards.forEach((card, index) => {
        const roundNumber = index + 1;
        const isUnlocked = unlockedRounds.includes(roundNumber);
        const statusEl = card.querySelector('.game-status');
        
        if (isUnlocked) {
            card.classList.remove('locked');
            card.classList.add('unlocked');
            
            if (roundNumber === currentRound) {
                statusEl.textContent = '▶️ Current Round';
                statusEl.style.background = 'linear-gradient(135deg, #ffd700, #ffed4e)';
                statusEl.style.color = '#000';
                card.style.borderColor = '#ffd700';
            } else if (roundNumber < currentRound) {
                statusEl.textContent = '✅ Completed';
                statusEl.style.background = 'rgba(76, 175, 80, 0.3)';
                statusEl.style.color = '#4CAF50';
            } else {
                statusEl.textContent = '🔓 Available';
                statusEl.style.background = 'rgba(255, 255, 255, 0.1)';
                statusEl.style.color = 'white';
            }
        } else {
            card.classList.remove('unlocked');
            card.classList.add('locked');
            statusEl.textContent = '🔒 Locked';
            statusEl.style.background = 'rgba(100, 100, 100, 0.3)';
            statusEl.style.color = '#999';
            card.style.borderColor = '#555';
        }
    });
}

// Open game simulation page
function openGame(roundNumber, gameUrl) {
    const card = document.querySelector(`[data-round="${roundNumber}"]`);
    
    if (card.classList.contains('locked')) {
        alert('🔒 This round is locked! Complete previous rounds first.');
        return;
    }
    
    // Store round number in sessionStorage for when simulation completes
    sessionStorage.setItem('currentGameRound', roundNumber);
    
    // Open the game simulation page
    window.location.href = gameUrl;
}

