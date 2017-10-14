# ImageServer

一个基于url参数的图片处理服务器

## 安装

```shell
# start

git clone https://github.com/Wkan/ImageServer.git

composer install

# set IMAGE_PATH & CACHE_PATH in .env file

# configure nginx like nginx.conf.example

# done
```

## 使用

1. 用表单上传图片到 `/upload` 接口，需要用 `image` 作为表单的 `name`;
1. 接口返回图片的 `key`;
1. 获取图片时使用上传时得到的 `key` ，通过 `/images + key` 的形式访问；
1. 需要对图片处理时，在链接后跟上 `!key=value` 形式的参数，图片会被自动处理，并缓存；

> 目前可用的处理参数

key 参数名|value 参数值|说明
---|---|---
`w`|int|缩小图片到多大的宽度
`pt`|`h`,`m`,`s`|三种预置图片处理方式
