<?php

declare(strict_types=1);

namespace App\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'telegram:webhook:set',
    description: 'Registers Telegram webhook using the token and URL from env'
)]
class SetTelegramWebhookCommand extends Command
{
    public function __construct(
        private ?string $token,
        private ?string $url,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null === $this->token || null === $this->url) {
            $output->writeln('<error>TELEGRAM_BOT_TOKEN or TELEGRAM_WEBHOOK_URL is not set</error>');

            return Command::FAILURE;
        }

        $apiUrl = 'https://api.telegram.org/bot' . $this->token . '/setWebhook?url=' . urlencode($this->url);

        $response = file_get_contents($apiUrl);

        if (false === $response) {
            $output->writeln('<error>Failed to set webhook: Unable to reach Telegram API</error>');

            return Command::FAILURE;
        }

        $data = json_decode($response, true);

        if (($data['ok'] ?? false) === true) {
            $output->writeln('<info>Webhook set successfully ✅</info>');
        } else {
            $output->writeln('<error>Failed to set webhook: ' . ($data['description'] ?? 'Unknown error') . '</error>');
        }

        return Command::SUCCESS;
    }
}
