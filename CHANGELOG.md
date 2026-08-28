## [1.1.0](https://forgejo.webgrip.dev/webgrip/telemetry-service/compare/v1.0.5...v1.1.0) (2026-08-28)

### Added

* **docs:** flip strict link validation on — links verified clean (estate item [#17](https://forgejo.webgrip.dev/webgrip/telemetry-service/issues/17)) ([04d3e99](https://forgejo.webgrip.dev/webgrip/telemetry-service/commit/04d3e99a930f59801eee1446cb97b0df470fee4d))
* **docs:** publish to docs.webgrip.dev/telemetry-service/ — estate rollout (ADR-0052) ([239741f](https://forgejo.webgrip.dev/webgrip/telemetry-service/commit/239741ff3bcdf82c6dd7b9a301b8bec58da07687))
* **docs:** publish to own docs-telemetry-service bucket (estate [#2](https://forgejo.webgrip.dev/webgrip/telemetry-service/issues/2)) ([fa502c6](https://forgejo.webgrip.dev/webgrip/telemetry-service/commit/fa502c655a00cf607f1385dd38b017df3a943a01))

### CI

* adopt @webgrip/semantic-release-config ([3849e4b](https://forgejo.webgrip.dev/webgrip/telemetry-service/commit/3849e4b72753a6e192ca4b90d3cedb8826f962bf))
* **release:** run the release job in the toolchain image; composite to v2.0.0 ([3a49f45](https://forgejo.webgrip.dev/webgrip/telemetry-service/commit/3a49f451a68b8653326c171f8c33b37602bcffa6))
* retrigger docs (git-ensure fix in update_techdocs) ([0e98590](https://forgejo.webgrip.dev/webgrip/telemetry-service/commit/0e9859090758d8a4aa46e37983b4ab5371eb9479))
* retrigger release train (re-run scheduler wedge; composite now has yq + prune fix) ([7949bf9](https://forgejo.webgrip.dev/webgrip/telemetry-service/commit/7949bf9d1689e3e0af6bebcefaf6e4f332fa3af7))

### Internal

* **docs:** bump reusables — backup-dir trash-net + pagefind estate-search index (estate items 1+4) ([a5977d0](https://forgejo.webgrip.dev/webgrip/telemetry-service/commit/a5977d06d576ba48412606f0bcddb1e81f30c254))

## [1.0.5](http://forgejo-http.forgejo.svc.cluster.local:3000/webgrip/telemetry-service/compare/1.0.4...1.0.5) (2026-07-18)

## [1.0.4](https://github.com/webgrip/telemetry-service/compare/1.0.3...1.0.4) (2025-05-27)


### Bug Fixes

* Fixed test ([5243511](https://github.com/webgrip/telemetry-service/commit/52435115d8ab0931504217b3bfb11885803f0704))
* Log a warning instead of an error when the collector is not configured ([fb542f7](https://github.com/webgrip/telemetry-service/commit/fb542f7dad741e2408eb0b499798f6fabe827f8c))

## [1.0.3](https://github.com/webgrip/telemetry-service/compare/1.0.2...1.0.3) (2025-04-16)


### Bug Fixes

* **backstage:** Removed duplicated file [skip-ci] ([0020630](https://github.com/webgrip/telemetry-service/commit/002063052f2276f45f9e959e4a4357a703b47cfa))
* No longer log a warning when the otel collector is not found ([a6616ed](https://github.com/webgrip/telemetry-service/commit/a6616ed9861a6f147dc43fbdb801933226158a4c))

## [1.0.2](https://github.com/webgrip/telemetry-service/compare/v1.0.1...1.0.2) (2025-03-03)


### Bug Fixes

* **workflows:** Added version to workflow output and forego the "v" prefix on the released tags ([2b66964](https://github.com/webgrip/telemetry-service/commit/2b66964102dff7e0e166f95b1877b0fc89cedf86))

## [1.0.1](https://github.com/webgrip/telemetry-service/compare/v1.0.0...v1.0.1) (2025-02-26)


### Bug Fixes

* Removed version from composer.json due to warning issued here https://getcomposer.org/doc/04-schema.md#version ([5826e94](https://github.com/webgrip/telemetry-service/commit/5826e94c3903b9098c4478f8560363766a0cd861))

# 1.0.0 (2025-02-26)


### Bug Fixes

* @semantic-release/exec instead of semantic-release/exec ([4f21070](https://github.com/webgrip/telemetry-service/commit/4f210709ebee7dcd5c8c95373a9fabbc8d561ace))
* Added tests/** to on source change ([f2a0193](https://github.com/webgrip/telemetry-service/commit/f2a0193df829b8d2eb7251251063ca0718e68e44))
* Changelog ([b197117](https://github.com/webgrip/telemetry-service/commit/b1971179d23b55a0a07b9623e99ba326230e2d4c))
* composer update ([5061f22](https://github.com/webgrip/telemetry-service/commit/5061f2200b394b8613052ee6d753a2e0715d8513))
* don't escape releaserc $composer ([9e5d903](https://github.com/webgrip/telemetry-service/commit/9e5d9033151002d8db378068a528054f3cb40f34))
* Don't run this on a container ([16cddd9](https://github.com/webgrip/telemetry-service/commit/16cddd9c3521d0f960cad0d3e170a1b16180ed97))
* Ephemeral install of semantic release node dependencies ([38e1266](https://github.com/webgrip/telemetry-service/commit/38e1266622465597519d839940aecac222bf9bcb))
* Fixed Integration Tests ([2a2d3fe](https://github.com/webgrip/telemetry-service/commit/2a2d3fe10ddf3b4716e6924568b01817fc151586))
* Fixing phpcs issues and added files that should trigger releases ([f9df5b4](https://github.com/webgrip/telemetry-service/commit/f9df5b469c0cdc2fc998e20464ca814902aea1cf))
* version ([2c09632](https://github.com/webgrip/telemetry-service/commit/2c09632ccdd81f622666f101e5801a35da519374))


### Features

* update workflows and autobump version + release ([32ec60c](https://github.com/webgrip/telemetry-service/commit/32ec60ce49f0a6f4bfa7b70be9b9ef37e6081702))
