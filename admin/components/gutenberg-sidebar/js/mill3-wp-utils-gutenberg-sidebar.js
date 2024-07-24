// Ensure the wp.data object is available

let MILL3_WP_UTILS_GUTENBERG_SIDEBAR_READY = false;

// LocalStorage key, previously defined in our all our themes using Gutenberg since 2022. ** DONT CHANGE THIS **
const MILL3_WP_UTILS_GUTENBERG_SIDEBAR_STORAGE_KEY = 'mill3_gutenberg_resizable_sidebar';

// Check if the wp.data object is available
if(typeof wp !== 'undefined' && typeof wp.data !== 'undefined') {

  wp.domReady(() => {
    wp.data.subscribe(mill3WpUtilsGutenbergSetResizable);
    wp.data.subscribe(mill3WpUtilsGutenbergSidebar);
  });

}

const mill3WpUtilsGutenbergSetResizable = () => {
  const ELEMENT_SELECTOR = '.interface-interface-skeleton__sidebar';

  // Check if the element exists, if not the wp.data.subscribe will be called again until it's found
  if(!jQuery(ELEMENT_SELECTOR).length) return

  jQuery(ELEMENT_SELECTOR).width(localStorage.getItem(MILL3_WP_UTILS_GUTENBERG_SIDEBAR_STORAGE_KEY))
  jQuery(ELEMENT_SELECTOR).resizable({
      handles: 'w',
      resize: function() {
          jQuery(this).css({'left': 0});
          localStorage.setItem(MILL3_WP_UTILS_GUTENBERG_SIDEBAR_STORAGE_KEY, jQuery(this).width());
      }
  });

  // set ready flag to true
  MILL3_WP_UTILS_GUTENBERG_SIDEBAR_READY = true;
}

const mill3WpUtilsGutenbergSidebar = () => {
  const CLASSNAME = '--mill3-gutenberg-sidebar-open';
  const ELEMENT = document.querySelector('.edit-post-layout, .edit-site-layout');

  if(!ELEMENT) return

  // Get the current state of the editor
  const state = wp.data.select('core/edit-post');

  // Check if the sidebar is open
  const isSidebarOpen = state.isEditorSidebarOpened();

  // Perform actions based on the sidebar state
  if (isSidebarOpen) {
    if( !ELEMENT.classList.contains(CLASSNAME) ) ELEMENT.classList.add(CLASSNAME);
  } else {
    if( ELEMENT.classList.contains(CLASSNAME) ) ELEMENT.classList.remove(CLASSNAME);
  }
}

