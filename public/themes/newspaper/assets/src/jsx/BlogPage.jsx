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
            //#! Assume we have more data to retrieve
            has_more: true,
            load_more: false,
        };

        this.btnLoadMoreRef = React.createRef();
        this.__initJS = this.__initJS.bind( this );
        this.__checkWinSize = this.__checkWinSize.bind( this );
        this.__initInfiniteScroll = this.__initInfiniteScroll.bind( this );
        this.__loadMoreOnClick = this.__loadMoreOnClick.bind( this );
    }

    componentDidMount() {
        // execute the ajax request
        this.__ajaxGetEntries();

        this.__initJS();
    }

    __ajaxGetEntries() {
        if ( this.state.loading || !this.state.has_more ) {
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
                            self.setState( {
                                has_more: false
                            } );
                        }
                    }
                    else {
                        if ( r.data ) {
                            console.error( locale.t.invalid_response );
                        }
                        else {
                            console.error( locale.t.empty_response );
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
                $( self.btnLoadMoreRef.current ).removeAttr( 'disabled' );
            } );
    }

    __loading() {
        const styles = {
            fontSize: '30px',
            color: '#cc0000'
        }
        return <div className="col-xs-12 col-sm-6 col-md-4 masonry-item">
            <div className="text-center mt-3 mb-3"><i className="fas fa-circle-notch fa-spin" style={styles}></i></div>
        </div>
    }

    /**
     * Checks for changes in window size
     * @private
     */
    __initJS() {
        var mq = window.matchMedia( "(max-width: 767px)" );
        this.__checkWinSize( mq );
        const self = this;
        $( window ).on( 'load resize', function () {
            self.__checkWinSize( mq );
        } );
    }

    /**
     * Helper method to check the window size
     * @param mq
     * @private
     */
    __checkWinSize(mq) {
        // If media query matches
        if ( mq.matches ) {
            this.setState( { load_more: true } )
        }
        else {
            this.__initInfiniteScroll();
            this.setState( { load_more: false } )
        }
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

    __loadMoreOnClick() {
        //#! Prevent repetitive clicks
        if ( this.loading ) {
            return false;
        }
        $( this.btnLoadMoreRef.current ).attr( 'disabled', true );
        this.__ajaxGetEntries();
    }

    render() {
        const { loading, data, load_more, has_more } = this.state;

        const entries = ( data.entries ? data.entries : false );

        return <React.Fragment>
            {entries ? <BlogPageMasonry elements={entries}/> : ''}

            {loading && this.__loading()}

            {( entries && load_more && has_more ) ? <div className="col-xs-12 col-sm-12">
                <div className="text-center mt-4 mb-4">
                    <button className="btn btn-primary"
                            ref={this.btnLoadMoreRef}
                            onClick={this.__loadMoreOnClick}>{locale.t.load_more}</button>
                </div>
            </div> : ''}
        </React.Fragment>
    }
}

export default BlogPage;
