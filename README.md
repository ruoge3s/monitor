# PHP开发之文件监控

> 在使用hyperf开发的时候，需要热重启
> 用官方文档中提供的插件，满足不了我的需求，因此基于[ha-ni-cc/hyperf-watch](https://github.com/ha-ni-cc/hyperf-watch)进行改造以满足个人需求

## 使用

### 配置编辑config.php
```php
return [
    // 项目名称
    "pname" => [
        "watch"=> [
            // 监控的目标目录
            "dir"       => "/var/dir/",
            // 监控的文件内容
            "ext"       => ["env", "php"],
            // 不监控变化的内容
            "exclude"   => ["vendor"],
            // 刷新间隔时长
            "rate"      => 2000
        ],
        "task"=> "/usr/bin/rsync -rzvt /var/dir/* root@192.168.1.5:/home/test"
    ],
];
```
### 使用命令启动

```bash
php bin.php pname
```
