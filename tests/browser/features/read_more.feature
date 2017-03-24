@chrome @staging
Feature: ReadMore
  Background:
    Given RelatedArticles test pages are installed

  @en.m.wikipedia.beta.wmflabs.org @extension-mobilefrontend
  Scenario: ReadMore is present in minerva stable on beta cluster.
    Given I am using the mobile site
      And I am on the "Related Articles 1" page
      And page has fully loaded with ReadMore code
    Then I must see ReadMore

  @en.wikipedia.beta.wmflabs.org @integration
  Scenario: ReadMore is not present on Vector
    Given I am on the "Related Articles 1" page
      And page has fully loaded without ReadMore code
    Then I must not see ReadMore