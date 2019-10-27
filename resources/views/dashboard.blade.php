@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    
    <div class="row mb-3">

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3 d-flex flex-column align-items-center">
                    <h3 class="mb-0 font-weight-bold text-uppercase">0</h3>
                    <!-- <span class="small">Applicants Booking (Last 7 days) </span> -->
                    <span class="small">Programs</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3 d-flex flex-column align-items-center">
                    <h3 class="mb-0 font-weight-bold text-uppercase">0</h3>
                    <span class="small">Courses</span>
                </div>
            </div>
        </div>


        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3 d-flex flex-column align-items-center">
                    <h3 class="mb-0 font-weight-bold text-uppercase">0</h3>
                    <span class="small">Teachers</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body py-3 d-flex flex-column align-items-center">
                    <h3 class="mb-0 font-weight-bold">0</h3>
                    <span class="small">Survays</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Survey List</div>
                <!--
                    <div class="card-body py-3">
                        <div class="font-weight-bold text-uppercase"></div>
                        <span class="small text-muted">Access code for the ongoing event protected pages.</span>
                    </div>
                    <hr/ class="my-0">
                -->
                <div class="card-body py-3">
                    <table class="table table-bordered{{ count($surveys) > 0 ? ' table-hover' : ''}}">
                        <thead>
                          <tr>
                            <th class="hidden-xs">UUID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th class="hidden-xs">Last edited</th>
                            <th class="hidden-xs"></th>
                          </tr>
                        </thead>

                        <tbody>
                            @if(count($surveys) < 1)
                              <tr>
                                <td colspan="5">
                                  <h3 class="text-center">
                                    {{ Html::linkRoute('survey.create', 'Create', [], ['class' => 'btn btn-primary']) }} your first survey.
                                  </h3>
                                </td>
                              </tr>
                            @endif

                            @foreach($surveys as $survey_index => $survey)
                              <tr>
                                <th class="hidden-xs">{{ $survey->uuid }}</th>
                                <td>
                                  {{ $survey->name }}
                                  <div class="visible-xs">
                                    {{ Html::linkRoute('survey.edit', 'Edit', [$survey->uuid], ['class' => 'btn btn-warning btn-xs']) }}
                                    {{ Html::linkRoute('survey.destroy', 'Delete', [$survey->uuid], ['class' => 'btn btn-danger btn-xs']) }}
                                  </div>
                                </td>
                                <td>{{ $survey->status }}</td>
                                <td class="hidden-xs">
                                  <span title="{{ $survey->updated_at_rfc1123 }}">{{ $survey->updated_at_diff }}</span>
                                </td>
                                <td class="hidden-xs">
                                  {{ Html::linkRoute('survey.edit', 'Edit', [$survey->uuid], ['class' => 'btn btn-warning btn-xs']) }}
                                  {{ Html::linkRoute('survey.destroy', 'Delete', [$survey->uuid], ['class' => 'btn btn-danger btn-xs']) }}
                                </td>
                              </tr>
                            @endforeach

                        </tbody>
                    </table>

                    <div class="text-center">
                        {!! $surveys->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  
@endsection

