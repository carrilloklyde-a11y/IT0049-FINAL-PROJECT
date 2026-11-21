<?php
namespace App\Controllers;
class Staff extends ManageController
{
    protected string $modelClass=\App\Models\UserModel::class;
    protected string $route='staff';
    protected string $label='Staff';
    protected ?string $uploadField='avatar';
    protected array $fields=[
        'username'=>['label'=>'Username','type'=>'text','required'=>true],
        'full_name'=>['label'=>'Full name','type'=>'text','required'=>true],
        'password'=>['label'=>'Password','type'=>'password','required'=>true],
    ];
    protected array $rules=['username'=>'required|alpha_dash|max_length[50]','full_name'=>'required|max_length[100]','password'=>'required|min_length[4]|max_length[200]'];
}
