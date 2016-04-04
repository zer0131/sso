# SSO

## 概述

PHP实现的SSO代码，此代码基于框架OneFox实现。

> [OneFox](https://github.com/zer0131/OneFox)

## 说明

### 目录及文件说明

```
├─Config                      配置目录
│ ├─sso.php                   SSO配置文件
├─Controller                  Controller目录
│ ├─Sso                       SSO模块
│ | ├─ApiController.php       接口控制器类
│ | ├─LoginController.php     登陆地址控制器类
├─Lib                         自定义类库目录
│ ├─SSO                       SSO模块
│ | ├─Code.php                code操作类
│ | ├─Session.php             session操作类
│ | ├─Ticket.php              ticket操作类
```

### 使用

将对应的目录中的内容拷贝到基于OneFox框架实现的业务代码目录中

**切拷贝时一定要主要目录的对应**
