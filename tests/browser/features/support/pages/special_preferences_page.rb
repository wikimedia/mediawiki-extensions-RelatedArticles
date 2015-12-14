class SpecialPreferencesPage
  include PageObject
  page_url 'Special:Preferences'

  a(:beta_features_tab, css: '#preftab-betafeatures')
  text_field(:read_more_checkbox, css: '#mw-input-wpread-more')
  button(:submit_button, css: '#prefcontrol')

  def enable_read_more
    self.beta_features_tab_element.when_present.click
    read_more_checkbox_element.when_present.click if read_more_checkbox_element.attribute('checked').nil?
    submit_button_element.when_present.click
  end
end
