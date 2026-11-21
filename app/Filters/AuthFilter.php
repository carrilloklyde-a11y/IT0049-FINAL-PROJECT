<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments=null)
    {
        $staff=session('staff');
        $user=$staff ? (new UserModel())->find($staff['id']) : null;
        if (!$user || $user['archived_at'] || (int)$user['auth_version'] !== (int)($staff['auth_version']??0)) {
            session()->remove('staff');
            return redirect()->to(site_url('login'))->with('error','Please sign in to continue.');
        }
        session()->set('staff',array_intersect_key($user,array_flip(['id','username','full_name','avatar','auth_version'])));
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments=null)
    {
        $response->setHeader('Cache-Control','no-store, private');
    }
}
