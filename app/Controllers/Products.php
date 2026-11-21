<?php
namespace App\Controllers;
class Products extends ManageController
{
    protected string $modelClass=\App\Models\ProductModel::class;
    protected string $route='products';
    protected string $label='Products';
    protected ?string $uploadField='image';
    protected array $fields=[
        'name'=>['label'=>'Product name','type'=>'text','required'=>true],
        'category'=>['label'=>'Category','type'=>'select','options'=>['Coffee','Non-coffee','Pastries'],'required'=>true],
        'price'=>['label'=>'Price (PHP)','type'=>'number','step'=>'0.01','required'=>true],
        'stock_quantity'=>['label'=>'Stock quantity','type'=>'number','step'=>'1','required'=>true],
    ];
    protected array $rules=[
        'name'=>'required|max_length[100]', 'category'=>'required|in_list[Coffee,Non-coffee,Pastries]',
        'price'=>'required|regex_match[/^[0-9]{1,6}(?:\.[0-9]{1,2})?$/]',
        'stock_quantity'=>'required|is_natural|less_than_equal_to[1000000]',
    ];
}
