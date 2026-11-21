<?php
namespace App\Libraries;
class Money
{
    public static function cents(string $amount): int
    {
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D',$amount)) { throw new \InvalidArgumentException('Enter a valid money amount with up to two decimal places.'); }
        $parts=explode('.',$amount);
        return (int)$parts[0]*100+(int)str_pad($parts[1]??'',2,'0');
    }
    public static function decimal(int $cents): string { return intdiv($cents,100).'.'.str_pad((string)($cents%100),2,'0',STR_PAD_LEFT); }
}
