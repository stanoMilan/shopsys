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

### Application
- Add visibility to all constants, or skip `Shopsys\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff` sniff ([#904](https://github.com/shopsys/shopsys/pull/904))

## [shopsys/coding-standards]
- We require to have visibility specified for constants ([#904](https://github.com/shopsys/shopsys/pull/904))
  You can skip `Shopsys\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff`, if it is not suits you
- Skip `Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff` sniff in your `easy-coding-standard.yml`, so you won't be forced to use `static` on protected constants ([#904](https://github.com/shopsys/shopsys/pull/904))

[Upgrade from v7.1.0 to Unreleased]: https://github.com/shopsys/shopsys/compare/v7.1.0...HEAD
[shopsys/framework]: https://github.com/shopsys/framework
[shopsys/coding-standards]: https://github.com/shopsys/coding-standards
