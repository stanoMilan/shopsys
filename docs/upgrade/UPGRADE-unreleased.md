# [Upgrade from v7.1.0 to Unreleased]

This guide contains instructions to upgrade from version v7.1.0 to Unreleased.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]
### Configuration
 - *(low priority)* use standard format for redis prefixes ([#928](https://github.com/shopsys/shopsys/pull/928))
    - change prefixes in `app/config/packages/snc_redis.yml` and `app/config/packages/test/snc_redis.yml`. Please find inspiration in [#928](https://github.com/shopsys/shopsys/pull/928/files)
    - once you finish this change, you still should deal with older redis cache keys that don't use new prefixes. Such keys are not removed even by `clean-redis-old`, please find and remove them manually (via console or UI)

    **Be careful, this upgrade will remove sessions**
 - nginx has now same limit for file size as is set in php.ini ([#947](https://github.com/shopsys/shopsys/pull/947))
    - update your nginx.conf file like this:
        ```diff
        server {
            listen 8080;
            access_log /var/log/nginx/shopsys-framework.access.log;
            root /var/www/html/web;
            server_tokens off;
        +   client_max_body_size 32M;
        ```

[Upgrade from v7.1.0 to Unreleased]: https://github.com/shopsys/shopsys/compare/v7.1.0...HEAD
