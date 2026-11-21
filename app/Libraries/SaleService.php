<?php
namespace App\Libraries;
use CodeIgniter\Database\BaseConnection;
class SaleService
{
    public function __construct(private ?BaseConnection $db=null) { $this->db ??= db_connect(); }
    public function record(int $productId, ?int $customerId, int $staffId, int $quantity, string $token, ?string $cash=null): int
    {
        if ($quantity<1 || $quantity>9999 || !preg_match('/^[a-f0-9]{32}$/D',$token)) { throw new \DomainException('Choose a valid quantity from 1 to 9999.'); }
        $db=$this->db;
        $db->transException(true)->transBegin();
        try {
            $existing=$db->table('sales')->where('checkout_token',$token)->get()->getRowArray();
            if ($existing) {
                if ((int)$existing['sold_by']!==$staffId) { throw new \DomainException('Please refresh the sale form.'); }
                $db->transCommit(); return (int)$existing['id'];
            }
            $staff=$db->table('users')->where('id',$staffId)->where('archived_at',null)->get()->getRowArray();
            $product=$db->table('products')->where('id',$productId)->where('archived_at',null)->get()->getRowArray();
            if (!$staff || !$product) { throw new \DomainException('The selected product or staff account is no longer available.'); }
            if ($customerId && !$db->table('customers')->where('id',$customerId)->where('archived_at',null)->countAllResults()) { throw new \DomainException('Choose an active customer or use Walk-in guest.'); }
            $total=Money::cents((string)$product['price'])*$quantity;
            if ($total>9999999999) { throw new \DomainException('This sale exceeds the maximum transaction amount.'); }
            $received=$cash===null || $cash==='' ? null : Money::cents($cash);
            if ($received!==null && $received<$total) { throw new \DomainException('Cash received must cover the total.'); }
            // A guarded atomic update prevents two checkouts from overselling the same stock.
            $db->table('products')->where('id',$productId)->where('archived_at',null)->where('stock_quantity >=',$quantity)
                ->set('stock_quantity','stock_quantity - '.$quantity,false)->update();
            if ($db->affectedRows()!==1) { throw new \DomainException('Not enough stock. Only '.$product['stock_quantity'].' unit(s) are available.'); }
            $db->table('sales')->insert([
                'product_id'=>$productId,'customer_id'=>$customerId,'sold_by'=>$staffId,'quantity'=>$quantity,
                'total_price'=>Money::decimal($total),'unit_price'=>$product['price'],'product_name'=>$product['name'],
                'cash_received'=>$received===null ? null : Money::decimal($received), 'checkout_token'=>$token,'created_at'=>date('Y-m-d H:i:s'),
            ]);
            $id=(int)$db->insertID();
            if (!$db->transStatus()) { throw new \RuntimeException('The sale could not be saved.'); }
            $db->transCommit(); return $id;
        } catch (\Throwable $e) { $db->transRollback(); throw $e; }
    }
}
