@staging @integration
Feature: ReadMore
  Background:
    Given RelatedArticles test pages are installed

  @extension-mobilefrontend
  Scenario: ReadMore is not present in minerva stable
    Given I am using the mobile site
      And I am on the "Related Articles 1" page
      And page has fully loaded without ReadMore code
    Then I must not see ReadMore

  @extension-mobilefrontend
  Scenario: ReadMore is present in minerva beta
    Given I am using the mobile site
      And I am in mobile beta mode
      And I am on the "Related Articles 1" page
      And page has fully loaded with ReadMore code
    Then I must see ReadMore
      And ReadMore must have three cards

  Scenario: ReadMore is not present when disabled as a BetaFeature
    Given I am on the "Related Articles 1" page
      And page has fully loaded without ReadMore code
    Then I must not see ReadMore

  @extension-betafeatures
  Scenario: ReadMore is present when enabled as a BetaFeature
    Given I am logged into the website
      And ReadMore is enabled as a beta feature
      And I am on the "Related Articles 1" page
      And page has fully loaded with ReadMore code
    Then I must see ReadMore
