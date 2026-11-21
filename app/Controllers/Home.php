<?php
namespace App\Controllers;
class Home extends BaseController
{
    public function index()
    {
        $db=db_connect();
        return $this->render('home',[
            'title'=>'Overview',
            'products'=>$db->table('products')->where('archived_at',null)->countAllResults(),
            'customers'=>$db->table('customers')->where('archived_at',null)->countAllResults(),
            'lowStock'=>$db->table('products')->where('archived_at',null)->where('stock_quantity <=',5)->countAllResults(),
            'todaySales'=>$db->table('sales')->where('created_at >=',date('Y-m-d').' 00:00:00')->selectSum('total_price')->get()->getRow()->total_price??0,
            'recent'=>$db->table('sales')->select('sales.*,users.full_name AS staff_name')->join('users','users.id=sales.sold_by')->orderBy('sales.id','DESC')->limit(5)->get()->getResultArray(),
        ]);
    }
    public function about() { return $this->render('about',['title'=>'About Ember']); }
    public function health() { return $this->response->setJSON(['status'=>'ok']); }
}
