<?php
namespace App\Controllers;
use App\Models\UserModel;
class Auth extends BaseController
{
    public function login()
    {
        if (session('staff')) { return redirect()->to(site_url('/')); }
        return $this->render('auth/login',['title'=>'Welcome back']);
    }
    public function attempt()
    {
        if (!service('throttler')->check('login-'.hash('sha256',$this->request->getIPAddress()),10,60)) {
            return redirect()->to(site_url('login'))->with('error','Too many attempts. Please try again in a minute.');
        }
        if (!$this->validateData($this->request->getPost(),['username'=>'required|max_length[50]','password'=>'required|max_length[200]'])) {
            return redirect()->to(site_url('login'))->with('error','Enter a username and password.');
        }
        $user=(new UserModel())->where('username',trim($this->request->getPost('username')))->where('archived_at',null)->first();
        if (!$user || !password_verify($this->request->getPost('password'),$user['password'])) {
            return redirect()->to(site_url('login'))->with('error','The username or password is incorrect.');
        }
        session()->regenerate(true);
        session()->set('staff',array_intersect_key($user,array_flip(['id','username','full_name','avatar','auth_version'])));
        return redirect()->to(site_url('/'))->with('success','Welcome back. Let’s make it a good café day.');
    }
    public function logout()
    {
        session()->destroy(); return redirect()->to(site_url('login'));
    }
}
