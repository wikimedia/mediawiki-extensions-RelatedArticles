# 1. Use ADRs in RelatedArticles

Date: 2023-11-08

## Status

To discuss.

## Context

ADRs are being used in a variety of web team extensions (Popups and Vector). They
help us reflect on decisions we've made. RelatedArticles is seldom worked on, and
refactors and modifications are rare, meaning developers can go long periods of time
without touching the codebase, so it would be useful that decisions here are documented.

## Decision

We are adopting architecture decision records as we are in other code repositories.

## Consequences

When an decision that is deemed important to the codebase or the ecosystem
is made inside this repository, it is up to the deciding individuals
to create a new markdown file in this directory and add an ADR with regards
to that decision.The ADR should be brief and may link to Phabricator or
other places that hold more context around the decision.

ADRs may also be used to propose changes by framing the ADR as a proposal
and marking the status as "proposed". If there is agreement to adopt the
ADR, it's status should be change to "accepted".

ADRs can also be marked "deprecated" or "superseded".