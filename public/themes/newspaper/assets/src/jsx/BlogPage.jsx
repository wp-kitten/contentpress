import React, { Component } from 'react';
import BlogPageMasonry from "./components/BlogPageMasonry";

const Masonry = require( 'masonry-layout' );
const $ = require( 'jquery' );

const locale = ( typeof ( window.CPLocale ) !== 'undefined' ? window.CPLocale : false );
if ( !locale ) {
    throw new Error( 'An error occurred. CPLocale not found.' )
}

/**
 *
 */
class BlogPage extends Component {

    constructor(props) {
        super( props );

        this.state = {
            ids: [],
            loading: false,
            data: {
                page: 0,
                entries: [],
            },
        };
    }

    componentDidMount() {
        // execute the ajax request
        this.__ajaxGetEntries();
        this.__initInfiniteScroll();
    }

    __ajaxGetEntries() {
        if ( this.state.loading ) {
            return false;
        }

        const self = this;
        self.setState( { loading: true } );

        const ajaxConfig = {
            url: locale.ajax.url,
            method: 'POST',
            dataType: 'json',
            cache: false,
            async: true,
            timeout: 29000,
            data: {
                action: 'get_blog_entries',
                exclude: self.state.ids,
                page: self.state.data.page,
                [locale.ajax.nonce_name]: locale.ajax.nonce_value,
            }
        };

        $.ajax( ajaxConfig )
            .done( function (r) {
                if ( r ) {
                    if ( r.success ) {
                        if ( r.data && r.data.ids ) {
                            //#! Update state
                            const page = r.data.page;
                            let objData = self.state.data;
                            objData.page = page;
                            Object.keys( r.data.entries ).map( function (k, ix) {
                                objData.entries.push( r.data.entries[k] );
                            } )
                            let ids = self.state.ids;
                            self.setState( {
                                ids: ids.concat( r.data.ids ),
                                data: objData
                            } );
                        }
                        else {
                            //#! Do nothing, we don't have any more posts to show
                        }
                    }
                    else {
                        if ( r.data ) {
                            alert( locale.t.invalid_response );
                        }
                        else {
                            alert( locale.t.empty_response );
                        }
                    }
                }
                else {
                    alert( locale.t.no_response );
                }
            } )
            .fail( function (x, s, e) {
                console.error( locale.t.unknown_error + ' ' + e );
            } )
            .always( function () {
                self.setState( { loading: false } );
            } );
    }

    __loading() {
        return <div className="col-xs-12 col-sm-6 col-md-4 masonry-item">
            <h3>Loading...</h3>
        </div>
    }

    __initInfiniteScroll() {
        const self = this;
        $( window ).on( 'scroll', function () {
            // End of the document reached?
            if ( $( document ).height() - $( this ).height() === $( this ).scrollTop() ) {
                self.__ajaxGetEntries();
            }
        } );
    }

    render() {
        const { loading, data } = this.state;

        const entries = ( data.entries ? data.entries : false );

        return <React.Fragment>
            {loading && this.__loading()}

            {entries && <BlogPageMasonry elements={entries}/>}
        </React.Fragment>
    }
}

export default BlogPage;
