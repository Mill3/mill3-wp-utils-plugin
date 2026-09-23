// constants to check when the script is ready
let MILL3_WP_UTILS_GUTENBERG_SIDEBAR_RIZEABLE_READY = false;

// LocalStorage key, previously defined in our all our themes using Gutenberg since 2022. ** DONT CHANGE THIS **
const MILL3_WP_UTILS_GUTENBERG_SIDEBAR_STORAGE_KEY = 'mill3_gutenberg_resizable_sidebar';

// Check if the wp.data object is available
if(typeof wp !== 'undefined' && typeof wp.data !== 'undefined') {
  wp.domReady(() => {
    // using wp.data.subscribe to wait for the Gutenberg editor to be ready, functions are called again as the state changes in the editor
    wp.data.subscribe(mill3WpUtilsGutenbergSetResizable);
    // runs on every store change, so it also reflects sidebar toggles triggered programmatically
    // (e.g. wp.data.dispatch('core/edit-post').openGeneralSidebar(...)), not just DOM clicks
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
  jQuery(ELEMENT_SELECTOR).width(storedWidth);
  // --mill3-sidebar-width tracks the resting (dragged) width, read by the CSS rule that
  // sizes the inner content — kept separate from the outer wrapper's own animating width
  // so form fields don't reflow/squeeze during the open/close transition
  jQuery(ELEMENT_SELECTOR).css('--mill3-sidebar-width', storedWidth + 'px');
  jQuery(ELEMENT_SELECTOR).resizable({
      handles: 'w',
      // suppress the open/close width transition while actively dragging, otherwise every
      // width update below would also ease/lag instead of tracking the mouse
      start: function() {
          jQuery(this).addClass('is-resizing');
      },
      stop: function() {
          jQuery(this).removeClass('is-resizing');
      },
      resize: function() {
          const newWidth = jQuery(this).width();
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

  // read the real sidebar state from the same store openGeneralSidebar()/closeGeneralSidebar() use,
  // instead of guessing from pinned plugin buttons (those don't reflect the core Settings/Block sidebar)
  const isSidebarOpen = !!wp.data.select('core/interface').getActiveComplementaryArea(SCOPE);

  if (isSidebarOpen) {
    if( !ELEMENT.classList.contains(CLASSNAME) ) ELEMENT.classList.add(CLASSNAME);
  } else {
    if( ELEMENT.classList.contains(CLASSNAME) ) ELEMENT.classList.remove(CLASSNAME);
  }
}
