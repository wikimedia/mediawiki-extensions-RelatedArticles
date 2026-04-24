# RelatedArticles Contributing Guide

## Welcome

Welcome to the RelatedArticles Contributing Guide, and thank you for your
interest.

This guide covers the different ways you can contribute. We are always open to
contributions in the following forms:

- Bug Reports and bug fixes
- Documentation improvements

At this time, we do not accept the following contributions:

- Feature Requests

### Overview

RelatedArticles is a MediaWiki extension that shows related pages as a list of
articles and descriptions at the bottom of a page on desktop and mobile.

Reader Growth currently lists RelatedArticles in the `Undecided` [maintenance
tier][maintenance-levels]. The [extension page][mw-extension] also notes that support is `Pending`,
with the Reader Growth team acting as maintainer of last resort. In practice:

- Reader Growth attention is primarily limited to user-blocking and
  security/privacy fixes.
- Review bandwidth is limited, so not every patch will be picked up or reviewed
  quickly.

For more information, see the [README][readme] and [Architecture Decision
Records][adr].

### Community engagement

Refer to the following channels to connect with fellow contributors or to stay
up-to-date with news about RelatedArticles:

- Connect with project maintainers via the
  [Reader Growth page][reader-growth].
- Participate in discussions in [Village Pump][village-pump].
- Stay updated on the latest news and changes to the project by following
  [MediaWiki's version lifecycle page][version-lifecycle].

## Contributing

Guidelines for specific ways of contributing to this project can be found
below.

### Code of Conduct

Before contributing, read our [Code of Conduct][coc] to learn more about our
community guidelines and expectations.

### Bug Reports

We use Phabricator to track tasks and bug reports. To report a bug:

1. **Search for existing issues**: Check if the issue has already been reported
   in [Phabricator][phabricator-board].
2. **Create a new issue**: If the issue doesn't exist, create a new issue on
   Phabricator through the Create Task dropdown and tag it with
   `#RelatedArticles`. If the change needs Reader Growth input, include the
   Reader Growth team as well.
3. **Provide details**: Include:
   - A clear description of the issue
   - Steps to reproduce
   - Expected vs. actual behavior
   - Your environment (MediaWiki version, browser, etc.)
   - Screenshots or error messages if applicable

### Proposals and feature requests

At this time, RelatedArticles does not accept new feature requests.
Scope-expanding product work should not start as an implementation patch.

If you still want to discuss a proposal (for example, a change that would need
maintainer agreement before any coding begins):

1. Create an issue on [Phabricator][phabricator-board] under `#RelatedArticles`.
2. Describe your idea clearly, including:
   - The problem you're trying to solve
   - Why the change is needed
   - The expected user and maintenance impact
3. Wait for feedback from maintainers before starting implementation.

Please discuss first for items such as:

- New reader-facing features or major UX changes
- New configuration options or integrations that broaden the extension's
  responsibilities
- Changes that assume new Wikimedia product commitments or rollout plans
- New analytics, instrumentation, or external service dependencies
- Architectural changes that increase long-term maintenance cost

### Code contribution

#### Before you start

Before you start contributing, ensure you have the following:

- A [Wikimedia developer account][wmf-dev-account] with Gerrit access
- A local MediaWiki development environment (see [Local development
  quickstart][local-dev])
- Node.js and npm
- PHP 8.1+ and Composer
- Basic familiarity with MediaWiki extension development, JavaScript, and PHP

Useful areas of the codebase:

- PHP integration and placement logic live in `includes/Hooks.php` and
  `extension.json`.
- The Read More UI, bootstrap logic, and related-pages gateway live in
  `resources/ext.relatedArticles.readMore/`.
- Shared styling and skin-specific styling live in
  `resources/ext.relatedArticles.styles.less` and `skinStyles/`.
- Automated coverage lives in `tests/jest/`, `tests/qunit/`, and
  `tests/selenium/`.
- Architecture decisions belong in `doc/adr/`.

For more information, see [How to become a MediaWiki hacker][mw-hacker].

#### Environment setup

For installation and usage instructions, see the
[RelatedArticles extension page][mw-extension]. Developers and code
contributors should install the extension from Git:

```sh
cd extensions/
git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/RelatedArticles
```

Then load the extension in `LocalSettings.php`:

```php
wfLoadExtension( 'RelatedArticles' );
```

For local development and configuration, refer to the extension page.


After cloning, install front-end dependencies with `npm install` and PHP
development tools with `composer install` as needed.

#### Troubleshooting

For general MediaWiki development issues, consult the [Installing MediaWiki
documentation][installing-mw].

If related pages are not showing, confirm that:

- The page is in the Main namespace and being viewed (not edited)
- The page is not a disambiguation or diff page
- The current skin is listed in `$wgRelatedArticlesFooterAllowedSkins`
- API requests for related pages are succeeding (check the browser network
  tab; see `$wgRelatedArticlesUseCirrusSearchApiUrl` if needed)

#### Best practices

Our project uses the following resources for best practices:

- **PHP**: [MediaWiki's PHP coding conventions][php-conventions]
- **JavaScript**: [MediaWiki's JavaScript coding
  conventions][js-conventions]
- **CSS/LESS**: [MediaWiki's CSS coding conventions][css-conventions]

#### Contribution workflow

##### Fork and clone repositories

RelatedArticles uses [Gerrit Code Review][gerrit] for code review. To
contribute:

1. **Set up Gerrit**: Follow the [Gerrit/Tutorial][gerrit-tutorial] to set up
   your Gerrit account and SSH keys.

2. **Clone the repository**:
```sh
   git clone "ssh://USERNAME@gerrit.wikimedia.org:29418/mediawiki/extensions/RelatedArticles"
```

3. **Install the commit-msg hook**:
```sh
   cd RelatedArticles
   git review -s
```

#### Issue management

Issues are managed through Phabricator and reviewed as patches in Gerrit. When
creating or updating tasks:

- Use descriptive titles
- Tag tasks with `#RelatedArticles` and other appropriate projects
- Reference related tasks or patches when applicable
- Keep tasks up to date with status changes

#### Commit messages

Follow [MediaWiki's commit message guidelines][commit-guidelines]:

- Use a short subject line (80 characters or less)
- Follow with a blank line and a detailed description
- Reference related issues or patches
- Use the present tense ("Add feature" not "Added feature")

Example:
```
Fix related pages placement on Vector

Ensure the Read More footer is only attached when the page is eligible
and the active skin is configured in RelatedArticlesFooterAllowedSkins.

Bug: T123456
```

#### Submitting patches

In MediaWiki, we use "change requests" (patches) in Gerrit instead of pull
requests. To submit a change:

1. **Make your changes** following the best practices and coding conventions.

2. **Run tests and linting**:
```sh
   # Back end
   composer test

   # Front end
   npm test
```

   For focused front-end work, `npm run test:unit` is also useful.

3. **Fix any issues** found by the linters or tests.

4. **Commit your changes** following the commit message guidelines.

5. **Push to Gerrit**:
```sh
   git review
```

6. **Wait for code review**: A maintainer will review your change. Address any
   feedback by amending your commit and pushing again.

For more information, see [Gerrit/Tutorial][gerrit-tutorial]. Changes for this
project appear under the
[RelatedArticles Gerrit project][gerrit-project].

#### Releases

RelatedArticles follows MediaWiki's "continuous integration" development
model, where software changes are pushed live to wikis regularly. [See
deployment schedule][deployment].

#### Text Formats

When editing and creating documents:

- **Markdown**: Use Markdown (`.md` files) for documentation
- **PHP**: Use PHP for back-end code, following MediaWiki's PHP coding
  conventions
- **JavaScript**: Use JavaScript/ES6+ for front-end code, following MediaWiki's
  JavaScript coding conventions
- **LESS**: Use LESS for stylesheets, following MediaWiki's CSS coding
  conventions
- **JSON**: Use JSON for configuration files (e.g., `extension.json`,
  `package.json`)

[readme]: README
[adr]: doc/adr/
[mw-extension]: https://www.mediawiki.org/wiki/Extension:RelatedArticles
[reader-growth]: https://www.mediawiki.org/wiki/Readers/Reader_Growth
[maintenance-levels]: https://www.mediawiki.org/wiki/Readers/Reader_Growth/Maintenance_Levels_and_Responsibilities
[village-pump]: https://en.wikipedia.org/wiki/Wikipedia:Village_pump
[version-lifecycle]: https://www.mediawiki.org/wiki/Version_lifecycle
[coc]: CODE_OF_CONDUCT.md
[phabricator-board]: https://phabricator.wikimedia.org/project/board/688/
[wmf-dev-account]: https://www.mediawiki.org/wiki/How_to_become_a_MediaWiki_hacker#Get_a_developer_account
[local-dev]: https://www.mediawiki.org/wiki/Local_development_quickstart
[mw-hacker]: https://www.mediawiki.org/wiki/How_to_become_a_MediaWiki_hacker
[installing-mw]: https://www.mediawiki.org/wiki/Manual:Installing_MediaWiki
[php-conventions]: https://www.mediawiki.org/wiki/Manual:Coding_conventions/PHP
[js-conventions]: https://www.mediawiki.org/wiki/Manual:Coding_conventions/JavaScript
[css-conventions]: https://www.mediawiki.org/wiki/Manual:Coding_conventions/CSS
[gerrit]: https://www.mediawiki.org/wiki/Gerrit
[gerrit-tutorial]: https://www.mediawiki.org/wiki/Gerrit/Tutorial
[gerrit-project]: https://gerrit.wikimedia.org/r/q/project:mediawiki/extensions/RelatedArticles
[commit-guidelines]: https://www.mediawiki.org/wiki/Gerrit/Commit_message_guidelines
[deployment]: https://wikitech.wikimedia.org/wiki/Deployments
