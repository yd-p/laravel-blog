<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 确保存储目录存在
        Storage::disk('public')->makeDirectory('media');

        $user = User::first();

        // 创建示例媒体文件
        $mediaFiles = [
            [
                'name' => '网站横幅图',
                'file_name' => 'banner-1.jpg',
                'mime_type' => 'image/jpeg',
                'disk' => 'public',
                'path' => 'media/banner-1.jpg',
                'collection_name' => 'banners',
                'size' => 1024000,
                'width' => 1920,
                'height' => 1080,
                'uploaded_by' => $user?->id,
                'custom_properties' => [
                    'alt' => '网站横幅',
                    'title' => '欢迎来到我们的网站',
                ],
            ],
            [
                'name' => '产品展示图',
                'file_name' => 'product-1.jpg',
                'mime_type' => 'image/jpeg',
                'disk' => 'public',
                'path' => 'media/product-1.jpg',
                'collection_name' => 'products',
                'size' => 512000,
                'width' => 800,
                'height' => 600,
                'uploaded_by' => $user?->id,
                'custom_properties' => [
                    'alt' => '产品图片',
                    'featured' => true,
                ],
            ],
            [
                'name' => '用户头像',
                'file_name' => 'avatar-1.jpg',
                'mime_type' => 'image/jpeg',
                'disk' => 'public',
                'path' => 'media/avatar-1.jpg',
                'collection_name' => 'avatars',
                'size' => 102400,
                'width' => 200,
                'height' => 200,
                'uploaded_by' => $user?->id,
            ],
            [
                'name' => '文章配图',
                'file_name' => 'post-image-1.jpg',
                'mime_type' => 'image/jpeg',
                'disk' => 'public',
                'path' => 'media/post-image-1.jpg',
                'collection_name' => 'posts',
                'size' => 768000,
                'width' => 1200,
                'height' => 800,
                'uploaded_by' => $user?->id,
                'custom_properties' => [
                    'alt' => '文章配图',
                    'caption' => '这是一张精美的文章配图',
                ],
            ],
            [
                'name' => '产品说明书',
                'file_name' => 'manual.pdf',
                'mime_type' => 'application/pdf',
                'disk' => 'public',
                'path' => 'media/manual.pdf',
                'collection_name' => 'documents',
                'size' => 2048000,
                'uploaded_by' => $user?->id,
                'custom_properties' => [
                    'version' => '1.0',
                    'language' => 'zh-CN',
                ],
            ],
            [
                'name' => '宣传视频',
                'file_name' => 'promo-video.mp4',
                'mime_type' => 'video/mp4',
                'disk' => 'public',
                'path' => 'media/promo-video.mp4',
                'collection_name' => 'default',
                'size' => 10240000,
                'width' => 1920,
                'height' => 1080,
                'uploaded_by' => $user?->id,
                'custom_properties' => [
                    'duration' => '00:02:30',
                    'quality' => '1080p',
                ],
            ],
            [
                'name' => '背景音乐',
                'file_name' => 'background-music.mp3',
                'mime_type' => 'audio/mpeg',
                'disk' => 'public',
                'path' => 'media/background-music.mp3',
                'collection_name' => 'default',
                'size' => 3072000,
                'uploaded_by' => $user?->id,
                'custom_properties' => [
                    'duration' => '00:03:45',
                    'artist' => '未知',
                ],
            ],
        ];

        foreach ($mediaFiles as $mediaData) {
            Media::create($mediaData);
        }

        $this->command->info('媒体库示例数据创建成功！');
    }
}
