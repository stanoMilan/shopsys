# [Upgrade from v7.1.0 to Unreleased]

This guide contains instructions to upgrade from version v7.1.0 to Unreleased.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]
### Application
- Add visibility to all constants, or skip `Shopsys\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff` sniff ([#904](https://github.com/shopsys/shopsys/pull/904))

## [shopsys/coding-standards]
- We require to have visibility specified for constants ([#904](https://github.com/shopsys/shopsys/pull/904))
  You can skip `Shopsys\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff`, if it is not suits you

[Upgrade from v7.1.0 to Unreleased]: https://github.com/shopsys/shopsys/compare/v7.1.0...HEAD
[shopsys/framework]: https://github.com/shopsys/framework
[shopsys/coding-standards]: https://github.com/shopsys/coding-standards
