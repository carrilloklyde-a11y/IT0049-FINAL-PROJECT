<?php
namespace App\Controllers;
use App\Models\ProductModel;
use App\Models\CustomerModel;
use App\Models\SaleModel;
use App\Libraries\SaleService;
class Sales extends BaseController
{
    public function form()
    {
        return $this->render('sales/form',[
            'title'=>'Record sale','products'=>(new ProductModel())->where('archived_at',null)->orderBy('name')->findAll(),
            'customers'=>(new CustomerModel())->where('archived_at',null)->orderBy('full_name')->findAll(),
            'token'=>bin2hex(random_bytes(16)),
        ]);
    }
    public function record()
    {
        $data=$this->request->getPost();
        if (!$this->validateData($data,[
            'product_id'=>'required|is_natural_no_zero','customer_id'=>'permit_empty|is_natural_no_zero',
            'quantity'=>'required|is_natural_no_zero|less_than_equal_to[9999]',
            'checkout_token'=>'required|regex_match[/^[a-f0-9]{32}$/]',
            'cash_received'=>'permit_empty|regex_match[/^[0-9]{1,8}(?:\.[0-9]{1,2})?$/]',
        ])) { return redirect()->to(site_url('pos'))->with('errors',$this->validator->getErrors()); }
        try {
            $id=(new SaleService())->record((int)$data['product_id'],empty($data['customer_id'])?null:(int)$data['customer_id'],(int)session('staff')['id'],(int)$data['quantity'],$data['checkout_token'],$data['cash_received']??null);
            return redirect()->to(site_url('sales/'.$id))->with('success','Sale recorded. Stock has been updated.');
        } catch (\DomainException|\InvalidArgumentException $e) {
            return redirect()->to(site_url('pos'))->with('errors',[$e->getMessage()]);
        } catch (\Throwable $e) {
            log_message('error','Checkout failed: {type}',['type'=>get_class($e)]);
            return redirect()->to(site_url('pos'))->with('errors',['The sale could not be saved. No stock was deducted. Please try again.']);
        }
    }
    private function joined()
    {
        return db_connect()->table('sales')->select('sales.*,customers.full_name AS customer_name,users.full_name AS staff_name,products.name AS current_product_name')
            ->join('customers','customers.id=sales.customer_id','left')->join('users','users.id=sales.sold_by')->join('products','products.id=sales.product_id');
    }
    public function index()
    {
        $sales=$this->joined()->orderBy('sales.id','DESC')->get()->getResultArray();
        return $this->render('sales/index',['title'=>'Sales history','sales'=>$sales]);
    }
    public function receipt(int $id)
    {
        $sale=$this->joined()->where('sales.id',$id)->get()->getRowArray();
        if (!$sale) { $this->notFound(); }
        return $this->render('sales/receipt',['title'=>'Sale receipt','sale'=>$sale]);
    }
}
