Given(/^RelatedArticles test pages are installed$/) do
  api.create_page 'Related Articles 1',
                  File.read('samples/related_articles_1.wikitext')

  api.create_page 'Related Articles 2',
                  File.read('samples/related_articles_2.wikitext')

  api.create_page 'Related Articles 3',
                  File.read('samples/related_articles_3.wikitext')

  api.create_page 'Related Articles 4',
                  File.read('samples/related_articles_4.wikitext')
end

Given(/^I am using the mobile site$/) do
  visit(MainPage) do |page|
    page_uri = URI.parse(page.page_url_value)

    domain = page_uri.host == 'localhost' ? nil : page_uri.host
    browser.cookies.add 'mf_useformat', 'true', domain: domain

    page.refresh
  end
end

Given /^I am in mobile beta mode$/ do
  visit(MainPage) do |page|
    page_uri = URI.parse(page.page_url_value)

    # A domain is explicitly given to avoid a bug in earlier versions of Chrome
    domain = page_uri.host == 'localhost' ? nil : page_uri.host
    browser.cookies.add 'mf_useformat', 'true', domain: domain
    browser.cookies.add 'optin', 'beta', domain: domain

    page.refresh
  end
end

Given(/^I am on the "(.*?)" page/) do |arg1|
  visit(ArticlePage, using_params: { article_name: arg1 })
end

Then(/^page has fully loaded with ReadMore code$/) do
  on(ArticlePage) do |page|
    page.wait_until do
      # Wait for async JS to hijack standard link
      script = 'return mw && mw.loader && '\
        'mw.loader.getState("ext.relatedArticles.readMore") === "ready";'
      page.execute_script(script)
    end
  end
end

Then(/^page has fully loaded without ReadMore code$/) do
  on(ArticlePage) do |page|
    page.wait_until do
      # Wait for async JS to hijack standard link
      script = 'return mw && mw.loader && '\
        'mw.loader.getState("ext.relatedArticles.readMore") === "registered";'
      page.execute_script(script)
    end
  end
end

Then(/^ReadMore is enabled as a beta feature$/) do
  visit(SpecialPreferencesPage).enable_read_more
end

Then(/^I must see ReadMore$/) do
  expect(on(ArticlePage).read_more_element.when_present).to be_visible
end

Then(/^I must not see ReadMore$/) do
  expect(on(ArticlePage).read_more_element).to_not be_visible
end

Then(/^ReadMore must have three cards$/) do
  expect(browser.execute_script("return $('.ext-cards-card').length")).to eq(3)
end
