@foreach ($collectors as $collector)
    <div class="modal fade" id="assignAreasModal{{ $collector->id }}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.collector.assign_areas', $collector->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Areas to {{ $collector->fullname }}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            &times;
                        </button>
                    </div>

                    <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                        <p class="text-muted small">
                            <i class="fas fa-info-circle"></i> Check the areas you want to assign to this collector.
                            You can assign multiple areas to this collector, and multiple collectors can share the same area.
                        </p>

                        @php
                            $groupedAreas = $uniqueAreas->groupBy('location_name');
                        @endphp

                        @foreach ($groupedAreas as $locationName => $areas)
                            <h6 class="mt-3 font-weight-bold text-primary">{{ $locationName }}</h6>
                            <div class="row">
                                @foreach ($areas as $area)
                                    @php
                                        // Check if current collector is already assigned to this area
                                        $isAssigned = $area->assigned_collectors->contains('collector_id', $collector->id);
                                        // Get other collectors assigned to this area
                                        $otherCollectors = $area->assigned_collectors->filter(function($c) use ($collector) {
                                            return $c->collector_id != $collector->id;
                                        })->pluck('fullname')->toArray();
                                    @endphp
                                    <div class="col-md-4 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="areas[]" value="{{ $area->location_name }}|{{ $area->areas_name }}"
                                                class="custom-control-input"
                                                id="areaCheck{{ $collector->id }}_{{ Str::slug($area->location_name) }}_{{ $area->areas_name }}"
                                                {{ $isAssigned ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-normal" for="areaCheck{{ $collector->id }}_{{ Str::slug($area->location_name) }}_{{ $area->areas_name }}">
                                                {{ $area->areas_name }}
                                                @if (!empty($otherCollectors))
                                                    <span class="text-muted small">({{ implode(', ', $otherCollectors) }})</span>
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
