# API文档说明

## 概述

本文档简要说明一下各个sso的接口定义，**所有接口都没有带host，请根据自己的实际情况添加**

## 校验code接口

### 请求地址
```
/sso/api/check_code
```

### 请求方式
```
POST(form-data)
```

### 请求参数
1. code：认证系统携带给子系统的code（必需）
2. app_key：认证系统分配给子系统的key值（必需）
3. app_id：认证系统分配给子系统的appid（必需）

### 成功响应示例
```
{
    "status": 0,
    "data": {
        "ticket": "6db5Y8XFeqUppTtNDutquAFFQQAO3TemiS%252BNsazVBdXD38K1yGrmTrPpV0YcxhWZ6%252ByBiA1ZH%252FByfNOcFg9xx3Kf3GwxFyuEQgGyFyQJM5yF",
        "username": "xxxxxxx"
    },
    "msg": "ok"
}
```

### 失败示例响应示例
```
{
    "status": 1,
    "data": null,
    "msg": "check error"
}
```

### 注意 
1. 返回的数据格式为json，当status为0时表示成功，为1时表示失败
2. msg为提示信息

## 校验ticket接口

### 请求地址
```
/sso/api/check_ticket
```

### 请求方式
```
POST(form-data)
```

### 请求参数
1. ticket：认证系统分配给子系统的会话凭证（必需）
2. app_id：认证系统分配给子系统的appid（必需）

### 成功响应示例
```
{
    "status": 0,
    "data": null,
    "msg": "ok"
}
```

### 失败响应示例
```
{
    "status": 1,
    "data": null,
    "msg": "check ticket failed"
}
```

## 统一登录地址

### 请求地址
```
/sso/login
```

### 请求方式
```
GET
```

### 请求参数
1. app_id：认证系统分配给子系统的appid（必需）
2. jumpto：需要跳转的子系统地址（非必需）

### 请求示例
```
/sso/login?app_id=1&jumpto=%2F
```

### 注意
1. jumpto地址需要url encode，不同语言请自行处理
2. 请携带参数重定向（302）至此地址
3. 若子系统没有携带jumpto参数，认证系统在跳转回子系统的时候会默认带上 jumpto参数（值为index），这时子系统需要对其做下处理

## 统一退出地址

### 请求地址
```
/sso/logout
```

### 请求方式
```
GET
```

### 请求参数
1. app_id：认证系统分配给子系统的appid（必需）
2. jumpto：需要跳转的子系统地址（非必需）

### 请求示例
```
/sso/logout?app_id=1&jumpto=%2F
```

### 注意
1. jumpto地址需要url encode，不同语言请自行处理
2. 请携带参数重定向（302）至此地址
3. 若子系统没有携带jumpto参数，认证系统在跳转回子系统的时候会默认带上 jumpto参数（值为index）， 这时子系统需要对其做下处理
