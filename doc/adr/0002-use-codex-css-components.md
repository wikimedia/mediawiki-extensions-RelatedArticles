# 1. Use Codex CSS components

Date: 2023-11-08

## Status

To discuss and refine once https://phabricator.wikimedia.org/T248718 has been resolved.

## Context

RelatedArticles is loaded when the user scrolls to the end of the page. Since it was
first made, the Codex library has come into existence and RelatedArticle's looks dated. By using Codex we can keep the extension in line with design best practices and the rest
of the UI and minimize the amount of modifications we have to make to it in future.

Given RelatedArticles can be loaded in the article namespace, when a user scrolls to the
bottom of the page, it is preferable to make use of Codex CSS components rather than
pulling in the full Vue.js library as the library is large. Loading the entirety of Vue
and Codex without a clear user interaction is prohibited (https://phabricator.wikimedia.org/T248718).

## Decision

* RelatedArticles uses Codex components.
* We are using Codex CSS components rather than Vue+Codex.


## Consequences

* Markup will need to be kept in sync with Codex CSS components.
* In future it may make sense to use Vue.js if there is greater adoption in the skin.
At the time of writing for skin only the search widget inside Vector is built in Vue.js.