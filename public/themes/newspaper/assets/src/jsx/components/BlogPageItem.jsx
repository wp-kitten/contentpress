import React, { Component } from "react";

class BlogPageItem extends Component {

    constructor(props) {
        super( props )
    }

    render() {
        const {
            image_url,
            post_title,
            post_url,
            category_name,
            category_url
        } = this.props.entry;

        const isAd = ( category_name.length === 0 );
        let linkAttrs = {};
        if ( isAd ) {
            linkAttrs.target = '_blank';
        }

        return (image_url.length ? <div className="col-xs-12 col-sm-6 col-md-4 masonry-item">
            <article className="hentry-loop">
                <header className="hentry-header">
                    {image_url && <div dangerouslySetInnerHTML={{__html: image_url}}></div>}
                    {category_url && <div className="hentry-category bg-danger">
                        <a href={category_url} className="text-light">
                            {category_name}
                        </a>
                    </div>}
                </header>
                <section className="hentry-content">
                    <h4 className="hentry-title">
                        <a href={post_url} className="text-info" {...linkAttrs}>
                            {post_title}
                        </a>
                    </h4>
                </section>
            </article>
        </div> : '')
    }

}

export default BlogPageItem;
