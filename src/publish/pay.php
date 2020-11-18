<?php
declare(strict_types=1);

return [
    'WeChatPay'=>[
        'WxPay'=>[
            'app_id' =>env('WECHAT_PAYMENT_APP_ID', 'wx8b25a352402a35ec'),//微信分配的公众账号ID
            'mch_id' =>env('WECHAT_PAYMENT_MCH_ID', '1602794825'),//微信商户
            'key'    =>env('WECHAT_PAYMENT_KEY', 'GZxundongkeji2017070707070707070'),//商户秘钥
            'cert_path' => env('WECHAT_PAYMENT_CERT_PATH', BASE_PATH.'/public/cert/wechat/apiclient_cert.pem'),//商户证书    // XXX: 绝对路径！！！！
            'key_path'  => env('WECHAT_PAYMENT_KEY_PATH', BASE_PATH.'/public/cert/wechat/apiclient_key.pem'),      // XXX: 绝对路径！！！！
            'notify_url'=>  env('WECHAT_PAYMENT_NOTIFY_URL', 'http://test_pay.xundong.top/notify_url'),// 默认支付结果通知地址
        ],
        'AppPay'=>[
            'app_id'=>env('WECHAT_PAYMENT_APP_ID', 'wx42238f73ed96d453'),//微信开放平台审核通过的应用APPID
        ],
        'Transfers'=>[
            'mchAppId' =>env('WECHAT_PAYMENT_MCH_APP_ID', ''),//商户appid，暂时没有用
        ],
    ],
    'AliPay'=>[
        'common'=>[
            'appCertPath'=>env('ALIPAY_APP_CERT_PATH',BASE_PATH.'/public/cert/alipay/appCertPublicKey.crt'),//app应用证书
            'alipayCertPath'=>env('ALIPAY_ALIPAY_CERT_PATH',BASE_PATH.'/public/cert/alipay/alipayCertPublicKey_RSA2.crt'),//阿里证书
            'rootCertPath'=>env('ALIPAY_ROOT_CERT_PATH',BASE_PATH.'/public/cert/alipay/alipayRootCert.crt'),//根证书
            'app_id'=>env('ALIPAY_APP_ID','2021001192692558'),//应用appid
            //秘钥
            'rsaPrivateKey'=>env('ALIPAY_RSA_PRIVATE_KEY','MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQC+iaFgH1f7VKPIRm9+cAobNOcYGHtH+NDDvMB28PYmxK8o1fP8kid901+KXO44dr4Vc7H8oUb3ZdqfJ5i1yyx0cdUi7iJJz7C/j4rqUE2R4l6RwhymOI33vNWtiJkz53C3W7TYj/7VyoCVIcitgEF7p3DojxpoGWZh+yhZege1/ao1q2r862l0osYqSPXNBt0+WcoTG4DkwPE/ty0x6xzE00I6QlhqULtjwtHE7Xe+UbhUWBspNdCG5lLXyCbO7DBDt4toLzitYImG0dTji2sepJ8IAKaeVeHV88hR6m7LN98BTzDzb+lbI3sp5D+tfJag/PIISzJPRA9DK+ulpE09AgMBAAECggEBAKUi45pLkBVj5g9N/JO4xwmNcDyR+0cFfuomTSjI7o3rKWRbCnt8sH19FPD2WgDV6SjufPRXUwpYXIJT7yagUIcboX4EpfMS7j0YS22I67HkX9I0SmBF48UzBH7CRQWXMTm5YaPQKS1htM+L2EGHRznEhTcJz/kpCnMhGndyK/ytvk7n4vmKsF9jcUw1RdzgEt1qcdkft4IQAfOAXR3EdxCuOUUAt5yLEHFJOAE2tdqDYCeHTZbG2sG89PIMRqxadQXuJNWxnZhWIa73Z3vGB0ZA1qeGDzIcwFCGrwkqq2lOcGawQ7EyQgikSs64r9uOEg3im/U00WvHCA6r13DYbIECgYEA4U6tNHNNZD8gsfK/1hnuy4Xx4eKSPxxDFw2JLMCwgkYaAwEPQAnXJ0mrpz7WOI1dmvDfEMBie7lMhWUYgpazIA5jD7X+PAOsYXBKoXrcWONzDBBz8VC3gn2z5U1ZbEYvY64gCxGaq1GwQkD47hE7dVXw9lTB3/qCC7v73lc/w7cCgYEA2H5m1w3PcejVzHdl2fT6bpze9+TiBlqzxkbwDd0aM//s+qE2kmdiu33sSg/nZ38ne6mtTJ4YxNPW/v5vWYtKpxD4kCiktoZHlPSFqEoMFTRg5Nlr3d2lWz8fcCSc40bmVCUHcHCpT6fH8GxK8lSVcMeQe2Cs6leIO/dzFtcL/qsCgYEArp916tL6xpmO05ybRmtvAtrbsAEU7EkRToq1KLRnmXCPtIhbs3xs/wxAOC2hhs86H15U5PzW16G0RyclqgD27/92k7SwSP9n2VVY4nMA2PvgLReLZRr0P/UBOtWKWzrCe/V27F1GjUWQ4KjOxmUguj5TtffXE6tqo4GdIqRozV0CgYA/3Glxnn1G2KvYeI/uzzjgUB3lefrCbRcCsgI11LbaB/Bbrhpu7VhOfkKpRi+4c+WtsMuSAkDELskIxv0JqPEIfxUTUSnlPY73/Xk/vzf5OU4rs4cCF1OjqucXKXWuBUgEIjgjjtgiyxdLcGZbPWAnuHaCLm5TYiapqcClSCIdqQKBgQCBLLuDJE5GfTu5YJpRaGWcB/hAW1Hr/qlQQb7ix+4ijHaOFEvgLOUA6ArpUKaMjFzs21fWw4h9Y2EmLQq7l0GoQflr+thhKS6SeU+BDIoN9dE8ZdIeFfcuU4ZowHxGY/Fn+CgcxFXidzcUj+2GGkWP7PtlZ+9hsl7BvWQZRSZqMg=='),
        ]
    ]
];