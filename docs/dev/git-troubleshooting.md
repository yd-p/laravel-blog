# Git连接问题解决方案

## 问题描述
```
fatal: unable to access 'https://github.com/yd-p/laravel-blog.git/': 
Failed to connect to github.com port 443 after 75020 ms: Couldn't connect to server
```

这是一个常见的Git连接GitHub失败问题，通常由网络、DNS、代理或防火墙设置引起。

## 🚀 快速解决方案

### 方案1：使用修复脚本（推荐）
```bash
# 给脚本执行权限
chmod +x dev/fix-git-connection.sh

# 运行修复脚本
./dev/fix-git-connection.sh

# 选择"10. 自动诊断和修复"进行自动修复
```

### 方案2：手动修复步骤

#### 1. 检查网络连接
```bash
# 测试网络连通性
ping -c 3 8.8.8.8

# 测试DNS解析
nslookup github.com

# 测试GitHub连接
curl -I https://github.com --connect-timeout 10
```

#### 2. 配置Git代理（如果使用代理）
```bash
# 设置HTTP代理
git config --global http.proxy http://proxy-server:port
git config --global https.proxy http://proxy-server:port

# 如果不使用代理，清除代理设置
git config --global --unset http.proxy
git config --global --unset https.proxy
```

#### 3. 优化Git配置
```bash
# 增加超时时间
git config --global http.lowSpeedLimit 1000
git config --global http.lowSpeedTime 300

# 增加缓冲区大小
git config --global http.postBuffer 524288000

# 如果SSL有问题（不推荐，仅临时使用）
git config --global http.sslVerify false
```

#### 4. 使用SSH代替HTTPS
```bash
# 生成SSH密钥
ssh-keygen -t ed25519 -C "your-email@example.com"

# 添加SSH密钥到ssh-agent
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519

# 显示公钥（需要添加到GitHub）
cat ~/.ssh/id_ed25519.pub

# 测试SSH连接
ssh -T git@github.com

# 修改远程仓库URL为SSH
git remote set-url origin git@github.com:yd-p/laravel-blog.git
```

#### 5. 使用镜像源
```bash
# 方案A: 使用gitclone.com镜像
git config --global url."https://gitclone.com/github.com/".insteadOf "https://github.com/"

# 方案B: 使用cnpmjs.org镜像
git config --global url."https://github.com.cnpmjs.org/".insteadOf "https://github.com/"

# 清除镜像配置
git config --global --remove-section url
```

## 🔧 针对不同环境的解决方案

### macOS环境
```bash
# 检查系统代理设置
networksetup -getwebproxy Wi-Fi
networksetup -getsecurewebproxy Wi-Fi

# 如果使用Homebrew安装的Git，可能需要重新安装
brew reinstall git

# 检查防火墙设置
sudo /usr/libexec/ApplicationFirewall/socketfilterfw --getglobalstate
```

### 企业网络环境
```bash
# 通常需要配置企业代理
git config --global http.proxy http://corporate-proxy:8080
git config --global https.proxy http://corporate-proxy:8080

# 可能需要配置证书
git config --global http.sslCAInfo /path/to/certificate.pem
```

### 中国大陆网络环境
```bash
# 使用国内镜像源
git config --global url."https://gitee.com/".insteadOf "https://github.com/"

# 或使用专门的GitHub镜像
git config --global url."https://hub.fastgit.xyz/".insteadOf "https://github.com/"
```

## 🛠️ 诊断命令

### 检查当前Git配置
```bash
# 查看所有配置
git config --list

# 查看代理配置
git config --get http.proxy
git config --get https.proxy

# 查看镜像配置
git config --get-regexp url
```

### 网络诊断
```bash
# 检查DNS
dig github.com
nslookup github.com

# 检查路由
traceroute github.com

# 检查端口连通性
telnet github.com 443
nc -zv github.com 443
```

### Git详细调试
```bash
# 启用详细输出
export GIT_CURL_VERBOSE=1
export GIT_TRACE=1

# 执行Git操作查看详细信息
git clone https://github.com/yd-p/laravel-blog.git

# 关闭调试
unset GIT_CURL_VERBOSE
unset GIT_TRACE
```

## 📋 常见错误和解决方案

### 错误1: Connection timed out
```bash
# 解决方案：增加超时时间
git config --global http.lowSpeedTime 600
git config --global http.lowSpeedLimit 1000
```

### 错误2: SSL certificate problem
```bash
# 临时解决方案（不推荐）
git config --global http.sslVerify false

# 推荐解决方案：更新证书
# macOS
brew install ca-certificates
# 或更新系统证书
```

### 错误3: Proxy issues
```bash
# 清除所有代理设置
git config --global --unset http.proxy
git config --global --unset https.proxy
git config --global --unset http.sslProxy
```

### 错误4: DNS resolution failed
```bash
# 临时更改DNS
echo "nameserver 8.8.8.8" | sudo tee /etc/resolv.conf
echo "nameserver 1.1.1.1" | sudo tee -a /etc/resolv.conf

# 或在macOS中
sudo dscacheutil -flushcache
sudo killall -HUP mDNSResponder
```

## 🎯 推荐解决流程

1. **首先尝试**: 运行自动修复脚本
2. **如果失败**: 检查网络和DNS设置
3. **企业环境**: 配置代理设置
4. **仍然失败**: 使用SSH连接
5. **最后选择**: 使用镜像源

## 📞 获取帮助

如果以上方案都无法解决问题，请：

1. 运行诊断脚本收集信息
2. 检查网络管理员设置
3. 考虑使用VPN或其他网络环境
4. 联系IT支持团队

## 🔄 恢复默认设置

如果需要恢复Git的默认设置：

```bash
# 清除所有全局配置
git config --global --unset-all http.proxy
git config --global --unset-all https.proxy
git config --global --unset-all http.sslVerify
git config --global --unset-all http.lowSpeedLimit
git config --global --unset-all http.lowSpeedTime
git config --global --unset-all http.postBuffer
git config --global --remove-section url 2>/dev/null || true

# 重新设置基本配置
git config --global user.name "Your Name"
git config --global user.email "your-email@example.com"
```