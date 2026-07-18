// Release config for webgrip/telemetry-service.
//
// Forge-agnostic: ONE config, correct on both Forgejo (leading) and GitHub (mirror). The publish
// plugin and the version commit-back are gated on GITEA_ACTIONS — an intrinsic Forgejo-runner env
// var (=true on Forgejo, unset on GitHub; GITHUB_ACTIONS is set on BOTH so it can't discriminate).
// See the `forgejo-port-workflows` skill in the homelab-cluster repo.
const onForgejo = !!process.env.GITEA_ACTIONS;

const noteKeywords = ['BREAKING CHANGE', 'BREAKING CHANGES', 'BREAKING'];

const commitAnalyzer = [
    '@semantic-release/commit-analyzer',
    {
        preset: 'conventionalcommits',
        releaseRules: [
            { breaking: true, release: 'major' },
            { revert: true, release: 'patch' },
            { type: 'feat', release: 'minor' },
            { type: 'fix', release: 'patch' },
            { type: 'perf', release: 'patch' },
            { type: 'refactor', release: 'patch' },
            { type: 'chore', scope: 'deps', release: 'patch' },
            { type: 'chore', release: false },
            { type: 'ci', release: false },
            { type: 'docs', release: false },
            { type: 'style', release: false },
            { type: 'test', release: false },
            { type: 'build', release: false },
        ],
        parserOpts: { noteKeywords },
    },
];

const releaseNotes = [
    '@semantic-release/release-notes-generator',
    {
        preset: 'conventionalcommits',
        presetConfig: {
            types: [
                { type: 'feat', section: 'Added' },
                { type: 'fix', section: 'Fixed' },
                { type: 'perf', section: 'Performance' },
                { type: 'refactor', section: 'Changed' },
                { type: 'docs', section: 'Docs', hidden: false },
                { type: 'test', section: 'Tests', hidden: false },
                { type: 'chore', section: 'Internal', hidden: true },
            ],
        },
        parserOpts: { noteKeywords },
    },
];

const changelog = ['@semantic-release/changelog', { changelogFile: 'CHANGELOG.md' }];

const exec = [
    '@semantic-release/exec',
    { successCmd: 'echo "version=${nextRelease.version}" >> $GITHUB_OUTPUT' },
];

// Version/changelog commit-back ONLY on Forgejo (the sole release authority). The GitHub mirror must
// never re-version. `[skip ci]` is honoured by both forges, so the Forgejo→GitHub mirror push of this
// commit re-triggers nothing.
const commitBack = onForgejo
    ? [
          [
              '@semantic-release/git',
              {
                  assets: ['CHANGELOG.md', 'composer.json'],
                  message: 'chore(release): ${nextRelease.version} [skip ci]\n\n${nextRelease.notes}',
              },
          ],
      ]
    : [];

// Forgejo: @saithodev/semantic-release-gitea reads GITEA_URL/GITEA_TOKEN set by the reusable action.
const giteaPublish = ['@saithodev/semantic-release-gitea', {}];
// GitHub: unchanged from the original config (kept correct in case GitHub ever cuts a release).
const githubPublish = ['@semantic-release/github', {}];

module.exports = {
    branches: ['main'],
    tagFormat: '${version}',
    plugins: [commitAnalyzer, releaseNotes, changelog, exec, ...commitBack, onForgejo ? giteaPublish : githubPublish],
};
