<?php

namespace App\Services\Telegram;

use GuzzleHttp\Client;

class TelegramService
{
    protected $client;
    protected $botToken;
    protected $channelId;

    public function __construct()
    {
        $this->client = new Client();
        $this->botToken = config('database')['connections']['telegram']['bot_token'];
        $this->channelId = config('database')['connections']['telegram']['chanel_log_id']; // Kênh nhận lỗi
    }

    /**
     * Gửi tin nhắn đến kênh Telegram
     *
     * @param string $message Nội dung tin nhắn
     * @return array|false Phản hồi từ Telegram hoặc false nếu có lỗi
     */
    public function sendMessage($message)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $data = [
            'chat_id' => $this->channelId,
            'text' => $message,
        ];

        try {
            $response = $this->client->post($url, [
                'form_params' => $data,
            ]);

            $responseBody = json_decode($response->getBody(), true);

            // Kiểm tra nếu gửi tin nhắn thành công
            if (isset($responseBody['ok']) && $responseBody['ok'] === true) {
                return $responseBody;
            } else {
                throw new \Exception('Error from Telegram API: ' . ($responseBody['description'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có
            throw new \Exception('Request failed: ' . $e->getMessage());
        }
    }
}
