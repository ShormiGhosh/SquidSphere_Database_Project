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
    
    // Count survivors
    survivors = players.filter(p => !p.eliminated && p.position >= 60).length;
    eliminated = players.filter(p => p.eliminated).length;
    
    // Show result
    setTimeout(() => {
        showResult();
    }, 1000);
}

// Show result screen
function showResult() {
    const resultScreen = document.getElementById('resultScreen');
    const resultTitle = document.getElementById('resultTitle');
    const resultMessage = document.getElementById('resultMessage');
    
    resultTitle.textContent = 'Game Complete!';
    resultMessage.innerHTML = `
        <strong>Survivors:</strong> ${survivors} players<br>
        <strong>Eliminated:</strong> ${eliminated} players
    `;
    
    resultScreen.classList.add('show');
}

// Start game when page loads
window.addEventListener('DOMContentLoaded', () => {
    initGame();
});
