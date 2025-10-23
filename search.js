// Search Players Function
async function searchPlayers() {
    const filters = {
        playerNumber: document.getElementById('playerNumber').value,
        playerName: document.getElementById('playerName').value,
        gender: document.getElementById('gender').value,
        minAge: document.getElementById('minAge').value,
        maxAge: document.getElementById('maxAge').value,
        nationality: document.getElementById('nationality').value,
        minDebt: document.getElementById('minDebt').value,
        maxDebt: document.getElementById('maxDebt').value,
        status: document.getElementById('status').value,
        advancedQuery: document.getElementById('advancedQuery').value,
        sortBy: document.getElementById('sortBy').value,
        limit: document.getElementById('limitResults').value || 100
    };

    // Build query string
    const queryParams = new URLSearchParams();
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            queryParams.append(key, filters[key]);
        }
    });

    try {
        const response = await fetch(`api/search_players.php?${queryParams.toString()}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();

        if (data.success) {
            displayResults(data.players, data.count);
            displayQueryInfo(data.sql, data.count);
        } else {
            console.error('Search error:', data.error);
            alert('Error searching players: ' + (data.error || 'Unknown error'));
            if (data.sql) {
                console.log('SQL Query:', data.sql);
            }
        }
    } catch (error) {
        console.error('Search error:', error);
        alert('Error connecting to server: ' + error.message);
    }
}

// Display results in tile format (same as players page)
function displayResults(players, count) {
    const resultsGrid = document.getElementById('searchResults');
    const resultCount = document.getElementById('resultCount');
    
    resultCount.textContent = count;
    
    if (players.length === 0) {
        resultsGrid.innerHTML = '<p class="no-results">No players found matching your criteria.</p>';
        return;
    }
    
    resultsGrid.innerHTML = '';
    
    players.forEach(player => {
        const tile = createPlayerTile(player);
        resultsGrid.appendChild(tile);
    });
}

// Create player tile (same style as players page)
function createPlayerTile(player) {
    const tile = document.createElement('div');
    tile.className = 'player-tile';
    
    // Apply eliminated status styling
    if (player.status === 'eliminated') {
        tile.classList.add('status-eliminated');
    }
    
    // Convert gender code to full text
    const genderText = player.gender === 'M' ? 'Male' : (player.gender === 'F' ? 'Female' : player.gender);
    
    // Check if this is a JOIN result with game information
    const hasGameInfo = player.game_name || player.round_number;
    const hasSimilarCount = player.similar_debt_players !== undefined;
    
    let playerInfoHTML = `
        <div class="player-number">${player.player_number || 'N/A'}</div>
        <div class="player-info">
            <div class="player-name">${player.name || 'No Player Data'}</div>
    `;
    
    // Add player details if available
    if (player.age) {
        playerInfoHTML += `<div class="player-detail">Age: ${player.age}</div>`;
    }
    if (player.gender) {
        playerInfoHTML += `<div class="player-detail">Gender: ${genderText}</div>`;
    }
    if (player.nationality) {
        playerInfoHTML += `<div class="player-detail">Nationality: ${player.nationality}</div>`;
    }
    if (player.debt_amount) {
        playerInfoHTML += `<div class="player-detail">Debt: ₩${Number(player.debt_amount).toLocaleString()}</div>`;
    }
    
    // Add JOIN-specific information
    if (hasGameInfo) {
        playerInfoHTML += `<div class="player-detail" style="color: #ffd700; font-weight: bold;">Game: ${player.game_name || 'N/A'} (Round ${player.round_number || 'N/A'})</div>`;
    }
    
    if (hasSimilarCount) {
        playerInfoHTML += `<div class="player-detail" style="color: #4CAF50; font-weight: bold;">Similar Debt Players: ${player.similar_debt_players}</div>`;
    }
    
    // Add status if available
    if (player.status) {
        playerInfoHTML += `<div class="player-status status-${player.status}">${player.status.toUpperCase()}</div>`;
    }
    
    playerInfoHTML += `</div>`;
    tile.innerHTML = playerInfoHTML;
    
    return tile;
}

// Display SQL query information
function displayQueryInfo(sql, count) {
    const queryInfo = document.getElementById('queryInfo');
    
    // Format SQL for display (order matters - replace longer phrases first!)
    const formattedSQL = sql
        .replace(/ORDER BY/gi, '<span class="sql-keyword">ORDER BY</span>')
        .replace(/GROUP BY/gi, '<span class="sql-keyword">GROUP BY</span>')
        .replace(/INNER JOIN/gi, '<span class="sql-keyword">INNER JOIN</span>')
        .replace(/LEFT JOIN/gi, '<span class="sql-keyword">LEFT JOIN</span>')
        .replace(/RIGHT JOIN/gi, '<span class="sql-keyword">RIGHT JOIN</span>')
        .replace(/CROSS JOIN/gi, '<span class="sql-keyword">CROSS JOIN</span>')
        .replace(/NOT IN/gi, '<span class="sql-keyword">NOT IN</span>')
        .replace(/SELECT/gi, '<span class="sql-keyword">SELECT</span>')
        .replace(/FROM/gi, '<span class="sql-keyword">FROM</span>')
        .replace(/WHERE/gi, '<span class="sql-keyword">WHERE</span>')
        .replace(/AND/gi, '<span class="sql-keyword">AND</span>')
        .replace(/OR/gi, '<span class="sql-keyword">OR</span>')
        .replace(/IN/gi, '<span class="sql-keyword">IN</span>')
        .replace(/LIKE/gi, '<span class="sql-keyword">LIKE</span>')
        .replace(/LIMIT/gi, '<span class="sql-keyword">LIMIT</span>')
        .replace(/UNION/gi, '<span class="sql-keyword">UNION</span>')
        .replace(/EXISTS/gi, '<span class="sql-keyword">EXISTS</span>')
        .replace(/DISTINCT/gi, '<span class="sql-keyword">DISTINCT</span>')
        .replace(/BETWEEN/gi, '<span class="sql-keyword">BETWEEN</span>')
        .replace(/AVG/gi, '<span class="sql-keyword">AVG</span>')
        .replace(/MAX/gi, '<span class="sql-keyword">MAX</span>')
        .replace(/MIN/gi, '<span class="sql-keyword">MIN</span>')
        .replace(/COUNT/gi, '<span class="sql-keyword">COUNT</span>')
        .replace(/HAVING/gi, '<span class="sql-keyword">HAVING</span>')
        .replace(/ON/gi, '<span class="sql-keyword">ON</span>')
        .replace(/JOIN/gi, '<span class="sql-keyword">JOIN</span>');
    
    queryInfo.innerHTML = `
        <div class="query-box">
            <strong>📊 SQL Query Used (${count} results):</strong>
            <pre class="sql-code">${formattedSQL}</pre>
        </div>
    `;
}

// Reset all filters
function resetFilters() {
    document.getElementById('playerNumber').value = '';
    document.getElementById('playerName').value = '';
    document.getElementById('gender').value = '';
    document.getElementById('minAge').value = '';
    document.getElementById('maxAge').value = '';
    document.getElementById('nationality').value = '';
    document.getElementById('minDebt').value = '';
    document.getElementById('maxDebt').value = '';
    document.getElementById('status').value = '';
    document.getElementById('advancedQuery').value = '';
    document.getElementById('sortBy').value = 'player_number ASC';
    document.getElementById('limitResults').value = '100';
    
    document.getElementById('searchResults').innerHTML = '<p class="no-results">Use filters above to search players...</p>';
    document.getElementById('resultCount').textContent = '0';
    document.getElementById('queryInfo').innerHTML = '';
}

// Load all players on initial load (optional)
window.addEventListener('DOMContentLoaded', () => {
    // Optionally load all players by default
    // searchPlayers();
});
