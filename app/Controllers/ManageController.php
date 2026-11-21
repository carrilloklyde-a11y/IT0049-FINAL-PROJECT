<?php
namespace App\Controllers;
use App\Libraries\ImageUpload;
abstract class ManageController extends BaseController
{
    protected string $modelClass;
    protected string $route;
    protected string $label;
    protected array $fields;
    protected array $rules;
    protected ?string $uploadField=null;
    public function index()
    {
        $archived=$this->request->getGet('archived')==='1';
        $model=new $this->modelClass();
        $model->where('archived_at'.($archived?' !=':''),null);
        $records=$model->orderBy('id','DESC')->findAll();
        return $this->render('manage/index',['title'=>$this->label,'route'=>$this->route,'label'=>$this->label,'fields'=>$this->fields,'records'=>$records,'archived'=>$archived,'uploadField'=>$this->uploadField]);
    }
    public function form(?int $id=null)
    {
        $record=$id ? (new $this->modelClass())->find($id) : [];
        if ($id && (!$record || $record['archived_at'])) { $this->notFound(); }
        return $this->render('manage/form',['title'=>($id?'Edit ':'Add ').rtrim($this->label,'s'),'route'=>$this->route,'label'=>$this->label,'fields'=>$this->fields,'record'=>$record,'uploadField'=>$this->uploadField]);
    }
    public function save(?int $id=null)
    {
        $model=new $this->modelClass();
        $existing=$id ? $model->find($id) : null;
        if ($id && (!$existing || $existing['archived_at'])) { $this->notFound(); }
        $data=[]; $errors=[];
        foreach ($this->fields as $field=>$meta) {
            $value=$this->request->getPost($field)??'';
            if (!is_string($value)) { $errors[$field]='Enter a valid '.$meta['label'].'.'; $value=''; }
            $data[$field]=trim($value);
        }
        $rules=$this->rules;
        if ($this->route==='staff' && $id && $data['password']==='') { unset($rules['password']); }
        if (!$this->validateData($data,$rules)) { $errors += $this->validator->getErrors(); }
        if ($this->route==='staff') {
            $query=(new \App\Models\UserModel())->where('username',$data['username']);
            if ($id) { $query->where('id !=',$id); }
            if ($query->first()) { $errors['username']='This username is already in use, including archived accounts.'; }
        }
        $returnPath=$this->route.($id?'/'.$id.'/edit':'/new');
        $safeInput=$data; unset($safeInput['password']);
        if ($errors) { return redirect()->to(site_url($returnPath))->with('errors',$errors)->with('form_data',$safeInput); }
        if ($this->route==='staff') {
            if ($data['password']!=='') { $data['password']=password_hash($data['password'],PASSWORD_DEFAULT); $data['auth_version']=$id ? (int)$existing['auth_version']+1 : 1; }
            else { unset($data['password']); }
        }
        $newImage=null;
        try {
            if ($this->uploadField) {
                $file=$this->request->getFile($this->uploadField);
                if ($file && $file->getError()!==UPLOAD_ERR_NO_FILE) { $newImage=(new ImageUpload())->save($file,$this->route==='staff'); $data[$this->uploadField]=$newImage; }
            }
            if ($id) { $ok=$model->update($id,$data); } else { $ok=$model->insert($data); }
            if (!$ok) { throw new \RuntimeException('The record could not be saved. Please check the details.'); }
        } catch (\Throwable $e) {
            if ($newImage && is_file(WRITEPATH.'uploads/'.$newImage)) { unlink(WRITEPATH.'uploads/'.$newImage); }
            $message=$e instanceof \InvalidArgumentException ? $e->getMessage() : 'The record could not be saved. Please try again.';
            log_message('error','Record save failed: {type}',['type'=>get_class($e)]);
            return redirect()->to(site_url($returnPath))->with('errors',[$message])->with('form_data',$safeInput);
        }
        if ($id && $this->route==='staff' && (int)session('staff')['id']===$id) {
            $fresh=$model->find($id); session()->set('staff',array_intersect_key($fresh,array_flip(['id','username','full_name','avatar','auth_version'])));
        }
        return redirect()->to(site_url($this->route))->with('success',($id?'Changes saved.':'Record added.').' A little more organized.');
    }
    public function archive(int $id)
    {
        $model=new $this->modelClass();
        if (!$model->find($id)) { $this->notFound(); }
        if ($this->route==='staff' && (int)session('staff')['id']===$id) { return redirect()->to(site_url('staff'))->with('error','You cannot archive the account you are signed in with.'); }
        $model->update($id,['archived_at'=>date('Y-m-d H:i:s')]);
        return redirect()->to(site_url($this->route))->with('success','Record archived. Existing sales history is preserved.');
    }
    public function restore(int $id)
    {
        $model=new $this->modelClass(); if (!$model->find($id)) { $this->notFound(); }
        $model->update($id,['archived_at'=>null]);
        return redirect()->to(site_url($this->route))->with('success','Record restored.');
    }
}
