PHP5 News Forge
===============

Forging an API onto existing news websites.


NewsForge API methods:

* `getStories($url)` returns all story links on the page
* `getStory($url [, $story])` returns the story data from an article page



Feature list (Todo):
--------------------

* Domain specific caching
* File/content type specific caching
* Generic spidering
* Site specific spidering rules
* Filtered getStories - matching specific criteria
* Getting story data and cached HTML by GUID
* Needs refined methods (with configurable caching):
  * getStoryHtml()
  * getStoryData()
  * getStoryEntities()
* Needs to detect 'empty stories'

* DEBUG: `*_CH_*`, and Guids without a date.
* Use a Decorator pattern to allow the request object to be decorated
  on a domain specific basis.