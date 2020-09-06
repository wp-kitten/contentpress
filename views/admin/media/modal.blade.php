@inject('mediaHelper', App\Helpers\MediaHelper)
<div class="modal fade js-media-modal" id="mediaModal" tabindex="-1" role="dialog" aria-labelledby="mediaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{__('a.Close')}}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="content js-content hidden">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="media-tab-files" data-toggle="tab" role="tab" href="#media-tab-files-panel" aria-controls="media-tab-files-panel" aria-selected="false">
                                {{__('a.All files')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="media-tab-upload" data-toggle="tab" href="#media-tab-upload-panel" role="tab" aria-controls="media-tab-upload-panel" aria-selected="false">
                                {{__('a.Upload')}}
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="media-tab-files-panel" role="tabpanel" aria-labelledby="media-tab-files">
                            <div class="row">
                                <div class="col-12">
                                    <div class="contentpress-media-list custom-scroll">
                                        @forelse($files as $file)
                                            <div class="item js--item" data-id="{{$file->id}}">
                                                <a href="#" class="js-contentpress-thumbnail thumbnail" data-id="{{$file->id}}">
                                                    <img src="{{$mediaHelper->getUrl($file->path)}}" alt="{{$file->title}}" class="contentpress-thumbnail"/>
                                                </a>
                                            </div>
                                        @empty
                                            <div class="alert alert-info">
                                                {{__('a.No files found')}}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="media-tab-upload-panel" role="tabpanel" aria-labelledby="media-tab-upload">
                            <div class="card-body">
                                <input type="file" id="dropify_image_field" name="dropify_image_field" accept="image/*" class="dropify"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal">{{__('a.Close')}}</button>
            </div>
        </div>
    </div>
</div>
