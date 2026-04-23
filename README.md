# mautic-plugin-onesignal-web16

Plugin for [Mautic](https://www.mautic.org/) 7.x that **avoids editing core** for OneSignal web push (Web SDK v16 subscription IDs) and for treating OneSignal JSON errors in HTTP-200 responses as send failures in campaigns.

## What it does

1. For **web** (non-mobile) push, sends `include_subscription_ids` instead of `include_player_ids` in the OneSignal REST request (aligned with subscription IDs from Web SDK v16 / Mautic `push_ids`).
2. For **mobile**, keeps `include_player_ids`.
3. If the OneSignal API returns **HTTP 200** with a JSON `errors` payload or with `id` empty or null, the response is coerced to **HTTP 422** so the default `CampaignSubscriber` does not record a false “delivered” outcome.

## Requirements

- PHP >= 8.2
- Mautic 7.x (`mautic/core-lib` ^7.0)

## Install (Composer, recommended for Packagist)

From the Mautic project root (Composer-based install):

```bash
composer require mtc/mautic-plugin-onesignal-web16
php bin/console mautic:plugins:reload
php bin/console cache:clear
```

In the Mautic UI: **Settings → Plugins**, install/enable **OnesignalWeb16** if necessary.




## License

GPL-3.0-or-later (same family as Mautic core).
