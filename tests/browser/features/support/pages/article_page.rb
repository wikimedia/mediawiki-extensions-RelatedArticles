# Standard article page
class ArticlePage
  include PageObject

  page_url '<%= URI.encode(params[:article_name]) %>'\
           '<%= URI.encode(params[:query_string]) if params[:query_string] %>'\
           '<%= params[:hash] %>'

  aside(:read_more, css: '.ra-read-more')
  li(:read_more_cards, css: '.ext-related-articles-card')
end
