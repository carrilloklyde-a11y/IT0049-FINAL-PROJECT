<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreatePos extends Migration
{
    public function up()
    {
        $common=['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true], 'created_at'=>['type'=>'DATETIME'], 'archived_at'=>['type'=>'DATETIME','null'=>true]];
        $this->forge->addField($common + ['name'=>['type'=>'VARCHAR','constraint'=>100],'price'=>['type'=>'DECIMAL','constraint'=>'10,2'],'stock_quantity'=>['type'=>'INT','unsigned'=>true,'default'=>0],'image'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],'category'=>['type'=>'VARCHAR','constraint'=>30,'default'=>'Coffee']]);
        $this->forge->addKey('id',true); $this->forge->createTable('products',true);
        $this->forge->addField($common + ['full_name'=>['type'=>'VARCHAR','constraint'=>100],'email'=>['type'=>'VARCHAR','constraint'=>100],'phone'=>['type'=>'VARCHAR','constraint'=>20,'null'=>true]]);
        $this->forge->addKey('id',true); $this->forge->createTable('customers',true);
        $this->forge->addField($common + ['username'=>['type'=>'VARCHAR','constraint'=>50],'full_name'=>['type'=>'VARCHAR','constraint'=>100],'password'=>['type'=>'VARCHAR','constraint'=>255],'avatar'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],'auth_version'=>['type'=>'INT','default'=>1]]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('username'); $this->forge->createTable('users',true);
        $this->forge->addField([
            'id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'product_id'=>['type'=>'INT','unsigned'=>true], 'customer_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],
            'sold_by'=>['type'=>'INT','unsigned'=>true], 'quantity'=>['type'=>'INT','unsigned'=>true],
            'total_price'=>['type'=>'DECIMAL','constraint'=>'10,2'], 'created_at'=>['type'=>'DATETIME'],
            'unit_price'=>['type'=>'DECIMAL','constraint'=>'10,2'], 'product_name'=>['type'=>'VARCHAR','constraint'=>100],
            'cash_received'=>['type'=>'DECIMAL','constraint'=>'10,2','null'=>true],
            'checkout_token'=>['type'=>'VARCHAR','constraint'=>64],
        ]);
        $this->forge->addKey('id',true); $this->forge->addUniqueKey('checkout_token');
        $this->forge->addForeignKey('product_id','products','id','RESTRICT','RESTRICT');
        $this->forge->addForeignKey('customer_id','customers','id','RESTRICT','RESTRICT');
        $this->forge->addForeignKey('sold_by','users','id','RESTRICT','RESTRICT');
        $this->forge->createTable('sales',true);
    }
    public function down()
    {
        foreach (['sales','customers','products','users'] as $table) { $this->forge->dropTable($table,true); }
    }
}
