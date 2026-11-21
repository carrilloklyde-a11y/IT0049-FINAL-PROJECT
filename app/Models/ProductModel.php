<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductModel extends Model
{
    protected $table='products';
    protected $allowedFields=['name','price','stock_quantity','image','category','archived_at'];
    protected $useTimestamps=true;
    protected $createdField='created_at';
    protected $updatedField='';
    protected $returnType='array';
}
