class LoginPage
  include PageObject
  page_url 'Special:Userlogin'

  text_field(:username, name: 'wpName')
  text_field(:password, name: 'wpPassword')

  def login_with(username, password)
    # deal with autocomplete
    self.username_element.when_present.clear
    self.username = username
    self.password = password
    login
  end
end
