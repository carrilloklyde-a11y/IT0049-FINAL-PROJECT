<?php
namespace App\Models;
use CodeIgniter\Model;
class CustomerModel extends Model
{
    protected $table='customers';
    protected $allowedFields=['full_name','email','phone','archived_at'];
    protected $useTimestamps=true;
    protected $createdField='created_at';
    protected $updatedField='';
    protected $returnType='array';
}
