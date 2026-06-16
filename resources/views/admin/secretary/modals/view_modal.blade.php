@foreach ($secretaries as $secretary)
    <div class="modal fade" id="areasModal{{ $secretary->id }}">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $secretary->fullname }} -
                        {{ $secretaryAreas->firstWhere('secretary_id', $secretary->id)->location_name ?? '' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                </div>

                <div class="modal-body">
                    <ul>

                        @php
                            $myAreas = $secretaryAreas->where('secretary_id', $secretary->id)->groupBy('areas_name');
                        @endphp

                        @foreach ($myAreas as $areaName => $rows)
                            <li>
                                <strong>
                                    {{ $areaName }}
                                </strong>
                                <br>
                                <small>
                                    Collector: {{ $rows->pluck('collector_name')->implode(', ') }}
                                </small>
                            </li>
                        @endforeach

                    </ul>
                </div>

            </div>
        </div>
    </div>
@endforeach
