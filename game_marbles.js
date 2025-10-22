// Game state
let player1Marbles = 10;
let player2Marbles = 10;
let currentRound = 1;
let winner = null;
let predeterminedWinner = Math.random() > 0.5 ? 1 : 2; // Decide winner at start

// Start the game automatically
window.addEventListener('load', () => {
    setTimeout(startGame, 1000);
});

function startGame() {
    const statusText = document.getElementById('statusText');
    const roundDisplay = document.getElementById('roundDisplay');
    const player1 = document.getElementById('player1');
    const player2 = document.getElementById('player2');
    const count1 = document.getElementById('count1');
    const count2 = document.getElementById('count2');
    const marbles1 = document.getElementById('marbles1');
    const marbles2 = document.getElementById('marbles2');
    
    // Run marble betting rounds
    const gameInterval = setInterval(() => {
        if (player1Marbles === 0 || player2Marbles === 0) {
            clearInterval(gameInterval);
            endGame();
            return;
        }
        
        // Determine round winner based on predetermined winner
        // Winner gets more wins to ensure they win all marbles
        let roundWinner;
        if (predeterminedWinner === 1) {
            // Player 1 should win - give them higher chance
            roundWinner = 1;
        } else {
            // Player 2 should win - give them higher chance
            roundWinner = 2;
        }
        
        roundDisplay.textContent = `Round ${currentRound}`;
        
        if (roundWinner === 1) {
            // Player 1 wins this round
            statusText.textContent = 'Player 067 wins the bet!';
            player1.classList.add('celebrating');
            player2.classList.add('losing');
            
            // Transfer marble from player 2 to player 1
            setTimeout(() => {
                const marble2Elements = marbles2.querySelectorAll('.marble:not(.disappear)');
                if (marble2Elements.length > 0) {
                    const lastMarble = marble2Elements[marble2Elements.length - 1];
                    lastMarble.classList.add('transferring');
                    
                    setTimeout(() => {
                        lastMarble.classList.add('disappear');
                        player2Marbles--;
                        player1Marbles++;
                        count1.textContent = player1Marbles;
                        count2.textContent = player2Marbles;
                    }, 200);
                }
            }, 100);
            
            setTimeout(() => {
                player1.classList.remove('celebrating');
                player2.classList.remove('losing');
            }, 300);
            
        } else {
            // Player 2 wins this round
            statusText.textContent = 'Player 456 wins the bet!';
            player2.classList.add('celebrating');
            player1.classList.add('losing');
            
            // Transfer marble from player 1 to player 2
            setTimeout(() => {
                const marble1Elements = marbles1.querySelectorAll('.marble:not(.disappear)');
                if (marble1Elements.length > 0) {
                    const lastMarble = marble1Elements[marble1Elements.length - 1];
                    lastMarble.classList.add('transferring');
                    
                    setTimeout(() => {
                        lastMarble.classList.add('disappear');
                        player1Marbles--;
                        player2Marbles++;
                        count1.textContent = player1Marbles;
                        count2.textContent = player2Marbles;
                    }, 200);
                }
            }, 100);
            
            setTimeout(() => {
                player2.classList.remove('celebrating');
                player1.classList.remove('losing');
            }, 300);
        }
        
        currentRound++;
        
    }, 500); // Each round takes 0.5 seconds
}

function endGame() {
    const statusText = document.getElementById('statusText');
    
    // Determine winner (should have all 20 marbles)
    if (player1Marbles === 20) {
        winner = {
            number: '067',
            image: 'images/green_player2.png',
            marbles: 20
        };
        statusText.textContent = 'Player 067 wins all marbles!';
    } else if (player2Marbles === 20) {
        winner = {
            number: '456',
            image: 'images/green_player3.png',
            marbles: 20
        };
        statusText.textContent = 'Player 456 wins all marbles!';
    }
    
    // Show result after delay
    setTimeout(showResult, 1000);
}

function showResult() {
    const resultScreen = document.getElementById('resultScreen');
    const resultTitle = document.getElementById('resultTitle');
    const winnerImage = document.getElementById('winnerImage');
    const winnerNumber = document.getElementById('winnerNumber');
    const winnerMarbles = document.getElementById('winnerMarbles');
    
    resultTitle.textContent = 'Marbles Champion!';
    winnerImage.src = winner.image;
    winnerNumber.textContent = 'Player ' + winner.number;
    winnerMarbles.textContent = '20 Marbles';
    
    resultScreen.classList.add('active');
}
