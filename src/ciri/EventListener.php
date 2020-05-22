<?php

declare(strict_types=1);

namespace ciri;

use Dynamicker\boymelancholy\dynamicker\Dynamickers;
use flowy\Flowy;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

use function flowy\listen;

class EventListener implements Listener
{
    public function __construct(Ciri $owner)
    {
        $this->owner = $owner;
    }

    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();

        if (!Dynamickers::enrolled($player)) {
            if (strtolower($event->getMessage()) === Process::CALL_COMMAND) {
                Ciri::getInstance()->getProcess()->call($event);
            } else {
                return;
            }
        }

        $dyn = Dynamickers::get($player);
        if (isset($dyn->status)) {
            $dyn->status = Process::STATUS_ALIVE;
        }

        Flowy::run(Ciri::getInstance(), \Closure::fromCallable(function() use($player) {
            $event = yield listen(PlayerChatEvent::class)->filter(function($ev) use($player) {
                if ($ev->getPlayer() !== $player) {
                    return false;
                } else {
                    return (
                        (
                            ctype_digit($ev->getMessage())
                            && ((int) $ev->getMessage() > -1
                            && (int) $ev->getMessage() < 4)
                        )
                        ||$ev->getMessage() === 'bye'
                    );
                }
            });

            if ($event->getMessage() === 'bye') {
                Ciri::getInstance()->getProcess()->hangUp($event);
            } else {
                Ciri::getInstance()->getProcess()->result($event);
            }
            return;
        }));

        /*if (Dynamickers::$dynamickerList !== []) {
            foreach (Dynamickers::$dynamickerList as &$dyn) {
                $msg = $event->getMessage();
                $name = $player->getName();
                $format = $event->getFormat();
                $data = str_replace(['{%0}', '{%1}'], [$name, $msg], $format);
                if (!isset($dyn->chatlog)) {
                    $dyn->chatlog = "";
                }
                $dyn->chatlog .= $data."\n";
            }
        }*/
    }
}