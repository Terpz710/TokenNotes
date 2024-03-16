<?php

declare(strict_types=1);

namespace Terpz710\TokenNotes;

use pocketmine\item\Item;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\block\VanillaBlocks;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\TextFormat;
use pocketmine\nbt\tag\CompoundTag;

use Terpz710\TokenNotes\Command\TokenNoteCommand;
use Terpz710\TokensAPI\API\TokenAPI;
use Terpz710\TokensAPI\Tokens;

class Loader extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("tokennote", new TokenNoteCommand($this));
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $action = $event->getAction();

        if ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK || $action === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
            if ($item->getNamedTag()->getTag("TokenNote", CompoundTag::class)) {
                $tokenAmount = $this->getTokenAmount($item);
                $event->cancel();
                $player->getInventory()->removeItem($item);
                TokenAPI::addToken($player, $tokenAmount);
                $player->sendMessage("§l§e(!)§r§f You received §e{$tokenAmount} tokens§f from the Token Note!");
            }
        }
    }

    public function createTokenNote(int $tokenAmount): Item {
        $tokenNote = VanillaBlocks::SUNFLOWER()->asItem();
        $tokenNote->setCustomName("§l§eToken§f Note");
        $tokenNote->setLore(["Worth $tokenAmount tokens"]);
        $tokenNote->getNamedTag()->setString("TokenNote", "");
        $enchantment = new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 3);
        $tokenNote->addEnchantment($enchantment);
        return $tokenNote;
    }

    public function getTokenAmount(Item $item): int {
        $lore = $item->getLore();
        if (!empty($lore) && preg_match('/Worth (\d+) tokens/', $lore[0], $matches)) {
            return (int)$matches[1];
        }
        return 1;
    }
}
