<?php
namespace App\Controllers;
use CodeIgniter\Controller;
abstract class BaseController extends Controller
{
    protected $helpers = ['url','form','pos'];
    protected function render(string $view, array $data=[]): string
    {
        return view($view, $data + ['currentUser'=>session('staff'), 'path'=>trim(service('uri')->getPath(),'/')]);
    }
    protected function notFound(): never { throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
}
