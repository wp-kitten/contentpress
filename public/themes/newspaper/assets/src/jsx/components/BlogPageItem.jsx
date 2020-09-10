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
        return <div className="col-xs-12 col-sm-6 col-md-4 masonry-item">
            <article className="hentry-loop">
                <header className="hentry-header">
                    {image_url && <img src={image_url} alt={post_title} className="image-responsive"/>}
                    <div className="hentry-category bg-danger">
                        <a href={category_url} className="text-light">
                            {category_name}
                        </a>
                    </div>
                </header>
                <section className="hentry-content">
                    <h4 className="hentry-title">
                        <a href={post_url} className="text-info">
                            {post_title}
                        </a>
                    </h4>
                </section>
            </article>
        </div>
    }

}

export default BlogPageItem;
