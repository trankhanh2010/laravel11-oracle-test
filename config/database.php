<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    // 'default' => env('DB_CONNECTION', 'oracle'),
    'default' => 'oracle_his',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],
        'telescope_mysql' => [
            'driver' => 'mysql',
            'host' => env('TELESCOPE_DB_HOST', '127.0.0.1'),
            'port' => env('TELESCOPE_DB_PORT', '3306'),
            'database' => env('TELESCOPE_DB_DATABASE', 'laravel_test_tele'),
            'username' => env('TELESCOPE_DB_USERNAME', 'root'),
            'password' => env('TELESCOPE_DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        'oracle_his' => [
            'driver' => 'oracle',
            'host' => env('DB_HIS_HOST', '192.168.101.251'),
            'port' => env('DB_HIS_PORT', '1521'),
            'database' => env('DB_HIS_DATABASE', 'oracletest'),
            'username' => env('DB_HIS_USERNAME', 'HIS_RS'),
            'password' => env('DB_HIS_PASSWORD', 'HIS_RS'),
            'service_name' => env('DB_HIS_SERVICE_NAME', 'orcl'),
            'charset' => 'AL32UTF8',
            'prefix' => '',
            // 'options' => [
            //     PDO::ATTR_PERSISTENT => true, // Kết nối liên tục
            // ],
        ],
        'oracle_acs' => [
            'driver' => 'oracle',
            'host' => env('DB_ACS_HOST', '192.168.101.251'),
            'port' => env('DB_ACS_PORT', '1521'),
            'database' => env('DB_ACS_DATABASE', 'oracletest'),
            'username' => env('DB_ACS_USERNAME', 'ACS_RS'),
            'password' => env('DB_ACS_PASSWORD', 'ACS_RS'),
            'service_name' => env('DB_ACS_SERVICE_NAME', 'orcl'),
            'charset' => 'AL32UTF8',
            'prefix' => '',
            // 'options' => [
            //     PDO::ATTR_PERSISTENT => true, // Kết nối liên tục
            // ],
        ],
        'oracle_sda' => [
            'driver' => 'oracle',
            'host' => env('DB_SDA_HOST', '192.168.101.251'),
            'port' => env('DB_SDA_PORT', '1521'),
            'database' => env('DB_SDA_DATABASE', 'oracletest'),
            'username' => env('DB_SDA_USERNAME', 'SDA_RS'),
            'password' => env('DB_SDA_PASSWORD', 'SDA_RS'),
            'service_name' => env('DB_SDA_SERVICE_NAME', 'orcl'),
            'charset' => 'AL32UTF8',
            'prefix' => '',
            // 'options' => [
            //     PDO::ATTR_PERSISTENT => true, // Kết nối liên tục
            // ],
        ],
        'oracle_emr' => [
            'driver' => 'oracle',
            'host' => env('DB_EMR_HOST', '192.168.101.251'),
            'port' => env('DB_EMR_PORT', '1521'),
            'database' => env('DB_EMR_DATABASE', 'oracletest'),
            'username' => env('DB_EMR_USERNAME', 'EMR_RS'),
            'password' => env('DB_EMR_PASSWORD', 'EMR_RS'),
            'service_name' => env('DB_EMR_SERVICE_NAME', 'orcl'),
            'charset' => 'AL32UTF8',
            'prefix' => '',
            // 'options' => [
            //     PDO::ATTR_PERSISTENT => true, // Kết nối liên tục
            // ],
        ],
        'oracle_sar' => [
            'driver' => 'oracle',
            'host' => env('DB_SAR_HOST', '192.168.101.251'),
            'port' => env('DB_SAR_PORT', '1521'),
            'database' => env('DB_SAR_DATABASE', 'oracletest'),
            'username' => env('DB_SAR_USERNAME', 'SAR_RS'),
            'password' => env('DB_SAR_PASSWORD', 'SAR_RS'),
            'service_name' => env('DB_SAR_SERVICE_NAME', 'orcl'),
            'charset' => 'AL32UTF8',
            'prefix' => '',
            // 'options' => [
            //     PDO::ATTR_PERSISTENT => true, // Kết nối liên tục
            // ],
        ],
        'oracle_emr_final' => [
            'driver' => 'oracle',
            'host' => env('DB_SAR_HOST', '192.168.101.251'),
            'port' => env('DB_SAR_PORT', '1521'),
            'database' => env('DB_EMR_FINAL_DATABASE', 'oracletest'),
            'username' => env('DB_EMR_FINAL_USERNAME', 'EMR_FINAL'),
            'password' => env('DB_EMR_FINAL_PASSWORD', 'EMR_FINAL'),
            'service_name' => env('DB_SAR_SERVICE_NAME', 'orcl'),
            'charset' => 'AL32UTF8',
            'prefix' => '',
            // 'options' => [
            //     PDO::ATTR_PERSISTENT => true, // Kết nối liên tục
            // ],
        ],
        'elasticsearch' => [
            'bulk' => [
                'max_batch_size_mb' => env('ELASTICSEARCH_MAX_BATCH_SIZE_MB', 1),
            ],
            'driver' => 'elasticsearch',
            'hosts' => [
                'host' => env('ELASTICSEARCH_HOST', 'localhost'),
                'port' => env('ELASTICSEARCH_PORT', 9200),
                'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
                'user' => env('ELASTICSEARCH_USER', ''),
                'pass' => env('ELASTICSEARCH_PASS', ''),
                'ca' => env('ELASTICSEARCH_CA', '/etc/ssl/certs/ca-certificates.crt'),
            ],
            'client' => [
                'persistent' => true,  // Sử dụng persistent connections (Giữ kết nối liên tục)
            ]
        ],
        'momo' => [
            'momo_partner_code' => env('MOMO_PARTNER_CODE', ''),
            'momo_access_key' => env('MOMO_ACCESS_KEY', ''),
            'momo_secret_key' => env('MOMO_SECRET_KEY', ''),
            'momo_endpoint' => env('MOMO_ENDPOINT', ''),
            'momo_endpoint_create_payment' => env('MOMO_ENDPOINT', '') . '/v2/gateway/api/create',
            'momo_endpoint_check_transaction' => env('MOMO_ENDPOINT', '') . '/v2/gateway/api/query',
            'momo_endpoint_refund_payment' => env('MOMO_ENDPOINT', '') . '/v2/gateway/api/refund',

            'momo_return_url_thanh_toan' => env('MOMO_RETURN_URL_THANH_TOAN', ''),
            'momo_notify_url_thanh_toan' => env('MOMO_NOTIFY_URL_THANH_TOAN', ''),

            'momo_return_url_tam_ung' => env('MOMO_RETURN_URL_TAM_UNG', ''),
            'momo_notify_url_tam_ung' => env('MOMO_NOTIFY_URL_TAM_UNG', ''),
        ],
        'vietinbank' => [
            'vietinbank_secret_key' => env('VIETINBANK_SECRET_KEY', ''),
            'vietinbank_merchant_id' => env('VIETINBANK_MERCHANT_ID', ''),
            'vietinbank_client_id' => env('VIETINBANK_CLIENT_ID', ''),
            'vietinbank_api_url' => env('VIETINBANK_API_URL', ''),
            'vietinbank_api_url_inq_detail_trans' => env('VIETINBANK_API_URL_INQ_DETAIL_TRANS', ''),
            'public_key_vietinbank_confirm_path' => env('PUBLIC_KEY_VIETINBANK_CONFIRM_PATH', ''),
            'public_key_vietinbank_inq_detail_path' => env('PUBLIC_KEY_VIETINBANK_INQ_DETAIL_PATH', ''),
            'private_key_bvxa_path' => env('PRIVATE_KEY_BVXA_PATH', ''),
            'merchant_code' => env('MERCHANT_CODE', ''),
            'merchant_cc' => env('MERCHANT_CC', ''),
            'merchant_name' => env('MERCHANT_NAME', ''),
            'terminal_id' => env('TERMINAL_ID', ''),
            'store_id' => env('STORE_ID', ''),
            'provider_id' => env('PROVIDER_ID', 'BVXUYENA'),
            'exp_time_qr_vtb' => (int) env('EXP_TIME_QR_VTB', 10),

        ],
        'twilio' => [
            'sid' => env('TWILIO_SID', ''),
            'auth_token' => env('TWILIO_AUTH_TOKEN', ''),
            'phone_number' => env('TWILIO_PHONE_NUMBER', ''),
        ],
        'e_sms' => [
            'api_key' => env('E_SMS_API_KEY', ''),
            'secret_key' => env('E_SMS_SECRET_KEY', ''),
            'brand_name' => env('E_SMS_BRAND_NAME', ''),
        ],
        'speed_sms' => [
            'api_key' => env('SPEED_SMS_API_KEY', ''),
            'sender' => env('SPEED_SMS_SENDER', ""),
        ],
        'otp' => [
            'otp_max_requests_per_day' => intval(env('OTP_MAX_REQUESTS_PER_DAY', 20)),
            'otp_max_requests_verify_per_otp' => intval(env('OTP_MAX_REQUESTS_VERIFY_PER_OTP', 5)),
            'otp_ttl' => intval(env('OTP_TTL', 5)),
        ],
        'telegram' => [
            'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
            'chanel_log_id' => env('TELEGRAM_CHANNEL_ID', ''),
        ],
        'zalo' => [
            'zalo_app_id' => env('ZALO_APP_ID', ''),
            'zalo_app_secret_key' => env('ZALO_APP_SECRET_KEY', ''),
        ],
        'libre_office' => [
            'libre_office_path' => env('LIBRE_OFFICE_PATH', ''),
        ],
        'fss' => [
            'fss_url' => env('URL_FSS', ''),
        ],
        'acs' => [
            'acs_url' => env('URL_ACS', 'https://apigw-vlg.xuyenahospital.com.vn/dev/vss/acs'),
        ],
        'mos' => [
            'mos_url' => env('URL_MOS', 'http://192.168.101.10:1408'),
        ],
        'guest' => [
            'dang_ky_kham' => [
                'request_room_code_mac_dinh' => env('REQUEST_ROOM_CODE_MAC_DINH_DANG_KY_KHAM', 'KB_DT'),
                'tai_khoan_mac_dinh' => [
                    'username' => env('USERNAME_TAI_KHOAN_MAC_DINH_DANG_KY_KHAM', 'truyenlm'),
                    'password' => env('PASSWORD_TAI_KHOAN_MAC_DINH_DANG_KY_KHAM', 'truyenlm'),
                ],
            ],
        ],
        'thong_bao' => [
            'danh_sach_email_nhan_thong_bao_loi' =>  explode(',', env('DANH_SACH_EMAIL_NHAN_THONG_BAO_LOI','tranlenguyenkhanh20102001@gmail.com'))
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'xa_migrations_laravel',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'name' => env('APP_NAME', 'laravel'),  // Đặt tên cho kết nối
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            // 'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
            'prefix' => "",
            'read_timeout' => 2.5,
        ],
        // 'default' => [
        //     'url' => env('REDIS_URL'),
        //     'host' => env('REDIS_HOST', '127.0.0.1'),
        //     'password' => env('REDIS_PASSWORD', null),
        //     'username' => env('REDIS_USERNAME', null),
        //     'port' => env('REDIS_PORT', '6379'),
        //     'database' => env('REDIS_DB', '0'),
        // ],

        // 'cache' => [
        //     'url' => env('REDIS_URL'),
        //     'host' => env('REDIS_HOST', '127.0.0.1'),
        //     'password' => env('REDIS_PASSWORD', null),
        //     'username' => env('REDIS_USERNAME', null),
        //     'port' => env('REDIS_PORT', '6379'),
        //     'database' => env('REDIS_CACHE_DB', '1'),
        // ],

        // 'options' => [
        //     'name' => env('APP_NAME', 'laravel'),
        //     'cluster' => false,
        //     'replication' => 'sentinel',
        //     'service' => env('REDIS_SENTINEL_SERVICE', 'my-master-sentine'),
        //     'prefix' => "",
        // ],

        // 'default' => [
        //     // 'url' => env('REDIS_URL'),
        //     'host' => env('REDIS_SENTINEL_HOST', 'my-redis-sentinel'),
        //     'password' => env('REDIS_PASSWORD', null),
        //     'port' => env('REDIS_SENTINEL_PORT', 26379),
        //     'database' => 0,
        // ],

        // 'cache' => [
        //     // 'url' => env('REDIS_URL'),
        //     'host' => env('REDIS_SENTINEL_HOST', 'my-redis-sentinel'),
        //     'password' => env('REDIS_PASSWORD', null),
        //     'port' => env('REDIS_SENTINEL_PORT', 26379),
        //     'database' => 1,
        // ],

        'clusters' => [
            'default' => [
                [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'password' => env('REDIS_PASSWORD', null),
                    'username' => env('REDIS_USERNAME', null),
                    'port' => env('REDIS_PORT', '6385'),
                    'database' => env('REDIS_DB', '0'),
                ],
            ],
            'cache' => [
                [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'password' => env('REDIS_PASSWORD', null),
                    'username' => env('REDIS_USERNAME', null),
                    'port' => env('REDIS_PORT', '6385'),
                    'database' => env('REDIS_CACHE_DB', '1'),
                ],
            ],
        ],
    ],

];
