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

  @integration
  Scenario: ReadMore is not present in minerva stable in default install
    Given I am using the mobile site
      And I am on the "Related Articles 1" page
      And page has fully loaded without ReadMore code
    Then I must not see ReadMore

  @en.m.wikipedia.beta.wmflabs.org @integration @extension-mobilefrontend
  Scenario: ReadMore is present in minerva beta in default installs and beta cluster
    Given I am using the mobile site
      And I am in mobile beta mode
      And I am on the "Related Articles 1" page
      And page has fully loaded with ReadMore code
    Then I must see ReadMore
      And ReadMore must have three cards

  @en.wikipedia.beta.wmflabs.org @integration
  Scenario: ReadMore is not present when disabled as a BetaFeature
    Given I am on the "Related Articles 1" page
      And page has fully loaded without ReadMore code
    Then I must not see ReadMore

  @en.wikipedia.beta.wmflabs.org @extension-betafeatures @integration
  Scenario: ReadMore is present when enabled as a BetaFeature
    Given I am logged in
      And ReadMore is enabled as a beta feature
      And I am on the "Related Articles 1" page
      And page has fully loaded with ReadMore code
    Then I must see ReadMore
