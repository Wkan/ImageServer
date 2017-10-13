<?php

namespace App\Http\Controllers;

use function GuzzleHttp\Psr7\parse_query;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class GetController extends Controller
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

    public function get($query)
    {
        // 使用`!`作为参数分割符
        $exploded = explode('!', $query);

        $path = $exploded[0];
        $realPath = $this->savePath . DIRECTORY_SEPARATOR . $path;

        // 检查文件是否存在
        if (!file_exists($realPath)) {
            return response('image not found!', 404);
        }

        // 加载图片
        $image = $this->manager->make($realPath);

        // 获取参数
        $params = parse_query($exploded[1] ?? '');

        if (isset($params['w']) || isset($params['h'])) {
            $this->parseResize($image, $params['w'] ?? null, $params['h'] ?? null);
        }

        $image->interlace(); // 使用交错，jpg图片可以渐进加载
        $image->getCore()->stripImage(); // 去除图片的exif
        $image->encode('jpg');

        // 保存图片
        $image->save($this->savePath . DIRECTORY_SEPARATOR . $query, 60);

        return $image->response(null, 60);
    }

    /**
     * @param Image    $image
     * @param null|int $width
     * @param null|int $height
     *
     * @return Image
     */
    protected function parseResize($image, $width = null, $height = null)
    {
        return $image->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }
}
