<?php
namespace App\Libraries;
use CodeIgniter\HTTP\Files\UploadedFile;
class ImageUpload
{
    public function save(UploadedFile $file, bool $avatar=false): string
    {
        $info=@getimagesize($file->getTempName());
        if (!$file->isValid() || !$info || !in_array($info[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP],true)
            || !in_array(strtolower($file->getClientExtension()),['jpg','jpeg','png','webp'],true)
            || $file->getSize()>2097152 || $info[0]>4096 || $info[1]>4096) {
            throw new \InvalidArgumentException('Choose a JPG, PNG, or WebP image up to 2 MB and 4096 × 4096 pixels.');
        }
        $directory=WRITEPATH.'uploads';
        if (!is_dir($directory)) { mkdir($directory,0775,true); }
        $name=bin2hex(random_bytes(16)).'.webp';
        $image=service('image','gd')->withFile($file->getTempName());
        if ($avatar) { $image->fit(320,320,'center'); } else { $image->resize(1000,800,true,'auto'); }
        $image->convert(IMAGETYPE_WEBP)->save($directory.DIRECTORY_SEPARATOR.$name,85);
        return $name;
    }
}
