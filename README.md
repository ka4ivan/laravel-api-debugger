# A debugging tool for Laravel APIs

[![License](https://img.shields.io/packagist/l/ka4ivan/laravel-api-debugger.svg?style=for-the-badge)](https://packagist.org/packages/ka4ivan/laravel-api-debugger)
[![Build Status](https://img.shields.io/github/stars/ka4ivan/laravel-api-debugger.svg?style=for-the-badge)](https://github.com/ka4ivan/laravel-api-debugger)
[![Latest Stable Version](https://img.shields.io/packagist/v/ka4ivan/laravel-api-debugger.svg?style=for-the-badge)](https://packagist.org/packages/ka4ivan/laravel-api-debugger)
[![Total Downloads](https://img.shields.io/packagist/dt/ka4ivan/laravel-api-debugger.svg?style=for-the-badge)](https://packagist.org/packages/ka4ivan/laravel-api-debugger)
[![Quality Score](https://img.shields.io/scrutinizer/g/ka4ivan/laravel-api-debugger.svg?style=for-the-badge)](https://scrutinizer-ci.com/g/ka4ivan/laravel-api-debugger/?branch=main)

## Installation

1) Require this package with composer
```shell
composer require ka4ivan/laravel-api-debugger --dev
```

2) Debug works if the `APP_DEBUG` = `true`

### How it works
- The debugger will start logging SQL queries and request data for every API request.
- It will only activate if `APP_DEBUG=true` in your `.env` file.
- The results, including query count, execution time, and detailed data, will be attached to the API response for debugging purposes.

### Example
When you send an API request and `APP_DEBUG=true`, you'll receive the following debug data in the response:

```json
{
    "data": {
        "..."
    },
    "debugger": {
        "queries": {
            "count": 7,
            "time": 5.57,
            "data": [
                {
                    "query": "select * from `domains` where `is_active` = ? limit 1",
                    "bindings": [
                        true
                    ],
                    "time": 10.09
                },
                {
                    "query": "select * from `products` limit 1",
                    "bindings": [],
                    "time": 0.75
                },
                {
                    "query": "select * from `menu_items` where (`domain_id` = ? or `domain_id` is null) order by `weight` asc",
                    "bindings": [
                        "8e591823-4f64-4e90-b564-e0c090696d6e"
                    ],
                    "time": 0.82
                },
                {
                    "query": "select * from `pages` where `slug` = ? and (`domain_id` = ? or `domain_id` is null) and `status` = ? order by `weight` asc, `created_at` asc limit 1",
                    "bindings": [
                        "contacts",
                        "8e591823-4f64-4e90-b564-e0c090696d6e",
                        "published"
                    ],
                    "time": 0.76
                },
                {
                    "query": "select * from `comparisons` where `guest_id` = ?",
                    "bindings": [
                        "62afacc1-37b2-464d-8028-818745e56de5"
                    ],
                    "time": 0.61
                },
                {
                    "query": "select * from `comparisons` where `guest_id` = ?",
                    "bindings": [
                        "62afacc1-37b2-464d-8028-818745e56de5"
                    ],
                    "time": 0.61
                },
                {
                    "query": "select * from `comparisons` where `guest_id` = ?",
                    "bindings": [
                        "62afacc1-37b2-464d-8028-818745e56de5"
                    ],
                    "time": 0.63
                },
                {
                    "query": "select * from `seos` where `path` = ? limit 1",
                    "bindings": [
                        ""
                    ],
                    "time": 0.93
                }
            ],
            "long_queries": [
                {
                    "query": "select * from `domains` where `is_active` = ? limit 1",
                    "bindings": [
                        true
                    ],
                    "time": 10.09
                }
            ],
            "n_plus_one": [
                {
                    "query": "select * from `comparisons` where `guest_id` = ?",
                    "count": 3
                }
            ]
        },
        "request": {
            "body": [],
            "headers": {
                "connection": [
                    "keep-alive"
                ],
                "accept-encoding": [
                    "gzip, deflate, br"
                ],
                "host": [
                    "dev-packages.test"
                ],
                "postman-token": [
                    "12366123-6abf-4511-9b91-7285f5123123"
                ],
                "user-agent": [
                    "PostmanRuntime/7.37.3"
                ],
                "authorization": [
                    "Bearer 8|vxwhATRrOfinZiR4zvhoTpckGqYJTPnmj9tkOfdMb5d9da28"
                ],
                "accept": [
                    "application/json"
                ]
            }
        }
    }
}
```

### License

This package is licensed under the [MIT License](https://opensource.org/licenses/MIT). You can freely use, modify, and distribute this package, provided that you include a copy of the license in any redistributed software.
