@foreach ($secretaries as $secretary)
    <div class="modal fade" id="assignAreasModal{{ $secretary->id }}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.secretary.assign_areas', $secretary->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Areas to {{ $secretary->fullname }}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            &times;
                        </button>
                    </div>

                    <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                        <p class="text-muted small">
                            <i class="fas fa-info-circle"></i> Check the areas you want to assign to this secretary.
                            To reassign an area currently managed by another secretary, simply check it here. Unchecking an area that already belongs to this secretary will not unassign it, as every area must have a secretary.
                        </p>

                        @php
                            $groupedAreas = $allAreas->groupBy('location_name');
                        @endphp

                        @foreach ($groupedAreas as $locationName => $areas)
                            <h6 class="mt-3 font-weight-bold text-primary">{{ $locationName }}</h6>
                            <div class="row">
                                @foreach ($areas as $area)
                                    <div class="col-md-4 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="areas[]" value="{{ $area->id }}"
                                                class="custom-control-input"
                                                id="areaCheck{{ $secretary->id }}_{{ $area->id }}"
                                                {{ $area->secretary_id == $secretary->id ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-normal" for="areaCheck{{ $secretary->id }}_{{ $area->id }}">
                                                {{ $area->areas_name }}
                                                @if ($area->secretary_id != $secretary->id && $area->secretary_name)
                                                    <span class="text-muted small">({{ $area->secretary_name }})</span>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background-color: #FF5F00; border-color: #FF5F00;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
