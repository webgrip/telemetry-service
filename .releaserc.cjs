'use strict';

// PHP service: tag-only releases (manifest 'composer' is the documented no-op — Packagist-style
// consumers read tags; the old config committed composer.json back but nothing ever modified it).
// NB tag line moves from bare `1.0.5` to `v1.0.6` — a v1.0.5 baseline tag was seeded at the last
// bare release so the sequence continues instead of restarting.
const { makeConfig } = require('@webgrip/semantic-release-config');

module.exports = makeConfig({ manifest: 'composer' });

// re-cut marker (2026-07-26): fresh run after the wedged re-run — the prune fix + yq
// install landed in the composite; this line re-enters the release train (infra precedent).
