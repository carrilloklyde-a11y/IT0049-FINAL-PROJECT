<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
class DemoSeeder extends Seeder
{
    public function run()
    {
        $now=date('Y-m-d H:i:s');
        // Seed only empty tables; restarting never resets a changed password or stock.
        if (!$this->db->table('users')->countAllResults()) {
            $this->db->table('users')->insert(['username'=>'admin','full_name'=>'Maverick John Klyde L. Carrillo','password'=>password_hash('admin',PASSWORD_DEFAULT),'created_at'=>$now]);
        }
        if (!$this->db->table('customers')->countAllResults()) {
            foreach ([['Isabel Santos','isabel@example.com','0917 555 0101'],['Miguel Reyes','miguel@example.com','0917 555 0102'],['Sofia Cruz','sofia@example.com','0917 555 0103'],['Lucas Garcia','lucas@example.com','0917 555 0104'],['Amara Flores','amara@example.com','0917 555 0105'],['Ethan Lim','ethan@example.com','0917 555 0106']] as [$name,$email,$phone]) {
                $this->db->table('customers')->insert(['full_name'=>$name,'email'=>$email,'phone'=>$phone,'created_at'=>$now]);
            }
        }
        if (!$this->db->table('products')->countAllResults()) {
            foreach ([['Spanish Latte','165.00',24,'Coffee'],['Americano','120.00',30,'Coffee'],['Cappuccino','150.00',18,'Coffee'],['Iced Caramel Latte','175.00',20,'Coffee'],['Matcha Latte','170.00',16,'Non-coffee'],['Chocolate','155.00',15,'Non-coffee'],['Butter Croissant','95.00',12,'Pastries'],['Cinnamon Roll','110.00',5,'Pastries']] as [$name,$price,$stock,$category]) {
                $this->db->table('products')->insert(['name'=>$name,'price'=>$price,'stock_quantity'=>$stock,'category'=>$category,'created_at'=>$now]);
            }
        }
    }
}
