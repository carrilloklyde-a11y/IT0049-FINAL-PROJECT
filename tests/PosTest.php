<?php
namespace Tests;
use App\Database\Seeds\DemoSeeder;
use App\Libraries\{ImageUpload,Money,SaleService};
use App\Models\{CustomerModel,ProductModel,UserModel};
use CodeIgniter\Test\{CIUnitTestCase,DatabaseTestTrait,FeatureTestTrait};
use CodeIgniter\HTTP\Files\UploadedFile;

final class PosTest extends CIUnitTestCase
{
    use DatabaseTestTrait,FeatureTestTrait;
    protected $namespace='App';
    protected $refresh=true;
    protected $seed=DemoSeeder::class;
    private function staff(): array { $user=(new UserModel())->first();unset($user['password']);return $user; }
    private function submit(string $path,array $data=[],bool $auth=true)
    {
        $token=bin2hex(random_bytes(16));\Config\Services::resetSingle('security');
        $values=['csrf_test_name'=>$token];if($auth)$values['staff']=$this->staff();
        return $this->withSession($values)->post($path,['csrf_test_name'=>$token]+$data);
    }
    public function testSeedAndHash(): void
    {
        $this->assertSame(8,(new ProductModel())->countAllResults());$this->assertSame(6,(new CustomerModel())->countAllResults());
        $user=(new UserModel())->first();$this->assertTrue(password_verify('admin',$user['password']));$this->assertNotSame('admin',$user['password']);
    }
    public function testProtectedPages(): void
    {
        foreach(['/','products','products/new','customers','staff','pos','sales','about','media/'.str_repeat('a',32).'.webp'] as $uri)$this->withSession([])->get($uri)->assertRedirectTo(site_url('login'));
        $this->get('login')->assertSee('CLASSROOM DEMO ACCESS');
    }
    public function testAuthenticatedViews(): void
    {
        foreach(['/','products','products/new','customers','customers/new','staff','staff/new','pos','sales','about'] as $uri)$this->withSession(['staff'=>$this->staff()])->get($uri)->assertStatus(200);
    }
    public function testLogin(): void
    {
        $this->submit('login',['username'=>'admin','password'=>'wrong'],false)->assertRedirectTo(site_url('login'));
        $this->submit('login',['username'=>'admin','password'=>'admin'],false)->assertRedirectTo(site_url('/'));
    }
    public function testCustomerCrud(): void
    {
        $this->submit('customers',['full_name'=>'Test Guest','email'=>'guest@example.com','phone'=>'09170000000'])->assertRedirectTo(site_url('customers'));
        $row=(new CustomerModel())->where('email','guest@example.com')->first();$this->assertNotNull($row);
        $this->submit('customers/'.$row['id'],['full_name'=>'Updated Guest','email'=>'guest@example.com','phone'=>''])->assertRedirect();
        $this->assertSame('Updated Guest',(new CustomerModel())->find($row['id'])['full_name']);
        $this->submit('customers/'.$row['id'].'/archive')->assertRedirect();$this->assertNotNull((new CustomerModel())->find($row['id'])['archived_at']);
        $this->submit('customers/'.$row['id'].'/restore')->assertRedirect();$this->assertNull((new CustomerModel())->find($row['id'])['archived_at']);
    }
    public function testProductCrudAndValidation(): void
    {
        $this->submit('products',['name'=>'Test Brew','category'=>'Coffee','price'=>'99.50','stock_quantity'=>'5'])->assertRedirectTo(site_url('products'));
        $row=(new ProductModel())->where('name','Test Brew')->first();$this->assertNotNull($row);
        $this->submit('products/'.$row['id'],['name'=>'Updated Brew','category'=>'Non-coffee','price'=>'109.25','stock_quantity'=>'9'])->assertRedirect();
        $this->assertSame(9,(int)(new ProductModel())->find($row['id'])['stock_quantity']);
        $this->submit('products/'.$row['id'].'/archive')->assertRedirect();$this->assertNotNull((new ProductModel())->find($row['id'])['archived_at']);
        $this->submit('products/'.$row['id'].'/restore')->assertRedirect();
        $count=(new ProductModel())->countAllResults();$this->submit('products',['name'=>'','category'=>'bad','price'=>'-1','stock_quantity'=>'-3'])->assertRedirect();$this->assertSame($count,(new ProductModel())->countAllResults());
    }
    public function testStaffCrudAndBlankPassword(): void
    {
        $this->submit('staff',['username'=>'barista','full_name'=>'Test Barista','password'=>'coffee123'])->assertRedirectTo(site_url('staff'));
        $row=(new UserModel())->where('username','barista')->first();$this->assertTrue(password_verify('coffee123',$row['password']));
        $this->submit('staff/'.$row['id'],['username'=>'barista','full_name'=>'Updated Barista','password'=>''])->assertRedirect();$this->assertSame($row['password'],(new UserModel())->find($row['id'])['password']);
        $this->submit('staff/'.$row['id'].'/archive')->assertRedirect();$this->assertNotNull((new UserModel())->find($row['id'])['archived_at']);
        $this->submit('staff/'.$row['id'].'/restore')->assertRedirect();$this->assertNull((new UserModel())->find($row['id'])['archived_at']);
    }
    public function testCannotArchiveSelfOrDuplicateUsername(): void
    {
        $id=$this->staff()['id'];$this->submit('staff/'.$id.'/archive')->assertRedirect();$this->assertNull((new UserModel())->find($id)['archived_at']);
        $this->submit('staff',['username'=>'admin','full_name'=>'Duplicate','password'=>'admin'])->assertRedirect();$this->assertSame(1,(new UserModel())->countAllResults());
    }
    public function testChangedCredentialsInvalidateSession(): void
    {
        $user=$this->staff();(new UserModel())->update($user['id'],['auth_version'=>2]);$this->withSession(['staff'=>$user])->get('products')->assertRedirectTo(site_url('login'));
    }
    public function testSaleAndReceipt(): void
    {
        $product=(new ProductModel())->first();$id=(new SaleService($this->db))->record($product['id'],1,$this->staff()['id'],2,bin2hex(random_bytes(16)),'500.00');
        $sale=$this->db->table('sales')->where('id',$id)->get()->getRowArray();$this->assertSame(33000,Money::cents((string)$sale['total_price']));
        $this->assertSame(22,(int)(new ProductModel())->find($product['id'])['stock_quantity']);
        $this->withSession(['staff'=>$this->staff()])->get('sales/'.$id)->assertSee('₱330.00');$this->withSession(['staff'=>$this->staff()])->get('sales')->assertSee('Isabel Santos');
    }
    public function testOversellingRollsBack(): void
    {
        try{(new SaleService($this->db))->record(1,null,$this->staff()['id'],100,bin2hex(random_bytes(16)));$this->fail('Oversale accepted');}
        catch(\DomainException $e){$this->assertStringContainsString('Not enough stock',$e->getMessage());}
        $this->assertSame(0,$this->db->table('sales')->countAllResults());$this->assertSame(24,(int)(new ProductModel())->find(1)['stock_quantity']);
    }
    public function testDuplicateCheckout(): void
    {
        $token=bin2hex(random_bytes(16));$service=new SaleService($this->db);$user=$this->staff()['id'];
        $this->assertSame($service->record(1,null,$user,1,$token),$service->record(1,null,$user,1,$token));
        $this->assertSame(1,$this->db->table('sales')->countAllResults());$this->assertSame(23,(int)(new ProductModel())->find(1)['stock_quantity']);
    }
    public function testUnderpaymentAndArchivedRecords(): void
    {
        $service=new SaleService($this->db);$user=$this->staff()['id'];
        foreach(['cash','product','customer'] as $case){
            if($case==='product')(new ProductModel())->update(1,['archived_at'=>date('Y-m-d H:i:s')]);
            if($case==='customer'){(new ProductModel())->update(1,['archived_at'=>null]);(new CustomerModel())->update(1,['archived_at'=>date('Y-m-d H:i:s')]);}
            try{$service->record(1,$case==='customer'?1:null,$user,1,bin2hex(random_bytes(16)),$case==='cash'?'1.00':null);$this->fail('Invalid sale accepted');}
            catch(\DomainException $e){$this->assertNotEmpty($e->getMessage());}
        }
        $this->assertSame(0,$this->db->table('sales')->countAllResults());$this->assertSame(24,(int)(new ProductModel())->find(1)['stock_quantity']);
    }
    public function testHistoryPreservesPriceAndArchiveRelationships(): void
    {
        $id=(new SaleService($this->db))->record(1,1,$this->staff()['id'],1,bin2hex(random_bytes(16)));
        (new ProductModel())->update(1,['name'=>'Changed','price'=>'1.00','archived_at'=>date('Y-m-d H:i:s')]);(new CustomerModel())->update(1,['archived_at'=>date('Y-m-d H:i:s')]);
        $result=$this->withSession(['staff'=>$this->staff()])->get('sales/'.$id);$result->assertSee('Spanish Latte');$result->assertSee('₱165.00');
    }
    public function testPreparedImagesAndUnsafeFiles(): void
    {
        $tmp=tempnam(sys_get_temp_dir(),'ember');$image=imagecreatetruecolor(640,480);imagepng($image,$tmp);imagedestroy($image);
        $file=new class($tmp,'avatar.png','image/png',filesize($tmp),UPLOAD_ERR_OK) extends UploadedFile{public function isValid(): bool{return true;}};
        $name=(new ImageUpload())->save($file,true);$filePath=WRITEPATH.'uploads/'.$name;$size=getimagesize($filePath);
        $this->assertSame(320,$size[0]);$this->assertSame(320,$size[1]);$this->assertSame(IMAGETYPE_WEBP,$size[2]);
        $this->withSession(['staff'=>$this->staff()])->get('media/'.$name)->assertStatus(200);unlink($filePath);unlink($tmp);
        $tmp=tempnam(sys_get_temp_dir(),'ember');file_put_contents($tmp,'<?php echo "bad";');
        $file=new class($tmp,'bad.php','image/png',filesize($tmp),UPLOAD_ERR_OK) extends UploadedFile{public function isValid(): bool{return true;}};
        try{(new ImageUpload())->save($file);$this->fail('Unsafe file accepted');}catch(\InvalidArgumentException $e){$this->assertNotEmpty($e->getMessage());}finally{unlink($tmp);}
    }
    public function testSeedPreservesProgress(): void
    {
        (new ProductModel())->update(1,['stock_quantity'=>7]);(new UserModel())->update(1,['password'=>'kept']);$this->seed(DemoSeeder::class);
        $this->assertSame(7,(int)(new ProductModel())->find(1)['stock_quantity']);$this->assertSame('kept',(new UserModel())->find(1)['password']);
    }
}
