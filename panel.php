<?php
  
$configPath = 'config.php';
if (!file_exists($configPath)) {
    die("Configuration file not found.");
}
$config = include $configPath;
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config['bot_token'] = $_POST['bot_token'];
    $config['source_channel'] = $_POST['source_channel'];
    $config['destination_channels'] = [];

    foreach ($_POST['channels'] as $channel) {
        if (!empty($channel['channel_id'])) {
            $placeholders = [];
            foreach ($channel['placeholders'] as $key => $value) {
                if (!empty($key) && !empty($value)) {
                    $placeholders[$key] = $value;
                }
            }

            $config['destination_channels'][] = [
                'channel_id' => $channel['channel_id'],
                'placeholders' => $placeholders,
            ];
        }
    }

    if (is_writable($configPath)) {
        file_put_contents($configPath, "<?php\nreturn " . var_export($config, true) . ";\n?>");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        die("Configuration file is not writable. Check permissions.");
    }
}
?>

<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            text-align: center;
            padding: 50px;
        }
        h1 {
            color: #333;
        }
        p {
            color: #555;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Config Control Panel</title>
    <style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Poppins:wght@300;400;600&display=swap');

body {
    font-family: 'Poppins', Arial, sans-serif;
    background: linear-gradient(135deg, rgba(255, 154, 158, 0.8), rgba(250, 208, 196, 0.8));
    color: #333;
    margin: 0;
    padding: 0;
    font-size: 14px;
    line-height: 1.6;
}

.container {
    max-width: 900px;
    margin: 50px auto;
    padding: 30px;
    background: rgba(255, 255, 255, 0.7); /* Slightly transparent white */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    border-radius: 15px;
    backdrop-filter: blur(10px); /* Subtle blur for modern look */
}

h1, h2 {
    text-align: center;
    color: #222;
    font-family: 'Roboto', sans-serif;
    font-weight: 700;
}

h1 {
    font-size: 26px; 
    margin-bottom: 15px;
}

h2 {
    font-size: 20px; 
    font-weight: 400;
    color: #555;
}

form {
    display: flex;
    flex-direction: column;
    gap: 10px; 
}

label {
    font-size: 14px;
    font-weight: 400;
    color: #444;
    margin-bottom: 5px; 
}

input[type="text"],
textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.8);
    color: #555;
    font-size: 14px;
    transition: 0.3s;
}

input[type="text"]:focus,
textarea:focus {
    border-color: #ff9a9e;
    background: rgba(255, 243, 243, 0.8);
    outline: none;
}

input[type="text"]::placeholder,
textarea::placeholder {
    color: #aaa;
    font-size: 13px;
}

button {
    background: #ff9a9e;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    transition: 0.3s;
}

button:hover {
    background: #e88b8f;
}

.channel {
    margin: 20px 0;
    padding: 12px;
    background: rgba(255, 154, 158, 0.2);
    border-radius: 10px;
    font-size: 13px;
}

button[type="button"] {
    background: #dc3545;
    font-size: 14px;
}

button[type="button"]:hover {
    background: #c82333;
}

#add-channel {
    background: #28a745;
}

#add-channel:hover {
    background: #218838;
}

a {
    display: inline-block;
    margin-top: 20px;
    padding: 8px 15px;
    background-color: #ff9a9e;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-size: 14px;
    transition: 0.3s;
}

a:hover {
    background-color: #e88b8f;
}
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to the Protected Page!</h2>
    <a href="logout.php">Logout</a>
        <form method="post">
            <h2>Bot Token</h2>
            <input type="text" name="bot_token" value="<?= htmlspecialchars($config['bot_token']) ?>" required placeholder="Enter your Bot Token">

            <h2>Source Channel</h2>
            <input type="text" name="source_channel" value="<?= htmlspecialchars($config['source_channel']) ?>" required placeholder="Enter Source Channel ID">

            <h2>Destination Channels</h2>
            <div id="channels">
                <?php foreach ($config['destination_channels'] as $index => $channel): ?>
                    <div class="channel">
                        <label>Channel ID:</label>
                        <input type="text" name="channels[<?= $index ?>][channel_id]" value="<?= htmlspecialchars($channel['channel_id']) ?>" required placeholder="Enter Destination Channel ID">

                        <label>Placeholders:</label>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="text" name="channels[<?= $index ?>][placeholders][(Change<?= $i ?>)]" 
                                value="<?= htmlspecialchars($channel['placeholders']["(Change$i)"] ?? '') ?>" 
                                placeholder="Placeholder (Cg<?= $i ?>)">
                        <?php endfor; ?>
                        <button type="button" onclick="removeChannel(this)">Remove Channel</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-channel">Add Channel</button>

            <button type="submit">Save Changes</button>
        </form>
    </div>
    <script>
        const addChannelButton = document.getElementById('add-channel');
        const channelsContainer = document.getElementById('channels');

        addChannelButton.addEventListener('click', () => {
            const index = Date.now(); 
            const newChannel = document.createElement('div');
            newChannel.className = 'channel';
            newChannel.innerHTML = `
                <label>Channel ID:</label>
                <input type="text" name="channels[${index}][channel_id]" required placeholder="Enter Destination Channel ID">
                <label>Placeholders:</label>
                <input type="text" name="channels[${index}][placeholders][(Change1)]" placeholder="Placeholder (Change1)">
                <input type="text" name="channels[${index}][placeholders][(Change2)]" placeholder="Placeholder (Change2)">
                <input type="text" name="channels[${index}][placeholders][(Change3)]" placeholder="Placeholder (Change3)">
                <input type="text" name="channels[${index}][placeholders][(Change4)]" placeholder="Placeholder (Change4)">
                <input type="text" name="channels[${index}][placeholders][(Change5)]" placeholder="Placeholder (Change5)">
                <button type="button" onclick="removeChannel(this)">Remove Channel</button>
            `;
            channelsContainer.appendChild(newChannel);
        });

        function removeChannel(button) {
            button.parentElement.remove();
        }
    </script>
</body>
</html>