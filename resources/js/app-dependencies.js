try {
    window.$ = window.jQuery = require('jquery');
} catch (e) {
    throw new Error("Error loading: jquery.js");
}
