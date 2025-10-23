// Global variables to store team data
let currentTeams = null;

// Form teams based on selected strategy
async function formTeams() {
    const strategy = document.querySelector('input[name="strategy"]:checked');
    
    if (!strategy) {
        alert('Please select a team formation strategy!');
        return;
    }
    
    const strategyValue = strategy.value;
    
    if (!confirm(`Form teams using ${strategy.parentElement.querySelector('h3').textContent} strategy?`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('strategy', strategyValue);
        
        const response = await fetch('api/form_teams.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentTeams = data;
            displayTeams(data);
            
            // Hide formation section, show teams section
            document.getElementById('formationSection').style.display = 'none';
            document.getElementById('teamsSection').style.display = 'block';
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error forming teams:', error);
        alert('Error connecting to server');
    }
}

// Display teams
function displayTeams(data) {
    // Display strategy used
    document.getElementById('strategyUsed').textContent = data.strategy;
    
    // Display Team A
    document.getElementById('teamACount').textContent = data.teamA.count;
    const teamAContainer = document.getElementById('teamAPlayers');
    teamAContainer.innerHTML = '';
    
    data.teamA.players.forEach(player => {
        const playerDiv = document.createElement('div');
        playerDiv.className = 'player-item';
        playerDiv.innerHTML = `
            <span class="player-number">#${player.player_number}</span>
            <span class="player-name">${player.name}</span>
            <span class="player-info">
                ${player.age}y, ${player.gender === 'M' ? '♂' : player.gender === 'F' ? '♀' : '⚥'}
            </span>
        `;
        teamAContainer.appendChild(playerDiv);
    });
    
    // Display Team B
    document.getElementById('teamBCount').textContent = data.teamB.count;
    const teamBContainer = document.getElementById('teamBPlayers');
    teamBContainer.innerHTML = '';
    
    data.teamB.players.forEach(player => {
        const playerDiv = document.createElement('div');
        playerDiv.className = 'player-item';
        playerDiv.innerHTML = `
            <span class="player-number">#${player.player_number}</span>
            <span class="player-name">${player.name}</span>
            <span class="player-info">
                ${player.age}y, ${player.gender === 'M' ? '♂' : player.gender === 'F' ? '♀' : '⚥'}
            </span>
        `;
        teamBContainer.appendChild(playerDiv);
    });
}

// Eliminate the losing team
async function eliminateTeam(losingTeam) {
    if (!currentTeams) {
        alert('No teams formed yet!');
        return;
    }
    
    const winningTeam = losingTeam === 'Team A' ? 'Team B' : 'Team A';
    const losingCount = losingTeam === 'Team A' ? currentTeams.teamA.count : currentTeams.teamB.count;
    
    if (!confirm(`⚠️ WARNING: This will eliminate all ${losingCount} players in ${losingTeam}!\n\n${winningTeam} will win and advance to the next round.\n\nContinue?`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('losingTeam', losingTeam);
        
        const response = await fetch('api/eliminate_team.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayResult(data);
            
            // Hide teams section, show result section
            document.getElementById('teamsSection').style.display = 'none';
            document.getElementById('resultSection').style.display = 'block';
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        console.error('Error eliminating team:', error);
        alert('Error connecting to server');
    }
}

// Display elimination result
function displayResult(data) {
    document.getElementById('resultTitle').textContent = 
        `🎉 ${data.winningTeam} Wins! ${data.losingTeam} Eliminated`;
    
    document.getElementById('resultEliminated').textContent = data.eliminatedCount;
    document.getElementById('resultSurvivors').textContent = data.remainingPlayers;
    document.getElementById('resultPrize').textContent = '₩' + data.prizeMoney.toLocaleString();
}

// Reset formation and go back to strategy selection
function resetFormation() {
    if (!confirm('Reset teams and choose a different strategy?')) {
        return;
    }
    
    currentTeams = null;
    
    // Hide teams section, show formation section
    document.getElementById('teamsSection').style.display = 'none';
    document.getElementById('formationSection').style.display = 'block';
    
    // Clear team displays
    document.getElementById('teamAPlayers').innerHTML = '';
    document.getElementById('teamBPlayers').innerHTML = '';
}

// Check game status on load
window.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('api/game_status.php');
        const data = await response.json();
        
        if (data.success) {
            // Check if Tug of War round is unlocked
            const unlockedRounds = data.unlockedRounds || [];
            
            if (!unlockedRounds.includes(3)) {
                alert('⚠️ Tug of War (Round 3) is not unlocked yet!\n\nComplete previous rounds first.');
                window.location.href = 'game_control.php';
            }
        }
    } catch (error) {
        console.error('Error checking game status:', error);
    }
});
