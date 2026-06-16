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

                        @foreach ($secretaryAreas as $area)
                            @if ($area->secretary_id == $secretary->id)
                                <li>
                                    <strong>

                                        {{ $area->areas_name }}
                                    </strong>
                                    <br>
                                    <small>
                                        Collector: {{ $area->collector_name }}
                                    </small>
                                </li>
                            @endif
                        @endforeach

                    </ul>
                </div>

            </div>
        </div>
    </div>
@endforeach
