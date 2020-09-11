import React from 'react';
import ReactDOM from 'react-dom';

import jQuery from 'jquery';
import BlogPage from "./BlogPage";

window.$ = jQuery;

const locale = ( typeof ( window.CPLocale ) !== 'undefined' ? window.CPLocale : false );
if ( !locale ) {
    throw new Error( 'An error occurred. CPLocale not found.' )
}

//#! Ensure valid context
const rootEl = $( '#blog-app-root' );
if ( rootEl && rootEl.length ) {
    ReactDOM.render( <BlogPage/>, document.getElementById( 'blog-app-root' ) );
}
