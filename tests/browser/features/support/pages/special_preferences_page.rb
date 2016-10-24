class SpecialPreferencesPage
  include PageObject
  page_url 'Special:Preferences'

  a(:beta_features_tab, css: '#preftab-betafeatures')
  text_field(:read_more_checkbox, css: '[name=wpread-more]')
  button(:submit_button, css: '#prefcontrol')

  def enable_read_more
    beta_features_tab_element.when_present.click
    return unless read_more_checkbox_element.attribute('checked').nil?
    read_more_checkbox_element.click
    submit_button_element.when_present.click
  end
end
