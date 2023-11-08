# 1. Do not server side render

Date: 2023-11-08

## Status

To discuss.

## Context

From a product perspective we do not wish to display related articles until the user has scrolled to the bottom of the page after finishing the reading of an article (which is seen as an intention of "wanting to read more" [1]) and its click-through rates don't merit a feature that needs to be available to all users [2]. I think if we were to server-side render it, we would still add CSS to hide it (visibility: hidden) by default and reveal it on scroll.

Server side rendering would require an instance of CirrusSearch to be installed which would make development more complicated. Currently developers can point RelatedArticles at a production API when working on the extension locally.

Given there are no clear benefits of moving to server side rendering and significant work would be required to improve the development workflow in that scenario we decided to continue to not server side render (for now).

[1] https://www.mediawiki.org/wiki/Reading/Web/Projects/Related_pages
[2] https://www.mediawiki.org/wiki/Reading/Web/Projects/Related_pages#Metrics_analysis

## Decision

We do not server render Vue.js. Instead the widget is rendered via JavaScript. To avoid
the article reflowing, space is reserved for the widget given we know it's height (by proxy of knowing the number of cards that will be displayed) beforehand.

## Consequences

* Space reserved may need to be modified with Codex releases.
