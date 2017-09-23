<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    */

    'appId' => env('ENT_APP_ID', 'wx2f3229d1524c07ac'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'secret' => env('ENT_APP_SECRET', 'ZrRSozTOevekjEYAfmubzXvFQQbgYgvPKol5b1aNNBqtG0SqjeHbSkkIfxG4WfXd'),

    'token'   => env('WECHAT_TOKEN', 'your-token'),          // Token
    
    'aes_key' => env('WECHAT_AES_KEY', ''),                    // EncodingAESKey
    
    'enable_mock' => env('WECHAT_ENABLE_MOCK', true),
    
    'mock_user' =>env('MOCK_USER', 'CeShiXiaoQi'),
    
    'attendant_chat'=>1000009,
    'attendant_wap'=>1000009,
    'assitant'=>0
    
];
