# Caching free.currencyconverterapi.com
Periodically fetch from https://free.currencyconverterapi.com and cache the result without exceeding "Requests per Hour" limit. Useful for application that don't need realtime conversion rate.

# Setup
1. Make sure you have your own API key from https://free.currencyconverterapi.com
1. Put your API key into `private/apikey.txt` file
1. Adjust your `baseCurrency` in `private/config.php`
1. Adjust your `numberOfRequests` in `private/config.php`. This will control how much request to be sent every time `private/create_cache.php` run. For example we set it to 7 requests, if I set a cron job to run `private/create_cache.php` every 5 minutes, there will be 84 request sent in 1 hour (60 minutes / 5 minutes * 7 requests) which is still below the 100 requests per hour limit. Since each request will ask for 2 pairs of currency, 192 pairs will be updated every hour.
1. Run `update_currencies.php` to get all currencies offered by https://free.currencyconverterapi.com. You can remove currencies that you don't need from `currencies.json`.

# Usage
Run `private/create_cache.php` periodically (use cron job or something simillar). In my case, I run it once every 5 minutes. `cache_XXX.json` (where XXX is currency code) will serve the cached result.
