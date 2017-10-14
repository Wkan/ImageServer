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
            abort(422, 'Cant find upload image.');
        }

        $imageFile = $request->file('image');

        if (!$imageFile->isValid()) {
            abort(422, $imageFile->getErrorMessage());
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

        $image->save($imagePath);

        return response()->json([
            'error'   => 0,
            'data'    => [
                'key' => $imageHashKey,
                'url' => route('get', ['query' => $imageHashKey]),
            ],
            'message' => '图片保存成功',
        ], 200, [], JSON_UNESCAPED_UNICODE);
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
