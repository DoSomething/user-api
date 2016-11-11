/**
 * This is where we load and initialize components of the app.
 */

// Import DoSomething.org libraries.
import '@dosomething/forge';

// Styles
import '../scss/app.scss';

// Utilities
import Analytics from './utilities/Analytics';

// Register validation rules.
import './validators/auth';

// Initialize analytics.
Analytics.init();
