/**
 * This is where we load and initialize components of the app.
 */

// Import Forge, the DoSomething.org pattern library.
import '@dosomething/forge';

// Styles
import '../scss/app.scss';

// Temporarily needed since old version of Validation package included
// some hardcoded calls to the window.Drupal object and t().
window.Drupal = {
  t: function(value) {
    return value;
  }
};

// Utilities
import Analytics from './utilities/Analytics';
import DeLorean from './utilities/DeLorean';

// Register validation rules for en lang only.
if (document.documentElement.lang === 'en') require('./validators/auth');


// Initialize analytics.
Analytics.init();

// Initialize routing back to last page.
DeLorean.init();
