<div class="modal fade" id="locationModal{{ Str::slug($locationName) }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $locationName }} - Assigned
                    Areas</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <ul>
                    @foreach ($areas as $area)
                        <li>{{ $area->areas_name }} -
                            {{ $area->collector_name }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
