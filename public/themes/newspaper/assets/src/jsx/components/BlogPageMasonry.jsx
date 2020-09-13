import React, { Component } from "react";
import BlogPageItem from "./BlogPageItem";
import Masonry from 'react-masonry-component';

class BlogPageMasonry extends Component {

    constructor(props) {
        super( props )
    }

    render() {
        const elements = this.props.elements;
        const hasElements = elements.length >= 1;
        let __children = false;

        const masonryOptions = {
            transitionDuration: 0,
            itemSelector: '.masonry-item',
            columnWidth: '.grid-sizer',
            percentPosition: true,
        };

        const imagesLoadedOptions = { background: '.my-bg-image-el' }

        if ( hasElements ) {
            __children = elements.map( function (obj, keyIndex) {
                return <BlogPageItem key={keyIndex} entry={obj}/>;
            } );
        }

        return <React.Fragment>
            {hasElements && <Masonry
                className="row masonry-grid blog-masonry-grid" // default ''
                elementType={'div'} // default 'div'
                options={masonryOptions} // default {}
                disableImagesLoaded={false} // default false
                updateOnEachImageLoad={false} // default false and works only if disableImagesLoaded is false
                imagesLoadedOptions={imagesLoadedOptions} // default {}
            >
                {/*  The sizing element for columnWidth  */}
                <div className="grid-sizer col-xs-12 col-sm-6 col-md-4"></div>
                {__children}
            </Masonry>}
        </React.Fragment>
    }

}

export default BlogPageMasonry;
