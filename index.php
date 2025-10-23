<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SquidSphere</title>
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <div class="logo-container">
        <img src="images/squidSphere.png" alt="SquidSphere Logo" class="logo">
    </div>

    <button class="enter-button" onclick="generatePlayersAndStart()">Enter</button>

   <script>
       function generatePlayersAndStart() {
           // Show loading state
           const btn = document.querySelector('.enter-button');
           btn.textContent = 'Generating Players...';
           btn.disabled = true;
           
           // Call the generate players script with ajax parameter
           fetch('database/generate_players.php?ajax=1')
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       // Redirect to players page
                       window.location.href = 'players.php';
                   } else {
                       btn.textContent = 'Error! Try Again';
                       btn.disabled = false;
                       alert('Error: ' + data.error);
                   }
               })
               .catch(error => {
                   console.error('Error:', error);
                   btn.textContent = 'Error! Try Again';
                   btn.disabled = false;
               });
       }
   </script>
</body>
</html>
