<ul class="pagination pagination-sm">
  <li><a href="{{ pager.getFirstPage() == pager.getCurrentPage() ? '#' : makeUrl(null, { 'offset' : pager.getFirstOffset() }) }}"
    class="{{ pager.getFirstPage() == pager.getCurrentPage() ? 'disabled' : '' }}">&laquo;</a></li>

  <li><a href="{{ pager.getFirstPage() == pager.getCurrentPage() ? '#' : makeUrl(null, { 'offset' : pager.getPreviousOffset() }) }}"
    class="{{ pager.getFirstPage() == pager.getCurrentPage() ? 'disabled' : '' }}">&lsaquo;</a></li>

  {% for page in pager.getPageList() %}
  <li><a href="{{ page == pager.getCurrentPage() ? '#' : makeUrl(null, { 'offset' : pager.getOffsetFromPage(page) }) }}"
    class="{{ page == pager.getCurrentPage() ? 'active' : '' }}">{{ page }}</a></li>
  {% endfor %}

  <li><a href="{{ pager.getLastPage() == pager.getCurrentPage() ? '#' : makeUrl(null, { 'offset' : pager.getLastOffset() }) }}"
    class="{{ pager.getLastPage() == pager.getCurrentPage() ? 'disabled' : '' }}">&rsaquo;</a></li>

  <li><a href="{{ pager.getLastPage() == pager.getCurrentPage() ? '#' : makeUrl(null, { 'offset' : pager.getNextOffset() }) }}"
    class="{{ pager.getLastPage() == pager.getCurrentPage() ? 'disabled' : '' }}">&raquo;</a></li>
</ul>