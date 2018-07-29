<?php

namespace App\Http\Controllers\Telegram\Bot;

use Telegram;
use App\TelegramState;
use App\Http\Controllers\Controller;

class WebhookController extends Controller
{
    public function handle()
    {
        $update = Telegram::getWebhookUpdates();

        if ($update->has('callback_query')) {
            $this->handleCallbackQuery($update);
        } elseif ($update->has('message')) {
            $this->handleMessage($update);
        }

        return 'ok';
    }

    protected function handleCallbackQuery($update)
    {
        $query = $update->getCallbackQuery();

        parse_str($query->get('data', ''), $data);

        if (isset($data['command'])) {
            $this->handleCommand($data['command'], $data['arguments'] ?? '', $update);
        }
    }

    protected function handleMessage($update)
    {
        $message = $update->getMessage();

        $text = $message->getText();

        if (isset($text)) {
            $matches = Telegram::getCommandBus()->parseCommand($text);
            if ($matches) {
                $this->handleCommand($matches[1], $matches[3], $update);
            } elseif ($this->isTextCommand($text, $update)) {
                $this->handleTextCommand($text, $update);
            } else {
                $this->handleText($text, $update);
            }
        }
    }

    protected function resetState($update)
    {
        TelegramState::reset($this->getFrom($update)->getId());
    }

    protected function handleCommand($command, $arguments, $update)
    {
        $this->resetState($update);

        $this->executeCommand($command, $arguments, $update);
    }

    protected function executeCommand($command, $arguments, $update)
    {
        Telegram::getCommandBus()->execute($command, $arguments, $update);
    }

    protected function isTextCommand($message)
    {
        return isset(config('telegram.text_commands')[$message]);
    }

    protected function handleTextCommand($text, $update)
    {
        $this->handleCommand(config('telegram.text_commands')[$text], '', $update);
    }

    protected function handleText($message, $update)
    {
        $state = TelegramState::last($this->getFrom($update)->getId());

        if ($state and 'none' !== $state->status) {
            $this->executeCommand($state->command, $state->arguments, $update);
        } else {
            $this->executeCommand('help', '', $update);
        }
    }

    protected function getFrom($update)
    {
        if ($update->has('message')) {
            return $update->getMessage()->getFrom();
        } elseif ($update->has('callback_query')) {
            return $update->getCallbackQuery()->getFrom();
        }
    }
}
