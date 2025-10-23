// Array to store players
let players = [];

// Available player images
const playerImages = [
    'images/green_player.png',
    'images/green_player2.png',
    'images/green_player3.png'
];

// Load players from database on page load
window.addEventListener('DOMContentLoaded', () => {
    loadPlayers();
});

// Function to get random player image
function getRandomPlayerImage() {
    const randomIndex = Math.floor(Math.random() * playerImages.length);
    return playerImages[randomIndex];
}

// Function to add a new player
function addPlayer() {
    // Create modal for player input
    const modal = document.createElement('div');
    modal.className = 'detail-modal';
    modal.innerHTML = `
        <div class="detail-modal-content">
            <h2>Add New Player</h2>
            <form id="addPlayerForm" onsubmit="submitPlayer(event)">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Age:</label>
                    <input type="number" name="age" min="18" required>
                </div>
                <div class="form-group">
                    <label>Gender:</label>
                    <select name="gender" required>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Debt Amount:</label>
                    <input type="number" name="debt_amount" step="0.01" min="0.01" required>
                </div>
                <div class="form-group">
                    <label>Nationality:</label>
                    <input type="text" name="nationality" required>
                </div>
                <div class="form-group">
                    <label>Alliance Group (Optional):</label>
                    <input type="number" name="alliance_group" min="1">
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="modal-btn confirm-btn">Add Player</button>
                    <button type="button" class="modal-btn cancel-btn" onclick="this.closest('.detail-modal').remove()">Cancel</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Function to submit new player
async function submitPlayer(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('action', 'add');
    
    try {
        const response = await fetch('api/player_api.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Player added successfully!');
            form.closest('.detail-modal').remove();
            loadPlayers(); // Reload players list
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error adding player: ' + error.message);
    }
}

// Function to render all players
function renderPlayers() {
    const playersGrid = document.getElementById('playersGrid');
    playersGrid.innerHTML = '';
    
    if (players.length === 0) {
        playersGrid.innerHTML = '<p style="color: white; text-align: center; grid-column: 1/-1; font-size: 20px;">No players yet. Click "Add Player" to get started!</p>';
        return;
    }
    
    players.forEach(player => {
        const tile = createPlayerTile(player);
        playersGrid.appendChild(tile);
    });
}

// Function to create a player tile
function createPlayerTile(player) {
    const tile = document.createElement('div');
    tile.className = 'player-tile';
    
    // Get random image for display
    const image = getRandomPlayerImage();
    
    // Add status class for styling (eliminated players will be dimmed)
    if (player.status === 'eliminated') {
        tile.classList.add('status-eliminated');
    }
    
    tile.innerHTML = `
        <img src="${image}" alt="Player ${player.player_number}" class="player-image">
        <div class="player-number">#${player.player_number}</div>
        <div class="player-name">${player.name}</div>
    `;
    
    // Add click event to show player details
    tile.addEventListener('click', () => showPlayerDetails(player));
    
    return tile;
}

// Function to show player details in a modal
function showPlayerDetails(player) {
    const image = getRandomPlayerImage();
    const registrationDate = new Date(player.registration_date).toLocaleDateString();
    
    // Create modal overlay
    const modal = document.createElement('div');
    modal.className = 'detail-modal';
    modal.innerHTML = `
        <div class="detail-modal-content">
            <span class="close-modal" onclick="this.parentElement.parentElement.remove()">&times;</span>
            <h2>Player Details</h2>
            <div class="detail-content">
                <img src="${image}" alt="Player ${player.player_number}" class="detail-player-image">
                <div class="detail-info">
                    <div class="detail-row">
                        <span class="detail-label">Player Number:</span>
                        <span class="detail-value">#${player.player_number}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${player.name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Age:</span>
                        <span class="detail-value">${player.age} years</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Gender:</span>
                        <span class="detail-value">${player.gender === 'M' ? 'Male' : player.gender === 'F' ? 'Female' : 'Other'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value status-${player.status}">${player.status.toUpperCase()}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Debt Amount:</span>
                        <span class="detail-value">$${parseFloat(player.debt_amount).toLocaleString()}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nationality:</span>
                        <span class="detail-value">${player.nationality}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Registration Date:</span>
                        <span class="detail-value">${registrationDate}</span>
                    </div>
                    ${player.alliance_group ? `
                    <div class="detail-row">
                        <span class="detail-label">Alliance Group:</span>
                        <span class="detail-value">Group ${player.alliance_group}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
            <div class="modal-buttons">
                <button class="modal-btn confirm-btn" onclick="editPlayer(${player.player_id})">Edit</button>
                <button class="modal-btn cancel-btn" onclick="deletePlayer(${player.player_id})">Delete</button>
            </div>
        </div>
    `;
    
    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    document.body.appendChild(modal);
}

// Function to edit player
function editPlayer(player_id) {
    // Find the player data
    const player = players.find(p => p.player_id == player_id);
    if (!player) return;
    
    // Close current modal
    document.querySelector('.detail-modal').remove();
    
    // Create edit modal
    const modal = document.createElement('div');
    modal.className = 'detail-modal';
    modal.innerHTML = `
        <div class="detail-modal-content">
            <span class="close-modal" onclick="this.parentElement.parentElement.remove()">&times;</span>
            <h2>Edit Player #${player.player_number}</h2>
            <form id="editPlayerForm" onsubmit="submitEditPlayer(event, ${player.player_id})">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" value="${player.name}" required>
                </div>
                <div class="form-group">
                    <label>Age:</label>
                    <input type="number" name="age" min="18" value="${player.age}" required>
                </div>
                <div class="form-group">
                    <label>Gender:</label>
                    <select name="gender" required>
                        <option value="M" ${player.gender === 'M' ? 'selected' : ''}>Male</option>
                        <option value="F" ${player.gender === 'F' ? 'selected' : ''}>Female</option>
                        <option value="Other" ${player.gender === 'Other' ? 'selected' : ''}>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status:</label>
                    <select name="status" required>
                        <option value="alive" ${player.status === 'alive' ? 'selected' : ''}>Alive</option>
                        <option value="eliminated" ${player.status === 'eliminated' ? 'selected' : ''}>Eliminated</option>
                        <option value="winner" ${player.status === 'winner' ? 'selected' : ''}>Winner</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Debt Amount:</label>
                    <input type="number" name="debt_amount" step="0.01" min="0.01" value="${player.debt_amount}" required>
                </div>
                <div class="form-group">
                    <label>Nationality:</label>
                    <input type="text" name="nationality" value="${player.nationality}" required>
                </div>
                <div class="form-group">
                    <label>Alliance Group (Optional):</label>
                    <input type="number" name="alliance_group" min="1" value="${player.alliance_group || ''}">
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="modal-btn confirm-btn">Save Changes</button>
                    <button type="button" class="modal-btn cancel-btn" onclick="this.closest('.detail-modal').remove()">Cancel</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Function to submit player edit
async function submitEditPlayer(event, player_id) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('action', 'update');
    formData.append('player_id', player_id);
    
    try {
        const response = await fetch('api/player_api.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Player updated successfully!');
            form.closest('.detail-modal').remove();
            loadPlayers(); // Reload players list
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error updating player: ' + error.message);
    }
}

// Function to delete player
async function deletePlayer(player_id) {
    const player = players.find(p => p.player_id == player_id);
    
    if (!confirm(`Are you sure you want to delete player #${player.player_number} (${player.name})?\n\nThis action cannot be undone!`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('player_id', player_id);
        
        const response = await fetch('api/player_api.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Player deleted successfully!');
            document.querySelector('.detail-modal').remove();
            loadPlayers(); // Reload players list
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error deleting player: ' + error.message);
    }
}

// Load players from database
async function loadPlayers() {
    const playersGrid = document.getElementById('playersGrid');
    playersGrid.innerHTML = '<p style="color: white; text-align: center; grid-column: 1/-1; font-size: 20px;">Loading players...</p>';
    
    try {
        const response = await fetch('api/player_api.php?action=get_all');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            players = result.players;
            console.log('Loaded players:', players.length);
            renderPlayers();
        } else {
            console.error('Error loading players:', result.message);
            playersGrid.innerHTML = `<p style="color: #ff0000; text-align: center; grid-column: 1/-1; font-size: 20px;">Error: ${result.message}</p>`;
        }
    } catch (error) {
        console.error('Error loading players:', error);
        playersGrid.innerHTML = `<p style="color: #ff0000; text-align: center; grid-column: 1/-1; font-size: 20px;">Error loading players: ${error.message}<br><br>Make sure you have:<br>1. Created the database table<br>2. Generated players using database/generate_players.php</p>`;
    }
}
