<?php
namespace App\Controllers;
class Media extends BaseController
{
    public function show(string $filename)
    {
        if (!preg_match('/^[a-f0-9]{32}\.webp$/D',$filename) || !is_file(WRITEPATH.'uploads/'.$filename)) { $this->notFound(); }
        return $this->response->setContentType('image/webp')->setHeader('X-Content-Type-Options','nosniff')->setBody(file_get_contents(WRITEPATH.'uploads/'.$filename));
    }
}
