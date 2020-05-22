<?php

namespace ciri;

use Dynamicker\boymelancholy\dynamicker\Dynamickers;
use flowy\Flowy;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\Server;

use function flowy\standard\delay;

class Process
{

    const CALL_COMMAND = 'hey ciri';
    const HANGUP_COMMAND = 'bye';

    const TALK_PREFIX = 'Ciri ： ';

    const STATUS_ALIVE = 'alive';
    const STATUS_DIED = 'died';

    /**
     * Ciriを起動
     *
     * @param PlayerChatEvent $event
     * @return void
     */
    public function call(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();

        Dynamickers::enrol($player);

        $dyn = Dynamickers::get($player);
        $dyn->status = Process::STATUS_ALIVE;

        if (!Dynamickers::enrolled($player)) {
            return;
        }

        $event->setCancelled();

        Flowy::run(Ciri::getInstance(), \Closure::fromCallable(function() use($player) {
            yield from delay(Ciri::getInstance()->getScheduler(), 1 * 20);
            $this->refreshScreen($player);

            $player->sendMessage(self::TALK_PREFIX.'Hi, I am Ciri. What\'s up?');
            $player->sendMessage(self::TALK_PREFIX.'Input "bye", close Ciri.');
            $player->sendMessage(' ');
            $player->sendMessage('：> Show the now time'."\n".'  = "0" ');
            $player->sendMessage('：> Show the participants'."\n".'  = "1" ');
            $player->sendMessage('：> Show the coordinate'."\n".'  = "2" ');
            $player->sendMessage('：> Show the item data in hand'."\n".'  = "3" ');
            $player->sendMessage('：> Quit from Ciri'."\n".'  = "bye" ');
            $player->sendMessage('：');
        }));
    }

    /**
     * Ciriを切る
     *
     * @param PlayerChatEvent $event
     * @return void
     */
    public function hangUp(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();

        $event->setCancelled();

        Flowy::run(Ciri::getInstance(), \Closure::fromCallable(function() use($player) {
            for ($i=0; $i<3; ++$i) {
                $this->refreshScreen($player);
                $player->sendMessage(str_repeat('.', $i + 1));
                yield from delay(Ciri::getInstance()->getScheduler(), 1 * 20);
            }

            $player->sendMessage(self::TALK_PREFIX.'See you :D');

            yield from delay(Ciri::getInstance()->getScheduler(), 3 * 20);
            $this->refreshScreen($player);
            //$this->sendChatScreen($player);

            Dynamickers::disenrol($player);
        }));
    }

    /**
     * Ciri結果処理
     *
     * @param PlayerChatEvent $event
     * @return void
     */
    public function result(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        $event->setCancelled();

        Flowy::run(Ciri::getInstance(), \Closure::fromCallable(function() use($player, $message) {
            for ($i=0; $i<3; ++$i) {
                $this->refreshScreen($player);
                $player->sendMessage(str_repeat('.', $i + 1));
                yield from delay(Ciri::getInstance()->getScheduler(), 1 * 20);
            }

            $this->optionAnswer($player, $message);
            $player->sendMessage(' ');
            $player->sendMessage(' ');

            yield from delay(Ciri::getInstance()->getScheduler(), 1 * 20);
            $player->sendMessage(self::TALK_PREFIX.'Anything else? :)');
            $player->sendMessage(' ');
            $player->sendMessage('：> Show the now time'."\n".'  = "0" ');
            $player->sendMessage('：> Show the participants'."\n".'  = "1" ');
            $player->sendMessage('：> Show the coordinate'."\n".'  = "2" ');
            $player->sendMessage('：> Show the item data in hand'."\n".'  = "3" ');
            $player->sendMessage('：> Quit from Ciri'."\n".'  = "bye" ');
            $player->sendMessage('：');
        }));
    }

    /**
     * チャットログ送信
     *
     * @param Player|null $player
     * @return void
     */
    private function sendChatScreen(Player $player)
    {
        $dyn = Dynamickers::get($player);
        if (isset($dyn->chatlog)) {
            $player->sendMessage(' ');
            $player->sendMessage($dyn->chatlog);
        }
    }

    /**
     * 空白送信
     *
     * @param Player|null $player
     * @return void
     */
    private function refreshScreen(Player $player)
    {
        $player->sendMessage(str_repeat("\n", 23));
    }

    /**
     * 解答
     *
     * @param Player $player
     * @param string $message
     * @return void
     */
    private function optionAnswer(Player $player, string $message)
    {
        switch ($message) {
            case '0':
                $d = new \DateTime();
                $date = $d->format('Y/m/d H:i:s');
                $player->sendMessage($date);
            break;

            case '1':
                Server::getInstance()->dispatchCommand($player, 'list');
            break;

            case '2':
                $player->sendMessage($player->getFloorX().', '.$player->getFloorY().', '.$player->getFloorZ());
            break;

            case '3':
                $item = $player->getInventory()->getItemInHand();
                $player->sendMessage('Id:Meta = '.$item->getId().':'.$item->getDamage());
                $player->sendMessage('ItemName = '.$item->getName());
            break;
        }
        $player->sendMessage(' ');
    }
}