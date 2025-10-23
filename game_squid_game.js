// Game state
let player1Health = 100;
let player2Health = 100;
let winner = null;

// Start the game automatically
window.addEventListener('load', () => {
    setTimeout(startGame, 1500);
});

function startGame() {
    const statusText = document.getElementById('statusText');
    const player1 = document.getElementById('player1');
    const player2 = document.getElementById('player2');
    const health1 = document.getElementById('health1');
    const health2 = document.getElementById('health2');
    
    let round = 0;
    const maxRounds = 6; // 6 rounds of combat
    
    const battleInterval = setInterval(() => {
        round++;
        
        // Random attack - either player 1 or player 2 attacks
        const attacker = Math.random() > 0.5 ? 1 : 2;
        const damage = Math.floor(Math.random() * 20) + 10; // 10-30 damage
        
        if (attacker === 1) {
            // Player 1 attacks
            player1.classList.add('attacking');
            player2.classList.add('defending');
            player2Health -= damage;
            statusText.textContent = 'Player 067 attacks!';
            
            setTimeout(() => {
                player1.classList.remove('attacking');
                player2.classList.remove('defending');
            }, 500);
        } else {
            // Player 2 attacks
            player2.classList.add('attacking');
            player1.classList.add('defending');
            player1Health -= damage;
            statusText.textContent = 'Player 456 attacks!';
            
            setTimeout(() => {
                player2.classList.remove('attacking');
                player1.classList.remove('defending');
            }, 500);
        }
        
        // Update health bars
        health1.style.width = Math.max(0, player1Health) + '%';
        health2.style.width = Math.max(0, player2Health) + '%';
        
        // Change health bar color based on health
        if (player1Health < 30) {
            health1.style.background = 'linear-gradient(90deg, #ff0000, #cc0000)';
        } else if (player1Health < 60) {
            health1.style.background = 'linear-gradient(90deg, #ffaa00, #ff8800)';
        }
        
        if (player2Health < 30) {
            health2.style.background = 'linear-gradient(90deg, #ff0000, #cc0000)';
        } else if (player2Health < 60) {
            health2.style.background = 'linear-gradient(90deg, #ffaa00, #ff8800)';
        }
        
        // Check for winner
        if (player1Health <= 0 || player2Health <= 0 || round >= maxRounds) {
            clearInterval(battleInterval);
            endGame();
        }
    }, 600);
}

function endGame() {
    const statusText = document.getElementById('statusText');
    const player1 = document.getElementById('player1');
    const player2 = document.getElementById('player2');
    
    // Determine winner based on remaining health
    if (player1Health > player2Health) {
        winner = {
            number: '067',
            image: 'images/green_player2.png'
        };
        player2.classList.add('defeated');
        statusText.textContent = 'Player 067 wins!';
    } else {
        winner = {
            number: '456',
            image: 'images/green_player3.png'
        };
        player1.classList.add('defeated');
        statusText.textContent = 'Player 456 wins!';
    }
    
    // Auto-execute winner declaration after animation
    setTimeout(() => executeWinnerDeclaration(), 2000);
}

// Auto-execute winner declaration
async function executeWinnerDeclaration() {
    try {
        const response = await fetch('api/set_winner.php', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showResult(data.winner);
        } else {
            alert('Error declaring winner!');
            showResult(null);
        }
        
    } catch (error) {
        console.error('Error declaring winner:', error);
        alert('Error declaring winner!');
        showResult(null);
    }
}

function showResult(winnerData) {
    const resultScreen = document.getElementById('resultScreen');
    const resultTitle = document.getElementById('resultTitle');
    const winnerImage = document.getElementById('winnerImage');
    const winnerNumber = document.getElementById('winnerNumber');
    
    resultTitle.textContent = 'Final Winner Declared!';
    winnerImage.src = winner.image;
    
    if (winnerData) {
        winnerNumber.textContent = `Winner: Player ${winnerData.player_number} - ${winnerData.name}`;
    } else {
        winnerNumber.textContent = 'Winner declared!';
    }
    
    resultScreen.classList.add('active');
}
