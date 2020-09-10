import React from 'react';
import ReactDOM from 'react-dom';

import jQuery from 'jquery';
import BlogPage from "./BlogPage";

window.$ = jQuery;

const locale = ( typeof ( window.CPLocale ) !== 'undefined' ? window.CPLocale : false );
if ( !locale ) {
    throw new Error( 'An error occurred. CPLocale not found.' )
}

ReactDOM.render( <BlogPage/>, document.getElementById( 'root' ) );

