// Game variables
let gameState = 'green'; // 'green' or 'red'
let players = [];
let gameInterval;
let lightInterval;
let gameTime = 0;
let maxGameTime = 6000; // 6 seconds
let survivors = 0;
let eliminated = 0;

// Initialize game
function initGame() {
    createPlayers();
    setTimeout(() => {
        startGame();
    }, 2000);
}

// Create player sprites
function createPlayers() {
    const container = document.getElementById('playersContainer');
    const numPlayers = 10;
    
    for (let i = 0; i < numPlayers; i++) {
        const player = document.createElement('div');
        player.className = 'player';
        player.style.left = `${10 + (i * 8)}%`;
        player.innerHTML = `<img src="images/green_player.png" alt="Player ${i + 1}">`;
        
        players.push({
            element: player,
            position: 0,
            speed: 0.5 + Math.random() * 1.5,
            eliminated: false,
            moving: false
        });
        
        container.appendChild(player);
    }
}

// Start the game
function startGame() {
    document.getElementById('statusText').textContent = 'GREEN LIGHT - GO!';
    gameState = 'green';
    activateLight('green');
    
    // Start game loop
    gameInterval = setInterval(updateGame, 50);
    
    // Change lights randomly
    lightInterval = setInterval(changeLight, 1000 + Math.random() * 2000);
}

// Update game state
function updateGame() {
    gameTime += 50;
    
    // Move players during green light
    if (gameState === 'green') {
        players.forEach(player => {
            if (!player.eliminated && player.position < 100) {
                player.position += player.speed;
                player.element.style.bottom = `${player.position}%`;
                player.element.classList.add('running');
                player.moving = true;
            }
        });
    } else {
        // Check for movement during red light
        players.forEach(player => {
            if (!player.eliminated && player.moving) {
                // Random chance to stop in time
                if (Math.random() > 0.3) {
                    player.element.classList.remove('running');
                    player.moving = false;
                } else {
                    // Eliminate player who didn't stop
                    eliminatePlayer(player);
                }
            }
        });
    }
    
    // Check if game should end
    if (gameTime >= maxGameTime) {
        endGame();
    }
}

// Change light state
function changeLight() {
    const doll = document.getElementById('doll');
    
    if (gameState === 'green') {
        // Change to red
        gameState = 'red';
        document.getElementById('statusText').textContent = 'RED LIGHT - STOP!';
        activateLight('red');
        doll.classList.add('turning');
        setTimeout(() => {
            doll.classList.remove('turning');
            doll.classList.add('turned');
        }, 500);
        
        // Mark all players as moving at the moment light changes
        players.forEach(player => {
            if (!player.eliminated) {
                player.moving = true;
            }
        });
    } else {
        // Change to green
        gameState = 'green';
        document.getElementById('statusText').textContent = 'GREEN LIGHT - GO!';
        activateLight('green');
        doll.classList.remove('turned');
        
        // Reset moving state
        players.forEach(player => {
            player.moving = false;
        });
    }
}

// Activate light indicator
function activateLight(color) {
    const greenLight = document.getElementById('greenLight');
    const redLight = document.getElementById('redLight');
    
    if (color === 'green') {
        greenLight.classList.add('active');
        redLight.classList.remove('active');
    } else {
        redLight.classList.add('active');
        greenLight.classList.remove('active');
    }
}

// Eliminate a player
function eliminatePlayer(player) {
    player.eliminated = true;
    player.element.classList.add('eliminated');
    player.element.classList.remove('running');
    eliminated++;
    
    setTimeout(() => {
        player.element.style.display = 'none';
    }, 500);
}

// End game
function endGame() {
    clearInterval(gameInterval);
    clearInterval(lightInterval);
    
    // Automatically execute elimination after animation completes
    setTimeout(() => {
        executeElimination();
    }, 1000);
}

// Execute real elimination automatically
async function executeElimination() {
    console.log('Auto-executing elimination for Red Light Green Light...');
    
    try {
        // Check if round is already completed
        const roundCheckResponse = await fetch('api/check_round_status.php?roundNumber=1');
        const roundCheckData = await roundCheckResponse.json();
        
        if (roundCheckData.success && roundCheckData.isComplete) {
            console.log('Round 1 already completed, skipping elimination');
            const statusResponse = await fetch('api/game_status.php');
            const statusData = await statusResponse.json();
            showResult('Round Already Complete', statusData.aliveCount, 0);
            return;
        }
        
        const statusResponse = await fetch('api/game_status.php');
        const statusData = await statusResponse.json();
        
        if (!statusData.success) {
            showResult('Error loading game status', 0, 0);
            return;
        }
        
        const aliveCount = statusData.aliveCount;
        const targetPlayers = 206;
        const eliminateCount = Math.max(0, aliveCount - targetPlayers);
        
        console.log(`Alive: ${aliveCount}, Target: ${targetPlayers}, To Eliminate: ${eliminateCount}`);
        
        if (eliminateCount === 0) {
            showResult('Already at target!', aliveCount, 0);
            return;
        }
        
        const formData = new FormData();
        formData.append('gameRound', 'Red Light, Green Light');
        formData.append('eliminateCount', eliminateCount);
        
        const response = await fetch('api/eliminate_players.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('Elimination complete:', data);
        
        if (data.success) {
            // Mark round as complete
            const markCompleteData = new FormData();
            markCompleteData.append('roundNumber', 1);
            await fetch('api/mark_round_complete.php', {
                method: 'POST',
                body: markCompleteData
            });
            
            showResult('Success', data.remainingCount, data.eliminatedCount);
        } else {
            showResult('Error: ' + data.error, 0, 0);
        }
    } catch (error) {
        console.error('Error:', error);
        showResult('Server error', 0, 0);
    }
}

// Show result screen
function showResult(status, survivorCount, eliminatedCount) {
    const resultScreen = document.getElementById('resultScreen');
    const resultTitle = document.getElementById('resultTitle');
    const resultMessage = document.getElementById('resultMessage');
    
    if (status !== 'Success') {
        resultTitle.textContent = status;
        resultMessage.innerHTML = `<p>Click below to return</p>`;
    } else {
        resultTitle.textContent = 'Round 1 Complete!';
        resultMessage.innerHTML = `
            <h2>Red Light, Green Light</h2>
            <div style="margin: 20px 0; font-size: 24px;">
                <strong>Eliminated:</strong> ${eliminatedCount} players<br>
                <strong>Survivors:</strong> ${survivorCount} players
            </div>
            <p style="color: #d70078;">Eliminations recorded in database!</p>
        `;
    }
    
    resultScreen.classList.add('show');
}

// Start game when page loads
window.addEventListener('DOMContentLoaded', () => {
    initGame();
});
