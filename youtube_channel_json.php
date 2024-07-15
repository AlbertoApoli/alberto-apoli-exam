<?php

require 'database.php';

$channelId = $_GET['channelId'] ?? null;

if ($channelId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM youtube_channels WHERE channel_id = ?");
        $stmt->execute([$channelId]);
        $channel = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM youtube_channel_videos WHERE channel_id = ? LIMIT 100");
        $stmt->execute([$channelId]);
        $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            'channel' => $channel,
            'videos' => $videos,
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Channel ID is required.',
    ]);
    http_response_code(400);
}
