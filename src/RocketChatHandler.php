<?php

namespace UseRH\Logging;

use Monolog\Level;
use GuzzleHttp\Client;
use Monolog\LogRecord;
use UseRH\Logging\RocketChatRecord;
use Monolog\Handler\AbstractProcessingHandler;

class RocketChatHandler extends AbstractProcessingHandler
{
    private Client $client;

    private ?string $username;

    private array $webhooks;

    private RocketChatRecord $rocketChatRecord;

    public function __construct(
        array  $webhooks,
        string $username = null,
        string $emoji = null,
        int    $level = Level::Error,
        bool   $bubble = true
    ) {
        parent::__construct($level, $bubble);

        $this->webhooks = $webhooks;
        $this->username = $username;

        $this->client = new Client();

        $this->rocketChatRecord = new RocketChatRecord(
            $username,
            $emoji,
            $this->formatter
        );
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function write(LogRecord $record): void
    {
        $content = $this->rocketChatRecord->getRocketChatData($record);

        foreach ($this->webhooks as $webhook) {
            $this->client->request('POST', $webhook, [
                'json' => $content,
            ]);
        }
    }
}
