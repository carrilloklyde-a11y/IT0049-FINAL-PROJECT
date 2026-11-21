<?php
namespace App\Models;
use CodeIgniter\Model;
class UserModel extends Model
{
    protected $table='users';
    protected $allowedFields=['username','full_name','password','avatar','auth_version','archived_at'];
    protected $useTimestamps=true;
    protected $createdField='created_at';
    protected $updatedField='';
    protected $returnType='array';
}
