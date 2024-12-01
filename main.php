<?php
$config = include('config.php');
include('start.php');

function validateLicense($licenseKey, $licenseServerURL) {
    $response = @file_get_contents($licenseServerURL . "?key=" . urlencode($licenseKey));
    if ($response === false) {
        die("Cannot connect to license server. Please check your internet connection or contact support.");
    }
    $responseData = json_decode($response, true);
    if (!isset($responseData['licenses'])) {
        die("Invalid license server response.");
    }
    foreach ($responseData['licenses'] as $license) {
        if ($license['key'] === $licenseKey && $license['valid'] === true) {
            return true; 
        }
    }
    return false;  
}

$botToken = $config['bot_token'];
$sourceChannel = $config['source_channel'];
$destinationChannels = $config['destination_channels'];
$licenseKey = $config['license_key'];  
$licenseServerURL = "https://hasan02.serv00.net/license/key.json"; 

if (!validateLicense($licenseKey, $licenseServerURL)) {
    die("Invalid license key. Please contact support.");
}

$apiURL = "https://api.telegram.org/bot$botToken/";

$update = file_get_contents("php://input");
$updateData = json_decode($update, true);

if (isset($updateData['message']) && isset($updateData['message']['text']) && $updateData['message']['text'] === '/start') {
    processStartCommand($updateData['message']);
    exit;
}
if (isset($updateData['channel_post'])) {
    $message = $updateData['channel_post'];
    
    if (!validateLicense($licenseKey, $licenseServerURL)) {
        die("Unauthorized access during channel_post processing.");
    }

    $originalText = $message['text'] ?? '';
    $originalCaption = $message['caption'] ?? '';
    $content = $originalText ?: $originalCaption;
    $cleanText = $content;
    
    foreach ($destinationChannels as $channel) {
        foreach ($channel['placeholders'] as $placeholder => $value) {
            $cleanText = str_replace($placeholder, '', $cleanText);
        }
    }
    
    if ($cleanText !== $content) {
        if (isset($message['photo'])) {
            file_get_contents($apiURL . "editMessageCaption?chat_id={$message['chat']['id']}&message_id={$message['message_id']}&caption=" . urlencode($cleanText));
        } else {
            file_get_contents($apiURL . "editMessageText?chat_id={$message['chat']['id']}&message_id={$message['message_id']}&text=" . urlencode($cleanText));
        }
    }
    
    foreach ($destinationChannels as $channel) {
        $customText = $content;
        $lines = explode("\n", $customText);
        foreach ($lines as &$line) {
            foreach ($channel['placeholders'] as $placeholder => $value) {
                if (strpos($line, $placeholder) !== false) {
                    $line = $value;
                }
            }
        }
        
        $customText = implode("\n", $lines);

        foreach ($destinationChannels as $chkChannel) {
            foreach ($chkChannel['placeholders'] as $chkPlaceholder => $chkValue) {
                $customText = str_replace($chkPlaceholder, '', $customText);
            }
        }
        
        $targetChatId = is_numeric($channel['channel_id']) ? $channel['channel_id'] : "@" . ltrim($channel['channel_id'], '@');
        if (isset($message['photo'])) {
            file_get_contents($apiURL . "sendPhoto?chat_id={$targetChatId}&photo=" . $message['photo'][0]['file_id'] . "&caption=" . urlencode($customText));
        } elseif (isset($message['video'])) {
            file_get_contents($apiURL . "sendVideo?chat_id={$targetChatId}&video=" . $message['video']['file_id'] . "&caption=" . urlencode($customText));
        } elseif (isset($message['document'])) {
            file_get_contents($apiURL . "sendDocument?chat_id={$targetChatId}&document=" . $message['document']['file_id'] . "&caption=" . urlencode($customText));
        } else {
            file_get_contents($apiURL . "sendMessage?chat_id={$targetChatId}&text=" . urlencode($customText));
        }
    }
}

echo "Bot is running!";
?>