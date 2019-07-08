---
title: Plugin settings - Mollie payments
prev: false
next: false
---

# Plugin settings

In the plugin settings you define which Mollie api key you want to use. You can also move this setting to a configuration file by creating ``config/mollie-payments.php`` and adding the following content:

```php
<?php

return [
    'apiKey' => 'test_api_key'
];
```