<footer class="site-footer text-light">

    <div class="container">
        <div class="row">
            <div class="col-sm-12 col-md-6 text-center text-md-left text-lg-left">
                <h2 class="site-logo pt-md-3">{{__('np::m.ContentPress')}}</h2>
            </div>
            <div class="col-sm-12 col-md-6">
                <div class="row mt-sm-0 mt-md-3">
                    <div class="col-xs-12 col-sm-12">
                        @if(cp_has_menu('footer-menu-1'))
                            <nav class="footer-menu-nav">
                                <ul class="footer-menu list-unstyled
                                d-flex
                                flex-wrap justify-content-center align-items-center align-content-center
                                flex-md-wrap justify-content-md-end align-items-md-end align-content-md-end">
                                    @php cp_menu('footer-menu-1') @endphp
                                </ul>
                            </nav>
                        @endif
                    </div>
                    <div class="col-xs-12 col-sm-12">
                        @if(cp_has_menu('footer-menu-2'))
                            <nav class="footer-menu-nav mt-2">
                                <ul class="footer-menu list-unstyled
                                d-flex
                                flex-wrap justify-content-center align-items-center align-content-center
                                flex-md-wrap justify-content-md-end align-items-md-end align-content-md-end">
                                    @php cp_menu('footer-menu-2') @endphp
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="copyright mt-3 pb-2">
                    <div class="d-flex pt-1
                                justify-content-center align-items-center align-content-center flex-column
                                flex-md-row justify-content-md-between align-items-md-between align-content-md-between">
                        <small>{!! __('np::m.Copyright &copy;2020 ContentPress') !!}</small>

                        @php
                            $cache = app('cp.cache');
                            $numPosts = $cache->get('np_num_posts');
                            if(! $numPosts){
                                $nh = new \App\Newspaper\NewspaperHelper();
                                $numPosts = $nh->getCountNumPosts();
                                $cache->set('np_num_posts', $numPosts);
                            }
                        @endphp
                        <small>{!! __('np::m.Posts: :num_posts', ['num_posts' => $numPosts]) !!}</small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</footer>
