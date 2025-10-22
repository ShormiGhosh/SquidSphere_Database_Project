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
        showResult();
    }, 1500);
}

// Show result screen
function showResult() {
    const resultScreen = document.getElementById('resultScreen');
    const resultTitle = document.getElementById('resultTitle');
    const resultMessage = document.getElementById('resultMessage');
    const resultImage = document.getElementById('resultImage');
    
    resultTitle.textContent = 'Game Complete!';
    resultTitle.className = '';
    resultMessage.innerHTML = `
        <strong style="color: #00ff00;">Survivors:</strong> ${survivors} players<br>
        <strong style="color: #ff0000;">Eliminated:</strong> ${eliminated} players
    `;
    
    // Show success/fail images
    let imagesHTML = '';
    candySlots.forEach((slot) => {
        const img = slot.querySelector('.candy-image');
        imagesHTML += `<img src="${img.src}" style="width: 80px; height: 80px; margin: 5px; object-fit: contain;">`;
    });
    resultImage.innerHTML = imagesHTML;
    
    resultScreen.classList.add('show');
}
