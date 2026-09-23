// constants to check when the script is ready
let MILL3_WP_UTILS_GUTENBERG_SIDEBAR_RIZEABLE_READY = false;

// LocalStorage key, previously defined in our all our themes using Gutenberg since 2022. ** DONT CHANGE THIS **
const MILL3_WP_UTILS_GUTENBERG_SIDEBAR_STORAGE_KEY = 'mill3_gutenberg_resizable_sidebar';

// Check if the wp.data object is available
if(typeof wp !== 'undefined' && typeof wp.data !== 'undefined') {
  wp.domReady(() => {
    wp.data.subscribe(mill3WpUtilsGutenbergSetResizable);
    wp.data.subscribe(mill3WpUtilsGutenbergOpenSidebar);
  });
}

const mill3WpUtilsGutenbergSetResizable = () => {
  // stop if already ready
  if(MILL3_WP_UTILS_GUTENBERG_SIDEBAR_RIZEABLE_READY === true) return;

  const ELEMENT_SELECTOR = '.interface-interface-skeleton__sidebar';

  // Check if the element exists, if not the wp.data.subscribe will be called again until it's found
  if(!jQuery(ELEMENT_SELECTOR).length) return

  const storedWidth = localStorage.getItem(MILL3_WP_UTILS_GUTENBERG_SIDEBAR_STORAGE_KEY);

  // --has-resized gates every width/transition override in the CSS
  if(storedWidth !== null) {
    jQuery(ELEMENT_SELECTOR).addClass('--has-resized');
    jQuery(ELEMENT_SELECTOR).width(storedWidth);
    jQuery(ELEMENT_SELECTOR).css('--mill3-sidebar-width', storedWidth + 'px');
  }

  jQuery(ELEMENT_SELECTOR).resizable({
      handles: 'w',
      start: function() {
          jQuery(this).addClass('is-resizing');
      },
      stop: function() {
          jQuery(this).removeClass('is-resizing');
      },
      resize: function() {
          const newWidth = jQuery(this).width();
          jQuery(this).addClass('--has-resized');
          jQuery(this).css({'left': 0});
          jQuery(this).css('--mill3-sidebar-width', newWidth + 'px');
          localStorage.setItem(MILL3_WP_UTILS_GUTENBERG_SIDEBAR_STORAGE_KEY, newWidth);
      }
  });

  // set ready flag to true
  MILL3_WP_UTILS_GUTENBERG_SIDEBAR_RIZEABLE_READY = true;
}

const mill3WpUtilsGutenbergOpenSidebar = () => {
  const CLASSNAME = '--mill3-gutenberg-sidebar-open';
  const ELEMENT = document.querySelector('.edit-post-layout, .edit-site-layout');
  const SCOPE = document.querySelector('.edit-site-layout') ? 'core/edit-site' : 'core/edit-post';

  if (!ELEMENT) return;

  const isSidebarOpen = !!wp.data.select('core/interface').getActiveComplementaryArea(SCOPE);
  if (isSidebarOpen) {
    if( !ELEMENT.classList.contains(CLASSNAME) ) ELEMENT.classList.add(CLASSNAME);
  } else {
    if( ELEMENT.classList.contains(CLASSNAME) ) ELEMENT.classList.remove(CLASSNAME);
  }
}
