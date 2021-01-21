# [Upgrade from v9.1.0 to v9.1.1-dev](https://github.com/shopsys/shopsys/compare/v9.1.0...9.1)

This guide contains instructions to upgrade from version v9.1.0 to v9.1.1-dev.

**Before you start, don't forget to take a look at [general instructions](https://github.com/shopsys/shopsys/blob/master/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

- sanitize non-printable search text ([#2174](https://github.com/shopsys/shopsys/pull/2174))
    - see #project-base-diff to update your project
  
- trim search text from spaces ([#2187](https://github.com/shopsys/shopsys/pull/2187))
    - see #project-base-diff to update your project

- update annotations for EntityExtensionTest.php ([#2197](https://github.com/shopsys/shopsys/pull/2197))
    - see #project-base-diff to update your project

- replace `sensiolabs/security-checker` with `enlightn/security-checker` ([#2211](https://github.com/shopsys/shopsys/pull/2211))
    - you can run `composer remove sensiolabs/security-checker; composer require enlightn/security-checker ^1.3` to avoid manual editing of composer files
    - security checks are now executed automatically only after composer update, you should add the check into your CI pipeline
    - you can run `composer security-check` or `php phing security-check` to perform security checks
    - see #project-base-diff to update your project

- update elfinder installer to be compatible with `helios-ag/fm-elfinder-bundle` v10.1 ([#2217](https://github.com/shopsys/shopsys/pull/2217))
    - if you have updated the `assets` phing target, you should remove `shopsys:elfinder:post-install` call
      and add `--docroot` option for `elfinder:install` command. See PR for inspiration