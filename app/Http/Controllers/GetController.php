<?php

namespace App\Http\Controllers;

use function GuzzleHttp\Psr7\parse_query;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class GetController extends Controller
{
    protected $manager;
    protected $savePath;
    protected $cachePath;

    const PRESET_TEMPLATE = [
        'h' => [
            'w' => 1000,
        ],
        'm' => [
            'w' => 400,
        ],
        's' => [
            'w' => 200,
        ]
    ];


    public function __construct()
    {
        $this->manager = new ImageManager([
            'driver' => 'imagick',
        ]);
        $this->savePath = realpath(env('IMAGE_PATH', sys_get_temp_dir() . '/image_server'));
        $this->cachePath = realpath(env('CACHE_PATH', sys_get_temp_dir() . '/image_server'));
    }

    public function get($query)
    {
        // 使用`!`作为参数分割符
        $exploded = explode('!', $query);

        $path = $exploded[0];
        $realPath = $this->savePath . DIRECTORY_SEPARATOR . $path;

        // 检查文件是否存在
        if (!file_exists($realPath)) {
            abort(404, 'Not Found Image.');
        }

        // 加载图片
        $image = $this->manager->make($realPath);

        // 获取参数
        $params = parse_query($exploded[1] ?? '');

        // 处理图片
        $this->parseImage($image, $params);

        // 图片路径
        $imagePath = $this->cachePath . DIRECTORY_SEPARATOR . $query;

        // 创建目录
        $dirName = dirname($imagePath);
        if (!is_dir($dirName)) {
            mkdir($dirName, 0755, true);
        }

        // 保存图片
        file_put_contents($imagePath, $image);

        // 直接返回一个图片响应
        return response($image)
            ->header('Content-Type', $image->mime())
            ->header('Content-Length', strlen($image))
            ->header('Cache-Control', 'max-age=2592000');
    }

    /**
     * @param Image $image
     * @param array $params
     */
    protected function parseImage($image, $params = [])
    {
        if (isset($params['pt'])) {
            // 使用了预置样式
            $pt = $params['pt'];
            $this->parseImage($image, static::PRESET_TEMPLATE[$pt] ?? []);
        }
        if (isset($params['w']) || isset($params['h'])) {
            $this->parseResize($image, $params['w'] ?? null, $params['h'] ?? null);
        }

        $image->interlace(); // 使用交错，jpg图片可以渐进加载
        $image->getCore()->stripImage(); // 去除图片的exif
        $image->encode('jpg', $params['q'] ?? 75);
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
