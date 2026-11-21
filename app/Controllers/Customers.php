<?php
namespace App\Controllers;
class Customers extends ManageController
{
    protected string $modelClass=\App\Models\CustomerModel::class;
    protected string $route='customers';
    protected string $label='Customers';
    protected array $fields=[
        'full_name'=>['label'=>'Full name','type'=>'text','required'=>true],
        'email'=>['label'=>'Email address','type'=>'email','required'=>true],
        'phone'=>['label'=>'Phone number','type'=>'tel','required'=>false],
    ];
    protected array $rules=['full_name'=>'required|max_length[100]','email'=>'required|valid_email|max_length[100]','phone'=>'permit_empty|max_length[20]|regex_match[/^[+0-9() .-]*$/]'];
}
