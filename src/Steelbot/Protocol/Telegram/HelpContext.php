<?php

namespace Steelbot\Protocol\Telegram;


use Steelbot\Context\Context;
use Steelbot\Route\RouteMatcherInterface;
use Steelbot\Protocol\IncomingPayloadInterface;

/**
 * Class HelpContext
 * @package Steelbot\Protocol\Telegram
 */
class HelpContext extends Context
{
    public function handle($message): \Generator
    {
        $this->resolve();
        $help = ["Commands:"];

        foreach ($this->app->getContextRouter()->getRoutes() as $priority => $pairs) {
            foreach ($pairs as $pair) {
                /** @var RouteMatcherInterface $routeMatcher */
                $routeMatcher = $pair[0];

                if (!$message->isGroupChatMessage() || $routeMatcher->getGroupChat()) {
                    foreach ($routeMatcher->getHelp() as $command => $description) {
                        $help[] = "$command - $description";
                    }
                }
            }
        }

        yield $this->answer(implode("\n", $help));
    }

    /**
     * @return mixed
     */
    public function getHelp(): array
    {
        return [
            '/help' => 'Список команд'
        ];
    }
}
