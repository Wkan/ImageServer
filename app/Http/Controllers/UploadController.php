<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;

class UploadController extends Controller
{
    protected $manager;
    protected $savePath;

    public function __construct()
    {
        $this->manager = new ImageManager([
            'driver' => 'imagick',
        ]);
        $this->savePath = realpath(env('IMAGE_PATH', sys_get_temp_dir() . '/image_server'));
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('image')) {
            return [
                'error'   => 1,
                'message' => '没有上传的图片'
            ];
        }

        $imageFile = $request->file('image');

        if (!$imageFile->isValid()) {
            return [
                'error'   => 1,
                'message' => $imageFile->getErrorMessage(),
            ];
        }

        // 加载图片
        $image = $this->manager->make($imageFile);

        $image->orientate(); // 把图片转到正确的方向
        $image->encode('jpg'); // 用jpeg格式

        $imageHashKey = $this->getKeyFromHash($this->hashFile($imageFile));
        $imagePath = $this->getSavePathOfKey($imageHashKey);

        $dirName = dirname($imagePath);
        if (!is_dir($dirName)) {
            mkdir($dirName, 0755, true);
        }
        $success = $image->save($imagePath);

        if ($success) {
            return [
                'error'   => 0,
                'data'    => [
                    'key' => $imageHashKey,
                ],
                'message' => '图片保存成功',
            ];
        }

        return [
            'error'   => 1,
            'data'    => null,
            'message' => '图片处理/保存失败',
        ];
    }

    protected function getSavePathOfKey($key)
    {
        return $this->savePath . DIRECTORY_SEPARATOR . $key;
    }

    protected function getKeyFromHash($hash)
    {
        return substr($hash, 0, 2)
            . DIRECTORY_SEPARATOR
            . substr($hash, 2, 2)
            . DIRECTORY_SEPARATOR
            . substr($hash, 4, 2)
            . DIRECTORY_SEPARATOR
            . substr($hash, 6);
    }

    protected function hashFile(UploadedFile $file)
    {
        return hash_file('sha1', $file->getPathname());
    }
}
