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

These settings can also be set per site, but this can only be done through the configuration file.

```php
<?php

return [
    'apiKey' => [
        'siteHandleA' => 'test_key_a',
        'siteHandleB' => 'test_key_b',
    ]   
];
```