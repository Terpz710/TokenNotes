<?php

declare(strict_types=1);

namespace Terpz710\TokenNotes;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

use Terpz710\TokenNotes\Command\TokenNoteCommand;
use Terpz710\TokensAPI\API\TokenAPI;
use Terpz710\TokensAPI\Tokens;

class Loader extends PluginBase implements Listener {

    private $tokenAPI;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("tokennote", new TokenNoteCommand($this));
        
        $tokensPlugin = $this->getServer()->getPluginManager()->getPlugin("TokensAPI");
        if ($tokensPlugin instanceof Tokens) {
            $this->tokenAPI = new TokenAPI($tokensPlugin);
        } else {
            $this->getLogger()->warning("TokensAPI plugin not found! Until TokensAPI gets installed all features are disabled...");
        }
    }

    public function getTokenAPI(): ?TokenAPI {
        return $this->tokenAPI;
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        
        $nbt = $item->getNamedTag();
        if ($nbt !== null && $nbt instanceof CompoundTag && $nbt->getTag("Value")) {
            $value = $nbt->getInt("Value");
            $tokenAPI = $this->getTokenAPI();
            if ($tokenAPI !== null) {
                $tokenAPI->addToken($player, $value);
                $player->sendMessage("Redeemed §e$value tokens§f from the bank note!");
                $player->getInventory()->removeItem($item);
                $event->setCancelled();
            }
        }
    }
}
