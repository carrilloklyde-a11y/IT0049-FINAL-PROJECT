<?php
namespace App\Models;
use CodeIgniter\Model;
class SaleModel extends Model
{
    protected $table='sales';
    protected $allowedFields=['product_id','customer_id','sold_by','quantity','total_price','unit_price','product_name','cash_received','checkout_token'];
    protected $useTimestamps=true;
    protected $createdField='created_at';
    protected $updatedField='';
    protected $returnType='array';
}
