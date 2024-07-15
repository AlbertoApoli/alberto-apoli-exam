<?php

require 'database.php';

$channelId = $_GET['channelId'] ?? null;

if ($channelId) {
    try {
        $apiKey = 'AIzaSyDFwcCzUenXxgOTv0CKrfKoFMcwVT4vtow';
        $baseUrl = 'https://www.googleapis.com/youtube/v3';
        $channelUrl = "{$baseUrl}/channels?part=snippet&id={$channelId}&key={$apiKey}";

        // Channel

        $channelData = json_decode(file_get_contents($channelUrl), true);

        if (empty($channelData['items'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Channel not found',
            ]);
            http_response_code(404);
            exit;
        }

        $channelInfo = $channelData['items'][0]['snippet'];
        $profilePicture = $channelInfo['thumbnails']['default']['url'];
        $name = $channelInfo['title'];
        $description = $channelInfo['description'];

        $stmt = $pdo->prepare("INSERT IGNORE INTO youtube_channels (channel_id, profile_picture, name, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$channelId, $profilePicture, $name, $description]);

        // Videos 

        $videos = [];
        $pageToken = '';

        while (count($videos) < 100) {
            $videosSearchUrl = "{$baseUrl}/search?key={$apiKey}&channelId={$channelId}&part=snippet&order=date&maxResults=50&pageToken={$pageToken}";
            $videosResponse = file_get_contents($videosSearchUrl);
            $videosData = json_decode($videosResponse, true);

            foreach ($videosData['items'] as $item) {
                if ($item['id']['kind'] == 'youtube#video') {
                    $videos[] = $item['id']['videoId'];
                    if (count($videos) >= 100) break;
                }
            }

            $pageToken = $videosData['nextPageToken'] ?? '';
            if (!$pageToken) break;
        }

        foreach ($videos as $videoId) {
            $videosUrl = "{$baseUrl}/videos?part=snippet&id={$videoId}&key={$apiKey}";
            $videoDetails = file_get_contents($videosUrl);
            $videoSnippet = json_decode($videoDetails, true)['items'][0]['snippet'];

            $title = $videoSnippet['title'];
            $description = $videoSnippet['description'];
            $thumbnail = $videoSnippet['thumbnails']['medium']['url'];
            $videoLink = "https://www.youtube.com/watch?v={$videoId}";

            $stmt = $pdo->prepare("INSERT IGNORE INTO youtube_channel_videos (channel_id, video_link, title, description, thumbnail) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$channelId, $videoLink, $title, $description, $thumbnail]);
        }

        $response = [
            'success' => true,
            'message' => 'Channel synced successfully.',
            'channel' => $name,
            'channelId' => $channelId,
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
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Channel ID is required.',
    ]);
    http_response_code(400);
}
