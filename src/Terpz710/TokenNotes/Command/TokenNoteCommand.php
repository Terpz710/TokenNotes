<?php

declare(strict_types=1);

namespace Terpz710\TokenNotes\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

class TokenNoteCommand extends Command {

    public function __construct() {
        parent::__construct("banknote", "Create a bank note with a specified value", "/banknote <amount>");
        $this->setPermission("tokennotes.cmd");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game!");
            return false;
        }
        
        if (count($args) !== 1 || !is_numeric($args[0])) {
            $sender->sendMessage("Usage: /banknote <amount>");
            return false;
        }

        $amount = (int) $args[0];
        if ($amount <= 0) {
            $sender->sendMessage("Please specify a positive amount!");
            return false;
        }

        $bankNote = $this->createBankNoteItem($amount);
        if ($bankNote !== null) {
            $sender->getInventory()->addItem($bankNote);
            $sender->sendMessage("Token note with value §e{$amount} tokens§f created!");
            return true;
        } else {
            $sender->sendMessage("Failed to create token note! Please contact §bTerpz710§f on discord: §eace873056. §for§e ace87.");
            return false;
        }
    }

    private function createBankNoteItem(int $value) {
        $bankNote = VanillaItems::PAPER();
        $bankNote->setCustomName("Bank Note $" . $value);
        $nbt = new CompoundTag("", [
            "BankNote" => new CompoundTag("BankNote", [
                "Value" => new IntTag("Value", $value)
            ])
        ]);
        $bankNote->setNamedTag($nbt);
        return $bankNote;
    }
}
