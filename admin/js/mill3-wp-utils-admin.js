(function() {
  const wpMenu = document.querySelector('#adminmenu .toplevel_page_mill3-wp-utils-plugin .wp-submenu');
  const supportLink = wpMenu ? wpMenu.querySelector('li:last-child a') : null;

  // open Support link in new tab
  if( supportLink ) supportLink.setAttribute('target', '_blank');

  const dashboard = document.querySelector('#mill3-wp-utils-plugin__dashboard');
  if( dashboard ) {
    class Component {
      constructor(el) {
        this.el = el;
        this.input = this.el.querySelector('input[type=checkbox].mill3-wp-utils-plugin__component__input');
        this.id = this.input.name;

        this._onInputChange = this._onInputChange.bind(this);

        this._bindEvents();
      }

      _bindEvents() {
        this.input.addEventListener('change', this._onInputChange);
      }

      _onInputChange() {
        const formData = new FormData();
              formData.append('action', 'mill3_wp_utils_admin_toggle_component');
              formData.append('component', this.id);
              formData.append('status', this.input.checked ? 1 : 0);
        
        const menuItem = getMenuItemBy(this.id);
        if( menuItem ) menuItem.is_enabled = this.input.checked;

        refreshNav();
        fetch(window.ajaxurl, { method: 'POST', body: formData });
      }
    }

    const nav = document.querySelector('.mill3-wp-utils-plugin__nav');
    const wpMenuPrepend = wpMenu ? wpMenu.firstElementChild.outerHTML : null;
    const menuItems = JSON.parse( document.querySelector('script#mill3-wp-utils-plugin__menuItems').innerHTML );

    const getMenuItemBy = function(id) { return menuItems.find(item => item.id === id); }
    const refreshNav = function() {
      nav.innerHTML = menuItems.map(menuItem => {
        if( !menuItem.is_enabled ) return '';

        return '<a href="%href" %target class="mill3-wp-utils-plugin__navItem %active">%title</a>'
          .replace('%href', menuItem.href)
          .replace('%title', menuItem.title)
          .replace('%target', menuItem.target ? `target="${menuItem.target}"` : '')
          .replace('%active', menuItem.is_active ? 'is-active' : '');
      }).join('');

      if( !wpMenu ) return;

      wpMenu.innerHTML = wpMenuPrepend + menuItems.map((menuItem, index) => {
        if( !menuItem.is_enabled ) return '';

        const li_attrs = [];
        const li_classnames = [];
        const link_attrs = [];
        const link_classnames = [];

        if( index === 0 ) {
          li_classnames.push('wp-first-item');
          link_classnames.push('wp-first-item');
        }

        if( menuItem.is_active ) {
          li_classnames.push('current');
          link_classnames.push('current');
          link_attrs.push('aria-current="page"');
        }

        if( menuItem.target ) link_attrs.push(`target="${ menuItem.target }"`);

        if( li_classnames.length > 0 ) li_attrs.push(`class="${ li_classnames.join(' ') }"`);
        if( link_classnames.length > 0 ) link_attrs.push(`class="${ link_classnames.join(' ') }"`);

        return '<li%li_attrs><a href="%href"%link_attrs>%title</a></li%li_attrs>'
          .replace('%href', menuItem.href)
          .replace('%title', menuItem.title)
          .replace('%li_attrs', li_attrs.length > 0 ? ' ' + li_attrs.join(' ') : '')
          .replace('%link_attrs', link_attrs.length > 0 ? ' ' + link_attrs.join(' ') : '');
      }).join('');
    };

    // create components
    [ ...dashboard.querySelectorAll('.mill3-wp-utils-plugin__component') ].map(component => new Component(component));
  }
})();
