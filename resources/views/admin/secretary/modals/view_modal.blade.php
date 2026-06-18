@foreach ($secretaries as $secretary)
    <div class="modal fade" id="areasModal{{ $secretary->id }}">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $secretary->fullname }} - Assigned Areas
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                </div>

                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    @php
                        $myLocations = $secretaryAreas->where('secretary_id', $secretary->id)->groupBy('location_name');
                    @endphp

                    @forelse ($myLocations as $locationName => $areasGroup)
                        <h6 class="font-weight-bold text-primary mt-2">{{ $locationName }}</h6>
                        <ul class="mb-3">
                            @foreach ($areasGroup->groupBy('areas_name') as $areaName => $rows)
                                <li>
                                    <strong>{{ $areaName }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        Collector: {{ $rows->pluck('collector_name')->unique()->implode(', ') }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    @empty
                        <p class="text-muted text-center py-3">No areas assigned to this secretary.</p>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
@endforeach
