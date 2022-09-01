 <!-- Modal -->
 <div class="modal fade" id="default-example-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    Sub Categories
                    <small class="m-0 text-muted">
                    </small>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="catForm" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="category_id">Select Category</label>
                        <select id="category_id" name="category_id" class="form-control">
                            @foreach($category as $c)
                                <option value="{{ $c->id }}"> {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control">
                        <input type="hidden" value="" id="service_id" name="service_id">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Image</label>
                        <input type="file" id="image" name="image" class="form-control">
                    </div>
                    {{-- <div class="form-group">
                        <img src="" name="image" id="image" width="100" height="100"/>
                    </div> --}}
                    <div class="form-group">
                        <label class="form-label" for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="2">Deactive</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="save" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>