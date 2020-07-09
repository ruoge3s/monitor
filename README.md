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


## task任务命令

- 使用密钥同步文件
```bash
eval $(ssh-agent -s) && ssh-add ~/key/txy.pem && /usr/bin/rsync -rzvt --exclude "vendor" --exclude "runtime" /project/dir/* root@192.168.1.3:/data/api/unify/
```

- 使用免密模式同步文件
```bash
/usr/bin/rsync -rzvt --exclude "vendor" --exclude "runtime" /project/dir/* root@192.168.1.3:/data/api/unify/
```


## 手动同步

- 配置说明

  1. 把下面的同步命令sync.sh添加到项目的根目录，并添加可执行权限
  2. 在phpstorm中，配置sync.sh为可运行，每次修改完内容，点击一下执行，就能同步到远程服务器进行测试了

- 同步命令

    sync.sh
    ```bash
    #!/usr/bin/env bash
    cd `dirname $0`
    
    echo "🚀 $(date "+%Y-%m-%d %H:%M:%S") 开始同步⛽️"
    
    # 目标地址
    REMOTE="root@192.168.1.1"
    # 目标目录
    REMOTE_DIR="/data/project/"
    
    # 具体要执行的命令
    eval $(ssh-agent -s) \
    && ssh-add ~/sk/txy \
    && /usr/bin/rsync -rzvt \
        --exclude "sync.sh" \
        --exclude "vendor" \
        --exclude "runtime" \
        `pwd`/* ${REMOTE}:${REMOTE_DIR} \
    && ssh -p22 ${REMOTE} "sed -i \"s/VERSION_PLACEHOLDER/updateAt \$(date \"+%Y-%m-%d %H:%M:%S\")/g\" ${REMOTE_DIR}config/config.php" \
    && ssh -p22 ${REMOTE} "docker restart unify"
    
    echo "✅ $(date "+%Y-%m-%d %H:%M:%S") 同步结束 😂"
    ```
