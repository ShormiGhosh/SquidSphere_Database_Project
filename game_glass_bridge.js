// Game state
let players = [
    { number: '001', eliminated: false },
    { number: '067', eliminated: false },
    { number: '101', eliminated: false },
    { number: '218', eliminated: false },
    { number: '240', eliminated: false },
    { number: '278', eliminated: false },
    { number: '324', eliminated: false },
    { number: '456', eliminated: false }
];

let currentPlayerIndex = 0;
let currentStep = 0;
const totalSteps = 6; // 6 steps on the bridge (reduced from 10)
let bridgePattern = []; // 0 = left is safe, 1 = right is safe
let survivorsNeeded = 2; // Exactly 2 players must survive
let survivorCount = 0;

// Start the game automatically
window.addEventListener('load', () => {
    generateBridge();
    setTimeout(startGame, 500);
});

function generateBridge() {
    const bridgeSteps = document.getElementById('bridgeSteps');
    
    // Generate random safe pattern
    for (let i = 0; i < totalSteps; i++) {
        bridgePattern.push(Math.random() > 0.5 ? 1 : 0);
        
        const stepDiv = document.createElement('div');
        stepDiv.className = 'bridge-step';
        stepDiv.id = `step-${i}`;
        
        // Left panel
        const leftPanel = document.createElement('div');
        leftPanel.className = 'glass-panel';
        leftPanel.dataset.step = i;
        leftPanel.dataset.side = 'left';
        
        // Right panel
        const rightPanel = document.createElement('div');
        rightPanel.className = 'glass-panel';
        rightPanel.dataset.step = i;
        rightPanel.dataset.side = 'right';
        
        stepDiv.appendChild(leftPanel);
        stepDiv.appendChild(rightPanel);
        bridgeSteps.appendChild(stepDiv);
    }
}

function startGame() {
    processNextPlayer();
}

function processNextPlayer() {
    if (currentPlayerIndex >= players.length) {
        endGame();
        return;
    }
    
    // Skip eliminated players
    if (players[currentPlayerIndex].eliminated) {
        currentPlayerIndex++;
        processNextPlayer();
        return;
    }
    
    // Highlight current player
    const playerItems = document.querySelectorAll('.player-item');
    playerItems.forEach((item, index) => {
        item.classList.remove('active');
        if (index === currentPlayerIndex) {
            item.classList.add('active');
        }
    });
    
    const statusText = document.getElementById('statusText');
    statusText.textContent = `Player ${players[currentPlayerIndex].number} is crossing...`;
    
    // Player attempts to cross
    attemptCrossing();
}

function attemptCrossing() {
    // Check if we already have enough survivors
    if (survivorCount >= survivorsNeeded) {
        // Force elimination for remaining players
        const stepElement = document.getElementById(`step-${currentStep}`);
        const chosenPanel = stepElement.querySelector(`[data-side="left"]`);
        
        setTimeout(() => {
            chosenPanel.classList.add('broken');
            playerEliminated();
        }, 150);
        return;
    }
    
    // For players who should survive (first 2 players)
    const shouldSurvive = survivorCount < survivorsNeeded;
    
    // Determine choice based on whether player should survive
    let choice, safeChoice;
    
    if (shouldSurvive) {
        // Make correct choices to survive
        safeChoice = bridgePattern[currentStep];
        choice = safeChoice;
    } else {
        // Make wrong choice to be eliminated
        safeChoice = bridgePattern[currentStep];
        choice = safeChoice === 0 ? 1 : 0; // Choose the wrong one
    }
    
    const stepElement = document.getElementById(`step-${currentStep}`);
    const chosenPanel = stepElement.querySelector(`[data-side="${choice === 0 ? 'left' : 'right'}"]`);
    const otherPanel = stepElement.querySelector(`[data-side="${choice === 0 ? 'right' : 'left'}"]`);
    
    setTimeout(() => {
        if (choice === safeChoice) {
            // Safe! Continue
            chosenPanel.classList.add('safe', 'strong');
            currentStep++;
            
            if (currentStep >= totalSteps) {
                // Player survived!
                playerSurvived();
            } else {
                // Continue to next step
                setTimeout(attemptCrossing, 200);
            }
        } else {
            // Wrong choice! Player falls
            chosenPanel.classList.add('broken');
            playerEliminated();
        }
    }, 150);
}

function playerEliminated() {
    const statusText = document.getElementById('statusText');
    statusText.textContent = `Player ${players[currentPlayerIndex].number} fell!`;
    
    players[currentPlayerIndex].eliminated = true;
    
    const playerItems = document.querySelectorAll('.player-item');
    playerItems[currentPlayerIndex].classList.add('eliminated');
    playerItems[currentPlayerIndex].classList.remove('active');
    
    updateSurvivorCount();
    
    // Next player after delay
    setTimeout(() => {
        currentPlayerIndex++;
        currentStep = 0; // Reset to start for next player
        processNextPlayer();
    }, 400);
}

function playerSurvived() {
    const statusText = document.getElementById('statusText');
    statusText.textContent = `Player ${players[currentPlayerIndex].number} made it across!`;
    
    const playerItems = document.querySelectorAll('.player-item');
    playerItems[currentPlayerIndex].classList.add('survived');
    playerItems[currentPlayerIndex].classList.remove('active');
    
    survivorCount++; // Increment survivor count
    
    // Next player after delay
    setTimeout(() => {
        currentPlayerIndex++;
        currentStep = 0; // Reset to start for next player
        processNextPlayer();
    }, 400);
}

function updateSurvivorCount() {
    const survivorCount = document.getElementById('survivorCount');
    const alive = players.filter(p => !p.eliminated).length;
    survivorCount.textContent = alive;
}

function endGame() {
    const statusText = document.getElementById('statusText');
    const survivors = players.filter(p => !p.eliminated).length;
    const eliminated = players.length - survivors;
    
    statusText.textContent = 'Game Over!';
    
    setTimeout(showResult, 800);
}

function showResult() {
    const resultScreen = document.getElementById('resultScreen');
    const survivorsSpan = document.getElementById('survivors');
    const eliminatedSpan = document.getElementById('eliminated');
    
    const survivors = players.filter(p => !p.eliminated).length;
    const eliminated = players.length - survivors;
    
    survivorsSpan.textContent = `${survivors} players`;
    eliminatedSpan.textContent = `${eliminated} players`;
    
    resultScreen.classList.add('active');
}
