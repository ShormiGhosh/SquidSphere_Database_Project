// Game variables
let candySlots = [];
let gameActive = true;
let survivors = 0;
let eliminated = 0;

// Initialize game
window.addEventListener('DOMContentLoaded', () => {
    startGame();
});

// Start automated game
function startGame() {
    candySlots = document.querySelectorAll('.candy-slot');
    
    // Animate each candy slot randomly
    candySlots.forEach((slot, index) => {
        setTimeout(() => {
            animateSlot(slot, index);
        }, index * 500);
    });
    
    // End game after 5-6 seconds
    const gameTime = 5000 + Math.random() * 1000;
    setTimeout(() => {
        endGame();
    }, gameTime);
}

// Animate individual candy slot
function animateSlot(slot, index) {
    slot.classList.add('working');
    
    // Random result after 3-4 seconds
    const resultTime = 3000 + Math.random() * 1000;
    setTimeout(() => {
        const success = Math.random() > 0.5; // 50% chance
        
        if (success) {
            slot.classList.remove('working');
            slot.classList.add('success');
            survivors++;
        } else {
            slot.classList.remove('working');
            slot.classList.add('failed');
            // Change to broken candy
            const candyImg = slot.querySelector('.candy-image');
            candyImg.src = 'images/broken-candy.png';
            candyImg.classList.add('broken');
            eliminated++;
        }
    }, resultTime);
}

// End game and show results
function endGame() {
    gameActive = false;
    
    setTimeout(() => {
        executeElimination();
    }, 1500);
}

// Execute real elimination automatically
async function executeElimination() {
    try {
        const statusResponse = await fetch('api/game_status.php');
        const statusData = await statusResponse.json();
        
        if (!statusData.success) {
            showResult('Error', 0, 0);
            return;
        }
        
        const aliveCount = statusData.aliveCount;
        const targetPlayers = 106;
        let eliminateCount = aliveCount - targetPlayers;
        
        if (eliminateCount < 0) {
            showResult('Not enough players!', aliveCount, 0);
            return;
        }
        
        if (eliminateCount === 0) {
            showResult('Already at target', aliveCount, 0);
            return;
        }
        
        const formData = new FormData();
        formData.append('gameRound', 'Honeycomb');
        formData.append('eliminateCount', eliminateCount);
        
        const response = await fetch('api/eliminate_players.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showResult('Success', data.remainingCount, data.eliminatedCount);
        } else {
            showResult('Error: ' + data.error, 0, 0);
        }
    } catch (error) {
        showResult('Server error', 0, 0);
    }
}

// Show result screen
function showResult(status, survivorCount, eliminatedCount) {
    const resultScreen = document.getElementById('resultScreen');
    const resultTitle = document.getElementById('resultTitle');
    const resultMessage = document.getElementById('resultMessage');
    const resultImage = document.getElementById('resultImage');
    
    if (status !== 'Success') {
        resultTitle.textContent = status;
        resultMessage.innerHTML = `<p>Click below to return</p>`;
    } else {
        resultTitle.textContent = 'Round 2 Complete!';
        resultMessage.innerHTML = `
            <h2>Honeycomb</h2>
            <div style="margin: 20px 0; font-size: 24px;">
                <strong>Eliminated:</strong> ${eliminatedCount} players<br>
                <strong>Survivors:</strong> ${survivorCount} players
            </div>
            <p style="color: #d70078;">Eliminations recorded in database!</p>
        `;
    }
    
    // Show candy images
    let imagesHTML = '';
    candySlots.forEach((slot) => {
        const img = slot.querySelector('.candy-image');
        imagesHTML += `<img src="${img.src}" style="width: 80px; height: 80px; margin: 5px; object-fit: contain;">`;
    });
    resultImage.innerHTML = imagesHTML;
    
    resultScreen.classList.add('show');
}
